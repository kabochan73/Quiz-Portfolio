<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Section>
 */
class SectionFactory extends Factory
{
    protected $model = Section::class;

    public function definition(): array
    {
        return [
            // categoryを指定しなければ新規に1件作る(テスト側で明示的に紐づけたい場合はfor()で上書きする)
            'category_id' => Category::factory(),
            'name' => fake()->unique()->words(2, true),
        ];
    }
}
