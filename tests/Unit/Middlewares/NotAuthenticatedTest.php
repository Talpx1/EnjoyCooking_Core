<?php

namespace Tests\Unit\Middlewares;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Seeders\PermissionsAndRolesSeeder;
use Tests\TestCase;

class NotAuthenticatedTest extends TestCase{

    use RefreshDatabase;

    protected $seed = true;
    protected $seeder = PermissionsAndRolesSeeder::class;

    public function test_authenticated_user_is_forbidden(){
        $this->actingAsApiAdmin();

        $this->getJson(route('award.index'))->assertForbidden();

        $this->actingAsAdmin();

        $this->getJson(route('award.index'))->assertForbidden();
    }

    public function test_non_authenticated_user_is_allowed(){
        $this->assertGuest();

        $this->getJson(route('award.index'))->assertForbidden();
    }

}
