<?php

namespace Database\Factories;

use App\Models\Award;
use App\Models\Comment;
use App\Models\Execution;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Awardable>
 */
class AwardableFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $model = Arr::random([Recipe::class, Comment::class, Execution::class]);
        return [
            'awardable_id' => $model::getRandomOrCreate()->id,
            'awardable_type' => $model,
            'award_id' => Award::getRandomOrCreate()->id,
            'user_id' => User::getRandomOrCreate()->id,
        ];
    }
}
