<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use App\Models\ProfessionGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ProfessionGroupTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function test_name_is_required(){
        $this->expectException(QueryException::class);
        ProfessionGroup::factory()->create(['name'=>null]);
    }

    /**
     * @test
     */
    public function test_name_must_be_unique(){
        ProfessionGroup::factory()->create(['name'=>'test']);
        $this->expectException(QueryException::class);
        ProfessionGroup::factory()->create(['name'=>'test']);
    }

    /**
     * @test
     */
    public function test_profession_group_has_many_users(){
        $profession_group_1 = ProfessionGroup::factory()->create();
        $profession_group_2 = ProfessionGroup::factory()->create();
        $users_1 = User::factory(2)->create(['profession_group_id' => $profession_group_1]);
        $users_2 = User::factory(4)->create(['profession_group_id' => $profession_group_2]);

        $this->assertNotNull($profession_group_1->users);
        $this->assertNotNull($profession_group_2->users);

        $this->assertInstanceOf(Collection::class, $profession_group_1->users);
        $this->assertInstanceOf(Collection::class, $profession_group_2->users);

        $this->assertCount(2, $profession_group_1->users);
        $this->assertCount(4, $profession_group_2->users);

        $profession_group_1->users->each(fn($user) => $this->assertInstanceOf(User::class, $user));
        $profession_group_2->users->each(fn($user) => $this->assertInstanceOf(User::class, $user));

        $users_1->each(fn($user) => $this->assertTrue($profession_group_1->users->contains($user)));
        $users_2->each(fn($user) => $this->assertFalse($profession_group_1->users->contains($user)));

        $users_2->each(fn($user) => $this->assertTrue($profession_group_2->users->contains($user)));
        $users_1->each(fn($user) => $this->assertFalse($profession_group_2->users->contains($user)));
    }
}
