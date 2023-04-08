<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\Seeders\PermissionsAndRolesSeeder;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;
    protected $seeder = PermissionsAndRolesSeeder::class;

    /**
     * @test
     */
    public function test_it_can_get_current_user(){
        $admin = $this->actingAsAdmin();
        $this->getJson(route('user.current'))->assertOk()->assertJson($admin->toArray());

        $user = $this->actingAsUser();
        $this->getJson(route('user.current'))->assertOk()->assertJson($user->toArray());
    }

}
