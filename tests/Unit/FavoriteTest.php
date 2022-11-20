<?php

namespace Tests\Unit;

use App\Models\Favorite;
use App\Models\Recipe;
use App\Models\Snack;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function test_favoritable_id_is_required(){
        $this->expectException(QueryException::class);
        Favorite::factory()->create(['favoritable_id'=>null]);
    }

    /**
     * @test
     */
    public function test_favoritable_type_is_required(){
        $this->expectException(QueryException::class);
        Favorite::factory()->create(['favoritable_type'=>null]);
    }

    /**
     * @test
     */
    public function test_user_id_is_required(){
        $this->expectException(QueryException::class);
        Favorite::factory()->create(['user_id'=>null]);
    }

    /**
     * @test
     */
    public function test_user_id_must_exists_in_users_table(){
        $user = User::factory()->create();
        Favorite::factory()->create(['user_id' => $user->id]);
        $this->assertDatabaseHas('favorites', ['user_id'=>$user->id]);

        $this->expectException(QueryException::class);
        Favorite::factory()->create(['user_id' => 111]);
        $this->assertDatabaseMissing('favorites', ['user_id'=>111]);
    }

    /**
     * @test
     */
    public function test_favorite_gets_deleted_if_user_gets_deleted(){
        $user = User::factory()->create();
        $favorite = Favorite::factory()->create(['user_id' => $user->id]);
        $this->assertDatabaseHas('favorites', ['user_id'=>$user->id]);

        $user->delete();
        $this->assertModelMissing($user);

        $this->assertDatabaseMissing('favorites', ['user_id'=>$user->id]);

        $this->assertModelMissing($favorite);
    }

    /**
     * @test
     */
    public function test_favorite_belongs_to_user(){
        $user = User::factory()->create();
        $favorite = Favorite::factory()->create(['user_id' => $user->id]);
        $this->assertNotNull($favorite->user);
        $this->assertInstanceOf(User::class, $favorite->user);
        $this->assertEquals($user->id, $favorite->user->id);
    }

    /**
     * @test
     */
    public function test_morphs_to_favoritable(){
        $recipe = Recipe::factory()->create();
        $snack = Snack::factory()->create();

        $favorite1 = Favorite::factory()->create(['favoritable_id' => $recipe->id,'favoritable_type' => $recipe::class]);
        $favorite2 = Favorite::factory()->create(['favoritable_id' => $snack->id,'favoritable_type' => $snack::class]);

        $this->assertNotNull($favorite1->favoritable);
        $this->assertInstanceOf($recipe::class, $favorite1->favoritable);
        $this->assertEquals($recipe->id, $favorite1->favoritable->id);

        $this->assertNotNull($favorite2->favoritable);
        $this->assertInstanceOf($snack::class, $favorite2->favoritable);
        $this->assertEquals($snack->id, $favorite2->favoritable->id);
    }

    /**
     * @test
     */
    public function test_combination_of_favoritable_id_favoritable_type_user_id_must_be_unique(){
        $recipe = Recipe::factory()->create();
        $user = User::factory()->create();
        $user2 = User::factory()->create();

        Favorite::factory()->create(['favoritable_id' => $recipe->id,'favoritable_type' => $recipe::class, 'user_id' => $user->id]);
        $this->assertDatabaseHas('favorites', ['favoritable_id' => $recipe->id,'favoritable_type' => $recipe::class, 'user_id' => $user->id]);

        Favorite::factory()->create(['favoritable_id' => $recipe->id,'favoritable_type' => $recipe::class, 'user_id' => $user2->id]);
        $this->assertDatabaseHas('favorites', ['favoritable_id' => $recipe->id,'favoritable_type' => $recipe::class, 'user_id' => $user2->id]);

        Favorite::factory()->create(['favoritable_id' => Snack::factory()->create()->id,'favoritable_type' => Snack::class, 'user_id' => $user->id]);

        $this->expectException(QueryException::class);
        Favorite::factory()->create(['favoritable_id' => $recipe->id,'favoritable_type' => $recipe::class, 'user_id' => $user->id]);
    }
}
