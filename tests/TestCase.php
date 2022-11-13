<?php

namespace Tests;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\User;
use App\Enums\Roles;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function actingAsAdmin(){
        $this->actingAs(User::factory()->create()->assignRole(Roles::ADMIN->value));
    }

    public function actingAsModerator(){
        $this->actingAs(User::factory()->create()->assignRole(Roles::MODERATOR->value));
    }

    public function actingAsSuperAdmin(){
        $this->actingAs(User::factory()->create()->assignRole(Roles::SUPER_ADMIN->value));
    }

    public function actingAsUser(){
        $this->actingAs(User::factory()->create()->assignRole(Roles::USER->value));
    }

    public function assertUniqueConstraintFails(QueryException $e){
        $this->assertEquals(23000, $e->getCode());
        $this->assertStringContainsString('UNIQUE constraint failed', $e->getMessage());
    }
}
