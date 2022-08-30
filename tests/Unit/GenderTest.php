<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use App\Models\Gender;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class GenderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function test_name_is_required(){
        $this->expectException(QueryException::class);
        Gender::factory()->create(['name'=>null]);
    }

    /**
     * @test
     */
    public function test_name_must_be_unique(){
        Gender::factory()->create(['name'=>'test']);
        $this->expectException(QueryException::class);
        Gender::factory()->create(['name'=>'test']);
    }

    /**
     * @test
     */
    public function test_gender_has_many_users(){
        $gender_1 = Gender::factory()->create();
        $gender_2 = Gender::factory()->create();
        $users_1 = User::factory(2)->create(['gender_id' => $gender_1]);
        $users_2 = User::factory(4)->create(['gender_id' => $gender_2]);

        $this->assertNotNull($gender_1->users);
        $this->assertNotNull($gender_2->users);

        $this->assertInstanceOf(Collection::class, $gender_1->users);
        $this->assertInstanceOf(Collection::class, $gender_2->users);

        $this->assertCount(2, $gender_1->users);
        $this->assertCount(4, $gender_2->users);

        $gender_1->users->each(fn($user) => $this->assertInstanceOf(User::class, $user));
        $gender_2->users->each(fn($user) => $this->assertInstanceOf(User::class, $user));

        $users_1->each(fn($user) => $this->assertTrue($gender_1->users->contains($user)));
        $users_2->each(fn($user) => $this->assertFalse($gender_1->users->contains($user)));

        $users_2->each(fn($user) => $this->assertTrue($gender_2->users->contains($user)));
        $users_1->each(fn($user) => $this->assertFalse($gender_2->users->contains($user)));
    }
}
