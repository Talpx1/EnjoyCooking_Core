<?php

namespace Database\Factories;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RecipeStep>
 */
class RecipeStepFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'image_path' => $this->faker->unique()->url,
            'thumbnail_path' => $this->faker->unique()->url,
            'description' => $this->faker->paragraph,
            'recipe_id' => Recipe::getRandomOrCreate()->id
        ];
    }
}
