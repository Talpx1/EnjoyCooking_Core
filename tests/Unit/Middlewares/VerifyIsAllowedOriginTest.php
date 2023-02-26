<?php

namespace Tests\Unit\Middlewares;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifyIsAllowedOriginTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_origin_is_forbidden(){
        \Config::set('cors.allowed_origins', []);

        $this->getJson(route('award.index'), ['Origin' => 'http://test.test'])->assertForbidden();
    }

    public function test_known_origin_is_allowed(){
        \Config::set('cors.allowed_origins', ['http://test.test']);

        $this->getJson(route('award.index'), ['Origin' => 'http://test.test'])->assertOk();
    }
}
