<?php

namespace Tests;

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
}
