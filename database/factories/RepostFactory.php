<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use App\Models\User;
use App\Models\Category;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Repost>
 */
class RepostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $post = Arr::random([Category::class, User::class]);
        return [
            'repostable_id' => $post::getRandomOrCreate()->id,
            'repostable_type' => $post,
            'user_id' => User::getRandomOrCreate()->id,
        ];
    }
}
