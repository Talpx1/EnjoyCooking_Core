<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\DifficultyLevel;
use App\Models\Course;
use App\Models\User;
use App\Models\Category;
use App\Models\ModerationStatus;
use App\Models\VisibilityStatus;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recipe>
 */
class RecipeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title' => $this->faker->words(rand(2, 5), true),
            'baking_minutes' => rand(0,1) ? rand(1, 100) : null,
            'preparation_minutes' => rand(10, 100),
            'description' => $this->faker->paragraph,
            'share_count' => rand(0, 10000),
            'difficulty_level_id' => DifficultyLevel::getRandomOrCreate()->id,
            'course_id' => Course::getRandomOrCreate()->id,
            'user_id' => User::getRandomOrCreate()->id,
            'category_id' => Category::getRandomOrCreate()->id,
            'moderation_status_id' => ModerationStatus::getRandomOrCreate()->id,
            'visibility_status_id' => VisibilityStatus::getRandomOrCreate()->id
        ];
    }
}
