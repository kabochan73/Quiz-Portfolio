<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 1問題に対する1回分の回答本文。同じ問題に何度でも再挑戦できる(要件定義3.3)ため、
 * 同じquestion_idに対して複数のAnswerが存在しうる。attempt_idで
 * 「どの一括回答セッションに属するか」を紐づける。
 */
class Answer extends Model
{
    use HasFactory;

    protected $fillable = ['question_id', 'user_id', 'attempt_id', 'body'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(Attempt::class);
    }

    public function score(): HasOne
    {
        return $this->hasOne(Score::class);
    }
}
