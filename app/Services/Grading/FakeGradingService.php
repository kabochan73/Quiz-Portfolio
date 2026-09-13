<?php

namespace App\Services\Grading;

use App\Enums\GradingLevel;

/**
 * テスト・ローカル動作確認用のダミー実装。全問80点の固定フィードバックを返す。
 * config('services.grading.driver')をfakeにすると、Claude APIを一切呼ばずに
 * 回答〜採点結果保存までの一連のフローを確認できる。
 */
class FakeGradingService implements GradingService
{
    public function grade(array $items, GradingLevel $level): array
    {
        return collect($items)
            ->map(fn () => [
                'score' => 80,
                'feedback' => '(テスト用の固定フィードバックです)',
            ])
            ->all();
    }
}
