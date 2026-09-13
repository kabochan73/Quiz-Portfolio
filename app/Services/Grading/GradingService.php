<?php

namespace App\Services\Grading;

use App\Enums\GradingLevel;
use App\Models\Question;

interface GradingService
{
    /**
     * 選択された問題+回答をまとめて採点する
     * (要件定義3.3: 1回のAPIリクエストで最大10問をまとめて採点)。
     *
     * @param  array<int, array{question: Question, body: string}>  $items  採点したい問題と回答の組
     * @return array<int, array{score: int, feedback: string}> $itemsと同じ順番・同じ件数で返す
     */
    public function grade(array $items, GradingLevel $level): array;
}
