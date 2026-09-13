<?php

namespace App\Models;

use App\Enums\GradingLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 「セクションの全問に一括回答した1回分」を表すレコード(要件定義3.3, 3.4)。
 * Udemyのクイズ結果のように、履歴一覧はこのAttempt単位で並べる。
 */
class Attempt extends Model
{
    use HasFactory;

    protected $fillable = ['section_id', 'user_id', 'grading_level'];

    protected function casts(): array
    {
        return [
            'grading_level' => GradingLevel::class,
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * この挑戦で答えた全問題の平均点。履歴一覧に表示する(要件定義3.4)。
     * 採点結果(score)がまだ紐づいていない回答は平均の計算から除く。
     */
    public function averageScore(): ?float
    {
        $scores = $this->answers
            ->pluck('score.score')
            ->filter(fn (?int $score) => $score !== null);

        if ($scores->isEmpty()) {
            return null;
        }

        return round($scores->avg(), 1);
    }
}
