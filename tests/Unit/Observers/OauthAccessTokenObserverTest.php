<?php

namespace Tests\Unit\Observers;

use App\Models\OauthAccessToken;
use App\Models\OauthRefreshToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OauthAccessTokenObserverTest extends TestCase
{

    use RefreshDatabase;

    /**
     * @test
     */
    public function test_when_access_token_gets_deleted_its_refresh_tokens_get_deleted()
    {
        $access_token = OauthAccessToken::factory()->create();
        $refresh_tokens = OauthRefreshToken::factory(3)->create(['access_token_id' => $access_token->id]);

        $access_token->delete();

        $this->assertModelMissing($access_token);
        $this->assertDatabaseMissing('oauth_access_tokens', ['id'=>$access_token->id]);
        $this->assertDatabaseMissing('oauth_refresh_tokens', ['access_token_id' => $access_token->id]);

        $refresh_tokens->each(fn($token) => $this->assertModelMissing($token));
    }
}
