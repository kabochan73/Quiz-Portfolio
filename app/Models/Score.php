<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Answerに1:1で紐づく採点結果(0〜100点+日本語フィードバック)。
 * 範囲外の値はDB側のCHECK制約(scores_score_between_0_and_100)でも弾かれる。
 */
class Score extends Model
{
    use HasFactory;

    protected $fillable = ['answer_id', 'score', 'feedback'];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
        ];
    }

    public function answer(): BelongsTo
    {
        return $this->belongsTo(Answer::class);
    }
}
