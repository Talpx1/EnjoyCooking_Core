<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use App\Models\Repost;
use App\Models\User;
use App\Models\Category;
use App\Models\DifficultyLevel;

class RepostTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function test_repostable_id_is_required(){
        $this->expectException(QueryException::class);
        Repost::factory()->create(['repostable_id'=>null]);
    }

    public function test_repostable_type_is_required(){
        $this->expectException(QueryException::class);
        Repost::factory()->create(['repostable_type'=>null]);
    }

    /**
     * @test
     */
    public function test_user_id_must_exists_in_users_table(){
        $user = User::factory()->create();
        Repost::factory()->create(['user_id' => $user->id]);
        $this->assertDatabaseHas('reposts', ['user_id'=>$user->id]);

        $this->expectException(QueryException::class);
        Repost::factory()->create(['user_id' => 111]);
        $this->assertDatabaseMissing('reposts', ['user_id'=>111]);
    }

    /**
     * @test
     */
    public function test_repost_gets_deleted_if_user_gets_deleted(){
        $user = User::factory()->create();
        $repost = Repost::factory()->create(['user_id' => $user->id]);
        $this->assertDatabaseHas('reposts', ['user_id'=>$user->id]);

        $user->delete();
        $this->assertModelMissing($user);

        $this->assertDatabaseMissing('reposts', ['user_id'=>$user->id]);

        $this->assertModelMissing($repost);
    }

    /**
     * @test
     */
    public function test_repost_belongs_to_user(){
        $user = User::factory()->create();
        $repost = Repost::factory()->create(['user_id' => $user->id]);
        $this->assertNotNull($repost->user);
        $this->assertInstanceOf(User::class, $repost->user);
        $this->assertEquals($user->id, $repost->user->id);
    }

    /**
     * @test
     */
    public function test_morphs_to_repostable(){
        //TODO: replace category and difficulty level with recipe and other repostables
        $category = Category::factory()->create();
        $difficulty_level = DifficultyLevel::factory()->create();

        $repost1 = Repost::factory()->create(['repostable_id' => $category->id,'repostable_type' => $category::class]);
        $repost2 = Repost::factory()->create(['repostable_id' => $difficulty_level->id,'repostable_type' => $difficulty_level::class]);

        $this->assertNotNull($repost1->repostable);
        $this->assertInstanceOf($category::class, $repost1->repostable);
        $this->assertEquals($category->id, $repost1->repostable->id);

        $this->assertNotNull($repost2->repostable);
        $this->assertInstanceOf($difficulty_level::class, $repost2->repostable);
        $this->assertEquals($difficulty_level->id, $repost2->repostable->id);
    }

    /**
     * @test
     */
    public function test_combination_of_repostable_id_repostable_type_user_id_must_be_unique(){
        //TODO: replace category with recipe
        $category = Category::factory()->create();
        $user = User::factory()->create();
        $user2 = User::factory()->create();

        Repost::factory()->create(['repostable_id' => $category->id,'repostable_type' => $category::class, 'user_id' => $user->id]);
        $this->assertDatabaseHas('reposts', ['repostable_id' => $category->id,'repostable_type' => $category::class, 'user_id' => $user->id]);

        Repost::factory()->create(['repostable_id' => $category->id,'repostable_type' => $category::class, 'user_id' => $user2->id]);
        $this->assertDatabaseHas('reposts', ['repostable_id' => $category->id,'repostable_type' => $category::class, 'user_id' => $user2->id]);

        Repost::factory()->create(['repostable_id' => Category::factory()->create()->id,'repostable_type' => Category::class, 'user_id' => $user->id]);

        $this->expectException(QueryException::class);
        Repost::factory()->create(['repostable_id' => $category->id,'repostable_type' => $category::class, 'user_id' => $user->id]);
    }
}