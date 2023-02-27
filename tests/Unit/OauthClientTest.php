<?php

namespace Tests\Unit;

use App\Models\OauthAccessToken;
use App\Models\OauthClient;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OauthClientTest extends TestCase
{

    use RefreshDatabase;

    /**
     * @test
     */
    public function test_client_has_many_access_tokens(){
        $client = OauthClient::factory()->create();
        $access_tokens = OauthAccessToken::factory(3)->create(['client_id' => $client->id]);
        $other_access_tokens = OauthAccessToken::factory(4)->create(['client_id' => OauthClient::factory()->create()->id]);

        $this->assertNotNull($client->tokens);
        $this->assertInstanceOf(Collection::class, $client->tokens);
        $this->assertCount(3, $client->tokens);

        $client->tokens->each(fn($accessToken) => $this->assertInstanceOf(OauthAccessToken::class, $accessToken));

        $access_tokens->each(fn($accessToken) => $this->assertTrue($client->tokens->contains($accessToken)));
        $other_access_tokens->each(fn($accessToken) => $this->assertFalse($client->tokens->contains($accessToken)));
    }
}
