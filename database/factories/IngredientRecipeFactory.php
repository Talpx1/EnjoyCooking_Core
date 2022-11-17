<?php

namespace Database\Factories;

use App\Models\Ingredient;
use App\Models\MeasureUnit;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IngredientRecipe>
 */
class IngredientRecipeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'recipe_id' => Recipe::getRandomOrCreate()->id,
            'ingredient_id' => Ingredient::getRandomOrCreate()->id,
            'measure_unit_id' => MeasureUnit::getRandomOrCreate()->id,
            'quantity' => rand(1,100),
        ];
    }
}
