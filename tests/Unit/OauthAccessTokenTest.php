<?php

namespace Tests\Unit;

use App\Models\OauthAccessToken;
use App\Models\OauthRefreshToken;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OauthAccessTokenTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function test_access_token_has_many_refresh_tokens(){
        $access_token = OauthAccessToken::factory()->create();
        $refresh_tokens = OauthRefreshToken::factory(3)->create(['access_token_id' => $access_token->id]);
        $other_refresh_tokens = OauthRefreshToken::factory(4)->create(['access_token_id' => OauthAccessToken::factory()->create()->id]);

        $this->assertNotNull($access_token->refreshTokens);
        $this->assertInstanceOf(Collection::class, $access_token->refreshTokens);
        $this->assertCount(3, $access_token->refreshTokens);

        $access_token->refreshTokens->each(fn($refreshToken) => $this->assertInstanceOf(OauthRefreshToken::class, $refreshToken));

        $refresh_tokens->each(fn($refreshToken) => $this->assertTrue($access_token->refreshTokens->contains($refreshToken)));
        $other_refresh_tokens->each(fn($refreshToken) => $this->assertFalse($access_token->refreshTokens->contains($refreshToken)));
    }
}
