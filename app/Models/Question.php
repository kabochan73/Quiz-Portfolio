<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * 自由記述の問題本体。タイトルは持たず、本文(body)のみ(要件定義3.2)。
 * 一覧画面ではタイトル代わりに本文の先頭部分を抜粋表示する。
 */
class Question extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'section_id', 'body'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * 問題一覧でタイトル代わりに表示する本文の抜粋。
     */
    public function excerpt(int $length = 40): string
    {
        return Str::limit(str($this->body)->squish(), $length);
    }
}
