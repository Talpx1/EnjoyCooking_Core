<?php

namespace Tests\Unit;

use App\Models\OauthAccessToken;
use App\Models\OauthClient;
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

    /**
     * @test
     */
    public function test_access_token_belongs_to_client(){
        $client = OauthClient::factory()->create();
        $token = OauthAccessToken::factory()->create(['client_id' => $client->id]);
        $this->assertNotNull($token->client);
        $this->assertInstanceOf(OauthClient::class, $token->client);
        $this->assertEquals($client->id, $token->client->id);
    }
}
