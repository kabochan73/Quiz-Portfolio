<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'section_id' => Section::factory(),
            'body' => fake()->paragraph(),
        ];
    }
}
