<?php

namespace Tests;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\User;
use App\Enums\Roles;
use Laravel\Passport\Passport;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function actingAsAdmin(){
        return $this->actingAs(User::factory()->create()->assignRole(Roles::ADMIN->value));
    }

    public function actingAsModerator(){
        return $this->actingAs(User::factory()->create()->assignRole(Roles::MODERATOR->value));
    }

    public function actingAsSuperAdmin(){
        return $this->actingAs(User::factory()->create()->assignRole(Roles::SUPER_ADMIN->value));
    }

    public function actingAsUser(){
        return $this->actingAs(User::factory()->create()->assignRole(Roles::USER->value));
    }

    public function actingAsApiAdmin(){
        return Passport::actingAs(User::factory()->create()->assignRole(Roles::ADMIN->value));
    }

    public function actingAsApiModerator(){
        return Passport::actingAs(User::factory()->create()->assignRole(Roles::MODERATOR->value));
    }

    public function actingAsApiSuperAdmin(){
        return Passport::actingAs(User::factory()->create()->assignRole(Roles::SUPER_ADMIN->value));
    }

    public function actingAsApiUser(){
        return Passport::actingAs(User::factory()->create()->assignRole(Roles::USER->value));
    }

    public function assertUniqueConstraintFails(QueryException $e){
        $this->assertEquals(23000, $e->getCode());
        $this->assertStringContainsString('UNIQUE constraint failed', $e->getMessage());
    }

    public function simulateAllowedOrigin()
    {
        \Config::set('cors.allowed_origins', ['http://test.test']);
        $this->withHeaders(['origin' => 'http://test.test']);
    }
}
