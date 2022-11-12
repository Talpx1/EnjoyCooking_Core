<?php

namespace Database\Factories;

use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IngredientImage>
 */
class IngredientImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'path' => $this->faker->unique()->url,
            'thumbnail_path' => $this->faker->unique()->url,
            'description' => $this->faker->paragraph,
            'ingredient_id' => Ingredient::getRandomOrCreate()->id,
            'user_id' => User::getRandomOrCreate()->id
        ];
    }
}
