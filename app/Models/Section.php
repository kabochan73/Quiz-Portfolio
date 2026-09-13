<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 「問題の入れ物」。必ず1つのCategoryに属し、問題(Question)は必ず1つのSectionに属す
 * (要件定義3.2)。回答は常にセクション内の全問一括で行う方式のため、
 * 1セクションにつき最大 config('quiz.max_questions_per_section') 問までしか作れない。
 */
class Section extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'name'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }

    /**
     * このセクションが問題数の上限に達しているか。
     * 問題作成フォームの表示可否や、バリデーションでの上限チェックに使う。
     */
    public function isFull(): bool
    {
        return $this->questions()->count() >= config('quiz.max_questions_per_section');
    }
}
