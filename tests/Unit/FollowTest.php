<?php

namespace Tests\Unit;

use App\Models\Follow;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\ModerationStatusSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FollowTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;
    protected $seeder = ModerationStatusSeeder::class;

    /**
     * @test
     */
    public function test_followable_id_is_required(){
        $this->expectException(QueryException::class);
        Follow::factory()->create(['followable_id'=>null]);
    }

    /**
     * @test
     */
    public function test_followable_type_is_required(){
        $this->expectException(QueryException::class);
        Follow::factory()->create(['followable_type'=>null]);
    }

    /**
     * @test
     */
    public function test_user_id_is_required(){
        $this->expectException(QueryException::class);
        Follow::factory()->create(['user_id'=>null]);
    }

    /**
     * @test
     */
    public function test_user_id_must_exists_in_users_table(){

        $user = User::factory()->create();
        Follow::factory()->create(['user_id' => $user->id]);
        $this->assertDatabaseHas('follows', ['user_id'=>$user->id]);

        $this->expectException(QueryException::class);
        Follow::factory()->create(['user_id' => 111]);
        $this->assertDatabaseMissing('follows', ['user_id'=>111]);
    }

    /**
     * @test
     */
    public function test_follow_gets_deleted_if_user_gets_deleted(){

        $user = User::factory()->create();
        $follow = Follow::factory()->create(['user_id' => $user->id]);
        $this->assertDatabaseHas('follows', ['user_id'=>$user->id]);

        $user->delete();
        $this->assertModelMissing($user);

        $this->assertDatabaseMissing('follows', ['user_id'=>$user->id]);

        $this->assertModelMissing($follow);
    }

    /**
     * @test
     */
    public function test_follow_belongs_to_user(){

        $user = User::factory()->create();
        $follow = Follow::factory()->create(['user_id' => $user->id]);
        $this->assertNotNull($follow->user);
        $this->assertInstanceOf(User::class, $follow->user);
        $this->assertEquals($user->id, $follow->user->id);
    }

    /**
     * @test
     */
    public function test_morphs_to_followable(){

        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $follow1 = Follow::factory()->create(['followable_id' => $user->id,'followable_type' => $user::class]);
        $follow2 = Follow::factory()->create(['followable_id' => $tag->id,'followable_type' => $tag::class]);

        $this->assertNotNull($follow1->followable);
        $this->assertInstanceOf($user::class, $follow1->followable);
        $this->assertEquals($user->id, $follow1->followable->id);

        $this->assertNotNull($follow2->followable);
        $this->assertInstanceOf($tag::class, $follow2->followable);
        $this->assertEquals($tag->id, $follow2->followable->id);
    }

    /**
     * @test
     */
    public function test_combination_of_followable_id_followable_type_user_id_must_be_unique(){

        $tag = Tag::factory()->create();
        $user = User::factory()->create();
        $user2 = User::factory()->create();

        Follow::factory()->create(['followable_id' => $tag->id,'followable_type' => $tag::class, 'user_id' => $user->id]);
        $this->assertDatabaseHas('follows', ['followable_id' => $tag->id,'followable_type' => $tag::class, 'user_id' => $user->id]);

        Follow::factory()->create(['followable_id' => $tag->id,'followable_type' => $tag::class, 'user_id' => $user2->id]);
        $this->assertDatabaseHas('follows', ['followable_id' => $tag->id,'followable_type' => $tag::class, 'user_id' => $user2->id]);

        Follow::factory()->create(['followable_id' => User::factory()->create()->id,'followable_type' => User::class, 'user_id' => $user->id]);

        $this->expectException(QueryException::class);
        Follow::factory()->create(['followable_id' => $tag->id,'followable_type' => $tag::class, 'user_id' => $user->id]);
    }
}
