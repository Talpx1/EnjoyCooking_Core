<?php

namespace Database\Factories;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Snack>
 */
class SnackFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title' => $this->faker->words(rand(1, 8), true),
            'description' => $this->faker->paragraph,
            'video_path' => $this->faker->url,
            'user_id' => User::getRandomOrCreate()->id,
            'recipe_id' => Recipe::getRandomOrCreate()->id
        ];
    }
}
