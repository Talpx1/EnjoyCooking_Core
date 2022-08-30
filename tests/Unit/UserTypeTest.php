<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\UserType;
use Illuminate\Database\QueryException;
use Database\Seeders\UserTypeSeeder;
use App\Enums\UserTypes;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserTypeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function test_name_is_required(){
        $this->expectException(QueryException::class);
        UserType::factory()->create(['name'=>null]);
    }

    /**
     * @test
     */
    public function test_name_must_be_unique(){
        UserType::factory()->create(['name'=>'test']);
        $this->expectException(QueryException::class);
        UserType::factory()->create(['name'=>'test']);
    }

    /**
     * @test
     */
    public function test_user_type_has_many_users(){
        $user_type_1 = UserType::factory()->create();
        $user_type_2 = UserType::factory()->create();
        $users_1 = User::factory(2)->create(['user_type_id' => $user_type_1]);
        $users_2 = User::factory(4)->create(['user_type_id' => $user_type_2]);

        $this->assertNotNull($user_type_1->users);
        $this->assertNotNull($user_type_2->users);

        $this->assertInstanceOf(Collection::class, $user_type_1->users);
        $this->assertInstanceOf(Collection::class, $user_type_2->users);

        $this->assertCount(2, $user_type_1->users);
        $this->assertCount(4, $user_type_2->users);

        $user_type_1->users->each(fn($user) => $this->assertInstanceOf(User::class, $user));
        $user_type_2->users->each(fn($user) => $this->assertInstanceOf(User::class, $user));

        $users_1->each(fn($user) => $this->assertTrue($user_type_1->users->contains($user)));
        $users_2->each(fn($user) => $this->assertFalse($user_type_1->users->contains($user)));

        $users_2->each(fn($user) => $this->assertTrue($user_type_2->users->contains($user)));
        $users_1->each(fn($user) => $this->assertFalse($user_type_2->users->contains($user)));
    }
}
