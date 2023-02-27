<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OauthAccessToken>
 */
class OauthAccessTokenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => strval($this->faker->unique()->randomNumber()),
            'user_id' => User::getRandomOrCreate(),
            'client_id' => rand(1,100),
            'name' => null,
            'scopes' => null,
            'revoked' => 0,
            'expires_at' => Carbon::now()->addYear(),
        ];
    }
}
