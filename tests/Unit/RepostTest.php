<?php

namespace Tests\Unit;

use App\Models\Like;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use App\Models\Repost;
use App\Models\User;
use App\Models\Category;
use App\Models\DifficultyLevel;
use App\Models\Recipe;

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

    /**
     * @test
     */
    public function test_repostable_type_is_required(){
        $this->expectException(QueryException::class);
        Repost::factory()->create(['repostable_type'=>null]);
    }

    /**
     * @test
     */
    public function test_user_id_is_required(){
        $this->expectException(QueryException::class);
        Repost::factory()->create(['user_id'=>null]);
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
        //TODO: replace difficulty level with other repostables
        $recipe = Recipe::factory()->create();
        $difficulty_level = DifficultyLevel::factory()->create();

        $repost1 = Repost::factory()->create(['repostable_id' => $recipe->id,'repostable_type' => $recipe::class]);
        $repost2 = Repost::factory()->create(['repostable_id' => $difficulty_level->id,'repostable_type' => $difficulty_level::class]);

        $this->assertNotNull($repost1->repostable);
        $this->assertInstanceOf($recipe::class, $repost1->repostable);
        $this->assertEquals($recipe->id, $repost1->repostable->id);

        $this->assertNotNull($repost2->repostable);
        $this->assertInstanceOf($difficulty_level::class, $repost2->repostable);
        $this->assertEquals($difficulty_level->id, $repost2->repostable->id);
    }

    /**
     * @test
     */
    public function test_combination_of_repostable_id_repostable_type_user_id_must_be_unique(){
        $recipe = Recipe::factory()->create();
        $user = User::factory()->create();
        $user2 = User::factory()->create();

        Repost::factory()->create(['repostable_id' => $recipe->id,'repostable_type' => $recipe::class, 'user_id' => $user->id]);
        $this->assertDatabaseHas('reposts', ['repostable_id' => $recipe->id,'repostable_type' => $recipe::class, 'user_id' => $user->id]);

        Repost::factory()->create(['repostable_id' => $recipe->id,'repostable_type' => $recipe::class, 'user_id' => $user2->id]);
        $this->assertDatabaseHas('reposts', ['repostable_id' => $recipe->id,'repostable_type' => $recipe::class, 'user_id' => $user2->id]);

        Repost::factory()->create(['repostable_id' => Category::factory()->create()->id,'repostable_type' => Category::class, 'user_id' => $user->id]);

        $this->expectException(QueryException::class);
        Repost::factory()->create(['repostable_id' => $recipe->id,'repostable_type' => $recipe::class, 'user_id' => $user->id]);
    }

    /**
     * @test
     */
    public function test_when_repost_gets_deleted_its_related_records_in_likes_table_get_deleted(){
        $repost = Repost::factory()->create();
        $likes = collect([
            Like::factory()->create(['likeable_id'=>$repost->id, 'likeable_type'=>$repost::class, 'user_id'=>User::factory()->create()->id]),
            Like::factory()->create(['likeable_id'=>$repost->id, 'likeable_type'=>$repost::class, 'user_id'=>User::factory()->create()->id]),
            Like::factory()->create(['likeable_id'=>$repost->id, 'likeable_type'=>$repost::class, 'user_id'=>User::factory()->create()->id]),
        ]);

        $repost->delete();
        $this->assertModelMissing($repost);
        $this->assertDatabaseMissing('reposts', ['title'=>$repost->id]);
        $this->assertDatabaseMissing('likes', ['likeable_id'=>$repost->id, 'likeable_type'=>$repost::class]);

        $likes->each(fn($like) => $this->assertModelMissing($like));
    }

    /**
     * @test
     */
    public function test_repost_morphs_many_likes(){
        $repost = Repost::factory()->create();
        $repost_likes = collect([
            Like::factory()->create(['likeable_id' => $repost->id, 'likeable_type' => $repost::class, 'user_id' => User::factory()->create()->id]),
            Like::factory()->create(['likeable_id' => $repost->id, 'likeable_type' => $repost::class, 'user_id' => User::factory()->create()->id]),
        ]);
        $other_repost_likes = collect([
            Like::factory()->create(['likeable_id'=>Repost::factory()->create()->id, 'likeable_type' => $repost::class, 'user_id' => User::factory()->create()->id]),
            Like::factory()->create(['likeable_id'=>Repost::factory()->create()->id, 'likeable_type' => $repost::class, 'user_id' => User::factory()->create()->id]),
            Like::factory()->create(['likeable_id'=>Repost::factory()->create()->id, 'likeable_type' => $repost::class, 'user_id' => User::factory()->create()->id]),
            Like::factory()->create(['likeable_id'=>Repost::factory()->create()->id, 'likeable_type' => $repost::class, 'user_id' => User::factory()->create()->id]),
        ]);

        $this->assertNotNull($repost->likes);

        $this->assertInstanceOf(Collection::class, $repost->likes);
        $repost->likes->each(fn($like) => $this->assertInstanceOf(Like::class, $like));

        $this->assertCount(2, $repost->likes);

        $repost_likes->each(fn($like) => $this->assertTrue($repost->likes->contains($like)));
        $other_repost_likes->each(fn($like) => $this->assertFalse($repost->likes->contains($like)));
    }
}
