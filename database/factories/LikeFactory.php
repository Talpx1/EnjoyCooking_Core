<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Recipe;
use App\Models\Repost;
use App\Models\Snack;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Like>
 */
class LikeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $model = Arr::random([Recipe::class, Comment::class, Repost::class, Snack::class]);//TODO: add execution
        return [
            'likeable_id' => $model::getRandomOrCreate()->id,
            'likeable_type' => $model,
            'user_id' => User::getRandomOrCreate()->id
        ];
    }
}
