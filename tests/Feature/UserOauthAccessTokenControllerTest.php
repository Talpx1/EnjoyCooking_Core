<?php

namespace Tests\Feature;

use App\Models\OauthAccessToken;
use App\Models\OauthRefreshToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Seeders\PermissionsAndRolesSeeder;
use Tests\TestCase;

class UserOauthAccessTokenControllerTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;
    protected $seeder = PermissionsAndRolesSeeder::class;

    public function test_user_can_delete_his_own_access_tokens(){
        $user = $this->actingAsApiUser();

        $this->assertAuthenticated();

        $access_tokens = OauthAccessToken::factory(3)->create(['user_id' => $user->id])
            ->each(fn($access_token) => OauthRefreshToken::factory(3)->create(['access_token_id' => $access_token->id]));

        $this->assertCount(3, $user->oauthAccessTokens);
        $access_tokens->each(fn($access_token) => $this->assertCount(3, $access_token->refreshTokens));

        $this->deleteJson(route('user.access_tokens.destroy'))->assertOk();

        $access_tokens->each(function($access_token){
            $this->assertModelMissing($access_token);
            $this->assertDatabaseMissing('oauth_access_tokens', ['id' => $access_token->id]);
            $this->assertDatabaseMissing('oauth_refresh_tokens', ['access_token_id' => $access_token->id]);
            $access_token->refreshTokens->each(function($token){
                $this->assertModelMissing($token);
                $this->assertDatabaseMissing('oauth_refresh_tokens', ['id' => $token->id]);
            });
        });

    }

    public function test_if_user_is_not_logged_in_action_is_aborted_401(){
        $this->assertGuest();

        $user = User::factory()->create();

        $access_tokens = OauthAccessToken::factory(3)->create(['user_id' => $user->id])
            ->each(fn($access_token) => OauthRefreshToken::factory(3)->create(['access_token_id' => $access_token->id]));

        $this->assertCount(3, $user->oauthAccessTokens);
        $access_tokens->each(fn($access_token) => $this->assertCount(3, $access_token->refreshTokens));

        $this->deleteJson(route('user.access_tokens.destroy'))->assertStatus(401);

    }
}
