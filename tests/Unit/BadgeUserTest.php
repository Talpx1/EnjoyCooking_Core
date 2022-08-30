<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use App\Models\BadgeUser;
use App\Models\User;
use App\Models\Badge;

class BadgeUserTest extends TestCase
{

    use RefreshDatabase;

    /**
     * @test
     */
    public function test_user_id_is_required(){
        $this->expectException(QueryException::class);
        BadgeUser::factory()->create(['user_id'=>null]);
    }

    public function test_badge_type_is_required(){
        $this->expectException(QueryException::class);
        BadgeUser::factory()->create(['badge_type'=>null]);
    }

    /**
     * @test
     */
    public function test_user_id_must_exists_in_users_table(){
        $user = User::factory()->create();
        BadgeUser::factory()->create(['user_id' => $user->id]);
        $this->assertDatabaseHas('badge_user', ['user_id'=>$user->id]);

        $this->expectException(QueryException::class);
        BadgeUser::factory()->create(['user_id' => 111]);
        $this->assertDatabaseMissing('badge_user', ['user_id'=>111]);
    }

    /**
     * @test
     */
    public function test_badge_id_must_exists_in_badges_table(){
        $badge = User::factory()->create();
        BadgeUser::factory()->create(['badge_id' => $badge->id]);
        $this->assertDatabaseHas('badge_user', ['badge_id'=>$badge->id]);

        $this->expectException(QueryException::class);
        BadgeUser::factory()->create(['badge_id' => 111]);
        $this->assertDatabaseMissing('badge_user', ['badge_id'=>111]);
    }

    /**
     * @test
     */
    public function test_badge_user_gets_deleted_if_user_gets_deleted(){
        $user = User::factory()->create();
        $badge_user = BadgeUser::factory()->create(['user_id' => $user->id]);
        $this->assertDatabaseHas('badge_user', ['user_id'=>$user->id]);

        $user->delete();
        $this->assertModelMissing($user);

        $this->assertDatabaseMissing('badge_user', ['user_id'=>$user->id]);

        $this->assertModelMissing($badge_user);
    }

    /**
     * @test
     */
    public function test_badge_user_gets_deleted_if_badge_gets_deleted(){
        $badge = Badge::factory()->create();
        $badge_user = BadgeUser::factory()->create(['badge_id' => $badge->id]);
        $this->assertDatabaseHas('badge_user', ['badge_id'=>$badge->id]);

        $badge->delete();
        $this->assertModelMissing($badge);

        $this->assertDatabaseMissing('badge_user', ['badge_id'=>$badge->id]);

        $this->assertModelMissing($badge_user);
    }

    /**
     * @test
     */
    public function test_combination_of_user_id_and_badge_id_must_be_unique(){
        $badge = Badge::factory()->create();
        $user = User::factory()->create();
        $user2 = User::factory()->create();

        BadgeUser::factory()->create(['badge_id' => $badge->id, 'user_id' => $user->id]);
        $this->assertDatabaseHas('badge_user', ['badge_id' => $badge->id, 'user_id' => $user->id]);

        BadgeUser::factory()->create(['badge_id' => $badge->id, 'user_id' => $user2->id]);
        $this->assertDatabaseHas('badge_user', ['badge_id' => $badge->id, 'user_id' => $user2->id]);

        BadgeUser::factory()->create(['badge_id' => Badge::factory()->create()->id, 'user_id' => $user->id]);

        $this->expectException(QueryException::class);
        BadgeUser::factory()->create(['badge_id' => $badge->id, 'user_id' => $user->id]);
    }
}
