<?php

namespace Database\Factories;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Favorite>
 */
class FavoriteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $model = Arr::random([Recipe::class]); //TODO: add execution
        return [
            'favoritable_id' => $model::getRandomOrCreate()->id,
            'favoritable_type' => $model,
            'user_id' => User::getRandomOrCreate()->id
        ];
    }
}
