<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Badge;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BadgeUser>
 */
class BadgeUserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'badge_id' => Badge::getRandomOrCreate()->id,
            'user_id' => User::getRandomOrCreate()->id,
        ];
    }
}
