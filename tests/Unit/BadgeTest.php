<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Badge;
use Illuminate\Database\QueryException;
use App\Models\User;
use App\Models\BadgeUser;
use Illuminate\Database\Eloquent\Collection;

class BadgeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function test_title_is_required(){
        $this->expectException(QueryException::class);
        Badge::factory()->create(['title'=>null]);
    }

    /**
     * @test
     */
    public function test_title_must_be_unique(){
        Badge::factory()->create(['title'=>'test']);
        $this->expectException(QueryException::class);
        Badge::factory()->create(['title'=>'test']);
    }

    /**
     * @test
     */
    public function test_description_is_nullable(){
        Badge::factory()->create(['title'=>'test123', 'description'=>null]);
        $this->assertDatabaseHas('badges', ['title'=>'test123', 'description'=>null]);
    }

    /**
     * @test
     */
    public function test_badge_belongs_to_many_users(){
        $badge = Badge::factory()->create();
        $users = User::factory(3)->create()->each(fn($user)=>BadgeUser::factory()->create(['user_id'=>$user->id,'badge_id'=>$badge->id]));
        $other_users = User::factory(5)->create()->each(fn($user)=>BadgeUser::factory()->create(['user_id'=>$user->id,'badge_id'=>Badge::factory()->create()->id]));

        $users->each(fn($user) => $this->assertDatabaseHas('badge_user', ['user_id'=>$user->id,'badge_id'=>$badge->id]));
        $other_users->each(fn($user) => $this->assertDatabaseHas('badge_user', ['user_id'=>$user->id]));
        $other_users->each(fn($user) => $this->assertDatabaseMissing('badge_user', ['user_id'=>$user->id, 'badge_id'=>$badge->id]));

        $this->assertNotNull($badge->users);
        $this->assertInstanceOf(Collection::class, $badge->users);
        $this->assertCount(3, $badge->users);
        $badge->users->each(fn($user)=>$this->assertInstanceOf(User::class, $user));

        $badge->users->each(fn($user)=>$this->assertTrue($users->contains($user)));
        $badge->users->each(fn($user)=>$this->assertFalse($other_users->contains($user)));
    }
}
