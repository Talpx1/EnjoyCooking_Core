<?php

namespace Tests\Unit;

use App\Models\Like;
use App\Models\Recipe;
use App\Models\Snack;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeTest extends TestCase{
    use RefreshDatabase;

    /**
     * @test
     */
    public function test_likeable_id_is_required(){
        $this->expectException(QueryException::class);
        Like::factory()->create(['likeable_id'=>null]);
    }

    /**
     * @test
     */
    public function test_likeable_type_is_required(){
        $this->expectException(QueryException::class);
        Like::factory()->create(['likeable_type'=>null]);
    }

    /**
     * @test
     */
    public function test_user_id_is_required(){
        $this->expectException(QueryException::class);
        Like::factory()->create(['user_id'=>null]);
    }

    /**
     * @test
     */
    public function test_user_id_must_exists_in_users_table(){
        $user = User::factory()->create();
        Like::factory()->create(['user_id' => $user->id]);
        $this->assertDatabaseHas('likes', ['user_id'=>$user->id]);

        $this->expectException(QueryException::class);
        Like::factory()->create(['user_id' => 111]);
        $this->assertDatabaseMissing('likes', ['user_id'=>111]);
    }

    /**
     * @test
     */
    public function test_like_gets_deleted_if_user_gets_deleted(){
        $user = User::factory()->create();
        $like = Like::factory()->create(['user_id' => $user->id]);
        $this->assertDatabaseHas('likes', ['user_id'=>$user->id]);

        $user->delete();
        $this->assertModelMissing($user);

        $this->assertDatabaseMissing('likes', ['user_id'=>$user->id]);

        $this->assertModelMissing($like);
    }

    /**
     * @test
     */
    public function test_like_belongs_to_user(){
        $user = User::factory()->create();
        $like = Like::factory()->create(['user_id' => $user->id]);
        $this->assertNotNull($like->user);
        $this->assertInstanceOf(User::class, $like->user);
        $this->assertEquals($user->id, $like->user->id);
    }

    /**
     * @test
     */
    public function test_morphs_to_likeable(){
        $recipe = Recipe::factory()->create();
        $snack = Snack::factory()->create();

        $like1 = Like::factory()->create(['likeable_id' => $recipe->id,'likeable_type' => $recipe::class]);
        $like2 = Like::factory()->create(['likeable_id' => $snack->id,'likeable_type' => $snack::class]);

        $this->assertNotNull($like1->likeable);
        $this->assertInstanceOf($recipe::class, $like1->likeable);
        $this->assertEquals($recipe->id, $like1->likeable->id);

        $this->assertNotNull($like2->likeable);
        $this->assertInstanceOf($snack::class, $like2->likeable);
        $this->assertEquals($snack->id, $like2->likeable->id);
    }

    /**
     * @test
     */
    public function test_combination_of_likeable_id_likeable_type_user_id_must_be_unique(){
        $recipe = Recipe::factory()->create();
        $user = User::factory()->create();
        $user2 = User::factory()->create();

        Like::factory()->create(['likeable_id' => $recipe->id,'likeable_type' => $recipe::class, 'user_id' => $user->id]);
        $this->assertDatabaseHas('likes', ['likeable_id' => $recipe->id,'likeable_type' => $recipe::class, 'user_id' => $user->id]);

        Like::factory()->create(['likeable_id' => $recipe->id,'likeable_type' => $recipe::class, 'user_id' => $user2->id]);
        $this->assertDatabaseHas('likes', ['likeable_id' => $recipe->id,'likeable_type' => $recipe::class, 'user_id' => $user2->id]);

        Like::factory()->create(['likeable_id' => Snack::factory()->create()->id,'likeable_type' => Snack::class, 'user_id' => $user->id]);

        $this->expectException(QueryException::class);
        Like::factory()->create(['likeable_id' => $recipe->id,'likeable_type' => $recipe::class, 'user_id' => $user->id]);
    }
}
