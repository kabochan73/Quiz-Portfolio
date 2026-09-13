<?php

namespace Database\Factories;

use App\Models\Answer;
use App\Models\Score;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Score>
 */
class ScoreFactory extends Factory
{
    protected $model = Score::class;

    public function definition(): array
    {
        return [
            'answer_id' => Answer::factory(),
            'score' => fake()->numberBetween(0, 100),
            'feedback' => fake()->sentence(),
        ];
    }
}
