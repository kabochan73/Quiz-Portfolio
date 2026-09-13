<?php

namespace App\Http\Controllers;

use App\Enums\GradingLevel;
use App\Http\Requests\StoreAnswerBatchRequest;
use App\Models\Section;
use App\Services\Answers\AnswerRetentionService;
use App\Services\Grading\GradingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class AnswerController extends Controller
{
    public function __construct(
        private readonly GradingService $grader,
        private readonly AnswerRetentionService $retention,
    ) {
    }

    /**
     * セクション内の全問題に対する一括回答フォームを表示する。
     * 1セクションは最大10問までしか作れないので、問題を選ばせず
     * 「このセクションの全問に回答する」という単純な方式にしている(要件定義3.3)。
     */
    public function create(Request $request, Section $section): View|RedirectResponse
    {
        $questions = $section->questions()->where('user_id', $request->user()->id)->get();

        if ($questions->isEmpty()) {
            return redirect()->route('sections.show', $section)->with('status', 'このセクションにはまだ問題がありません。');
        }

        $gradingLevels = GradingLevel::cases();

        return view('answers.create', compact('section', 'questions', 'gradingLevels'));
    }

    /**
     * セクション内の全問題の回答をまとめて保存し、その場で採点して結果を表示する。
     * 採点は1回のGradingService呼び出しにまとめて渡す(要件定義3.3: 1リクエストで一括採点)。
     */
    public function store(StoreAnswerBatchRequest $request, Section $section): View|RedirectResponse
    {
        $data = $request->validated();
        $user = $request->user();

        // 送られてきた問題IDが、本当に自分の・このセクションの問題かを確認する
        // (他人の問題や他セクションの問題が混ざっていても弾く)
        $questionIds = collect($data['answers'])->pluck('question_id')->unique();
        $questions = $section->questions()
            ->whereIn('id', $questionIds)
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('id');

        abort_unless($questions->count() === $questionIds->count(), 403);

        // バリデーション時点では'easy'等の生文字列のままなので、GradingServiceに渡す前にenum化する
        $gradingLevel = GradingLevel::from($data['grading_level']);

        // AIに渡す材料をまとめてから、1回のリクエストでまとめて採点してもらう
        $items = collect($data['answers'])
            ->map(fn (array $a) => [
                'question' => $questions[$a['question_id']],
                'body' => $a['body'],
            ])
            ->all();

        // Claude APIの通信エラーやキー未設定など、外部サービス起因の失敗は普通に起こりうるので、
        // 500エラーにせず入力内容を保持したままフォームへ差し戻す。
        try {
            $results = $this->grader->grade($items, $gradingLevel);
        } catch (Throwable $e) {
            Log::error('AI採点に失敗しました', ['exception' => $e]);

            return back()->withInput()->withErrors(['grading' => 'AI採点でエラーが発生しました。しばらくしてから再度お試しください。']);
        }

        // この「1回分の全問回答」をまとめるAttemptを先に作り、各Answerをそこにぶら下げる
        // (履歴画面でUdemyのクイズ結果のように挑戦単位で一覧・詳細表示するため)
        $attempt = $section->attempts()->create([
            'user_id' => $user->id,
            'grading_level' => $gradingLevel,
        ]);

        $answers = collect();

        foreach ($data['answers'] as $i => $a) {
            $question = $questions[$a['question_id']];

            $answer = $question->answers()->create([
                'user_id' => $user->id,
                'attempt_id' => $attempt->id,
                'body' => $a['body'],
            ]);

            $answer->score()->create([
                'score' => $results[$i]['score'],
                'feedback' => $results[$i]['feedback'],
            ]);

            $this->retention->pruneOldAnswers($question, $user);

            // attemptは作成済みのものをそのまま使い回す(answers.resultビューが参照するため)
            $answer->setRelation('attempt', $attempt);
            $answers->push($answer->load('score', 'question'));
        }

        return view('answers.result', compact('section', 'answers'));
    }
}
