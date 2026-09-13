<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Models\Question;
use App\Models\Section;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QuestionController extends Controller
{
    use AuthorizesRequests;

    /**
     * 問題作成フォーム。セクション詳細ページの「+問題を追加」から遷移するため、
     * セクションはURLで固定済み(選択の必要がない)。
     * 既に上限(config('quiz.max_questions_per_section'))に達している場合は、
     * フォームを出さずに警告だけを表示する(要件定義3.2)。
     */
    public function create(Section $section): View
    {
        return view('questions.create', compact('section'));
    }

    public function store(StoreQuestionRequest $request, Section $section): RedirectResponse
    {
        $question = $section->questions()->create([
            'user_id' => $request->user()->id,
            ...$request->validated(),
        ]);

        return redirect()->route('questions.show', $question)->with('status', '問題を作成しました。');
    }

    public function show(Question $question): View
    {
        return view('questions.show', compact('question'));
    }

    public function edit(Question $question): View
    {
        $this->authorize('update', $question);

        // 編集画面では所属セクションの変更も可能にするため、選択肢としてセクション一覧を渡す
        // (表示に「カテゴリ名 / セクション名」を使うのでcategoryをeager loadしN+1を避ける)
        $sections = Section::with('category')->orderBy('name')->get();

        return view('questions.edit', compact('question', 'sections'));
    }

    public function update(UpdateQuestionRequest $request, Question $question): RedirectResponse
    {
        $this->authorize('update', $question);

        $question->update($request->validated());

        return redirect()->route('questions.show', $question)->with('status', '問題を更新しました。');
    }

    /**
     * 問題を削除する。回答履歴・採点結果はDBの外部キーcascadeで連動削除される。
     * 削除後は所属していたセクションの詳細画面へ戻す。
     */
    public function destroy(Question $question): RedirectResponse
    {
        $this->authorize('delete', $question);

        $section = $question->section;

        $question->delete();

        return redirect()->route('sections.show', $section)->with('status', '問題を削除しました。');
    }
}
