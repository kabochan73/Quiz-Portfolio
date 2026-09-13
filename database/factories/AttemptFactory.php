<?php

namespace Database\Factories;

use App\Enums\GradingLevel;
use App\Models\Attempt;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attempt>
 */
class AttemptFactory extends Factory
{
    protected $model = Attempt::class;

    public function definition(): array
    {
        return [
            'section_id' => Section::factory(),
            'user_id' => User::factory(),
            'grading_level' => fake()->randomElement(GradingLevel::cases()),
        ];
    }
}
