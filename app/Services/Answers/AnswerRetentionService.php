<?php

namespace App\Services\Answers;

use App\Models\Answer;
use App\Models\Attempt;
use App\Models\Question;
use App\Models\User;

/**
 * 履歴の自動整理(要件定義3.4)。
 * 同一問題については回答(answers)を直近$keep件のみ保持し、それより古いものを削除する。
 * この結果、回答が0件になった挑戦(Attempt)も自動的に削除される。
 */
class AnswerRetentionService
{
    public function __construct(
        private readonly int $keep = 10,
    ) {
    }

    public function pruneOldAnswers(Question $question, User $user): void
    {
        // PostgreSQLはDELETE文にLIMITを使えないため、先に消す対象のIDを絞り込んでから削除する。
        // created_at同士が同一時刻になるケース(テストでの連続作成など)でも順序が安定するよう、
        // idを第二ソートキーにする(idは常に作成順を反映する)。
        $staleIds = $question->answers()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->pluck('id')
            ->slice($this->keep);

        if ($staleIds->isEmpty()) {
            return;
        }

        $staleAttemptIds = Answer::whereIn('id', $staleIds)->pluck('attempt_id')->unique();

        Answer::whereIn('id', $staleIds)->delete();

        // セクションの全問を毎回一括回答する仕様上、古い挑戦の回答は全問題でほぼ同時に
        // この上限へ達するため、回答が1件も残らなくなった挑戦は挑戦自体も削除して履歴から消す。
        Attempt::whereIn('id', $staleAttemptIds)->whereDoesntHave('answers')->delete();
    }
}
