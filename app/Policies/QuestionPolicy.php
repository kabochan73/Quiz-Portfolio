<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;

/**
 * 自分の問題のみ編集・削除できるようにする(要件定義3.2「自分の問題を編集・削除」)。
 * 現状ログインユーザーは管理者1名のみだが、将来のマルチユーザー化を見据えて
 * abort_unless直書きではなくPolicyとして正式に定義しておく。
 */
class QuestionPolicy
{
    public function update(User $user, Question $question): bool
    {
        return $user->id === $question->user_id;
    }

    public function delete(User $user, Question $question): bool
    {
        return $this->update($user, $question);
    }
}
