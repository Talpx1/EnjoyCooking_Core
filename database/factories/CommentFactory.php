<?php

namespace Database\Factories;

use App\Models\Execution;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $model = Arr::random([Recipe::class, Execution::class]);
        return [
            'commentable_id' => $model::getRandomOrCreate()->id,
            'commentable_type' => $model,
            'body' => $this->faker->paragraph,
            'user_id' => User::factory()->create()->id,
            'parent_comment_id' => null
        ];
    }
}
