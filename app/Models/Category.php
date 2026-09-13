<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 分類のみを表すモデル。問題は直接持たず、必ずSectionを介してQuestionにつながる
 * (Category → Section → Question の3階層、要件定義3.2)。
 */
class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }
}
