<?php

namespace Database\Factories;

use App\Models\OauthAccessToken;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OauthRefreshToken>
 */
class OauthRefreshTokenFactory extends Factory
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
            'access_token_id' => OauthAccessToken::getRandomOrCreate()->id,
            'revoked' => 0,
            'expires_at' => Carbon::now()->addYear(),
        ];
    }
}
