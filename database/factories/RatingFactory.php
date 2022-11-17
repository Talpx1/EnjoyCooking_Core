<?php

namespace Database\Factories;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rating>
 */
class RatingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $model = Arr::random([Recipe::class, Ingredient::class]); //TODO: change ingredient with Execution
        return [
            'rating' => rand(1,5),
            'rateable_id' => $model::getRandomOrCreate()->id,
            'rateable_type' => $model,
            'user_id' => User::getRandomOrCreate()->id,
        ];
    }
}
