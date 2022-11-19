<?php

namespace Database\Factories;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Follow>
 */
class FollowFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $model = Arr::random([User::class, Tag::class]);
        return [
            'followable_id' => $model::getRandomOrCreate()->id,
            'followable_type' => $model,
            'user_id' => User::getRandomOrCreate()->id
        ];
    }
}
