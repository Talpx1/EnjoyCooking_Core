<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Utils\AuthUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Auth;

/**
 * @coversDefaultClass App\Utils\AuthUtils
 */
class AuthUtilsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     *
     * @covers ::isAdmin
     */
    public function test_it_checks_if_is_admin(){
        $this->seed(RoleSeeder::class);
        $this->actingAsModerator();
        $this->assertFalse(AuthUtils::isAdmin());
        $this->actingAsAdmin();
        $this->assertTrue(AuthUtils::isAdmin());
    }

    /**
     * @test
     *
     * @covers ::isModerator
     */
    public function test_it_checks_if_is_moderator(){
        $this->seed(RoleSeeder::class);
        $this->actingAsAdmin();
        $this->assertFalse(AuthUtils::isModerator());
        $this->actingAsModerator();
        $this->assertTrue(AuthUtils::isModerator());
    }

    /**
     * @test
     *
     * @covers ::isSuperAdmin
     */
    public function test_it_checks_if_is_super_admin(){
        $this->seed(RoleSeeder::class);
        $this->actingAsAdmin();
        $this->assertFalse(AuthUtils::isSuperAdmin());
        $this->actingAsSuperAdmin();
        $this->assertTrue(AuthUtils::isSuperAdmin());
    }

    /**
     * @test
     *
     * @covers ::isUser
     */
    public function test_it_checks_if_is_user(){
        $this->seed(RoleSeeder::class);
        $this->actingAsAdmin();
        $this->assertFalse(AuthUtils::isUser());
        $this->actingAsUser();
        $this->assertTrue(AuthUtils::isUser());
    }

    /**
     * @test
     *
     * @covers ::isLoggedIn
     */
    public function test_it_checks_if_is_logged_in(){
        $this->seed(RoleSeeder::class);
        $this->assertFalse(AuthUtils::isLoggedIn());
        $this->actingAsUser();
        $this->assertTrue(AuthUtils::isLoggedIn());
        $this->actingAsSuperAdmin();
        $this->assertTrue(AuthUtils::isLoggedIn());
    }
}
