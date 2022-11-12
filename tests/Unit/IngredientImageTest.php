<?php

namespace Tests\Unit;

use App\Models\Ingredient;
use App\Models\IngredientImage;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IngredientImageTest extends TestCase{
    use RefreshDatabase;

    /**
     * @test
     */
    public function test_path_is_required(){
        $this->expectException(QueryException::class);
        IngredientImage::factory()->create(['path'=>null]);
    }

    /**
     * @test
     */
    public function test_path_must_be_unique(){
        IngredientImage::factory()->create(['path'=>'test']);
        $this->expectException(QueryException::class);
        IngredientImage::factory()->create(['path'=>'test']);
    }

    /**
     * @test
     */
    public function test_thumbnail_path_is_required(){
        $this->expectException(QueryException::class);
        IngredientImage::factory()->create(['thumbnail_path'=>null]);
    }

    /**
     * @test
     */
    public function test_thumbnail_path_must_be_unique(){
        IngredientImage::factory()->create(['thumbnail_path'=>'test']);
        $this->expectException(QueryException::class);
        IngredientImage::factory()->create(['thumbnail_path'=>'test']);
    }

    /**
     * @test
     */
    public function test_description_is_nullable(){
        IngredientImage::factory()->create(['path' => 'test', 'description'=>null]);
        $this->assertDatabaseHas('ingredient_images', ['path'=>'test', 'description'=>null]);
    }

    /**
     * @test
     */
    public function test_ingredient_id_is_required(){
        $this->expectException(QueryException::class);
        IngredientImage::factory()->create(['path' => 'test', 'ingredient_id'=>null]);
    }

    /**
     * @test
     */
    public function test_ingredient_id_must_exists_in_ingredients_table(){
        $ingredient = Ingredient::factory()->create();
        IngredientImage::factory()->create(['path' => 'test', 'ingredient_id' => $ingredient->id]);
        $this->assertDatabaseHas('ingredient_images', ['path'=>'test', 'ingredient_id'=>$ingredient->id]);

        $this->expectException(QueryException::class);
        IngredientImage::factory()->create(['path' => 'test 2', 'ingredient_id' => 111]);
    }

    /**
     * @test
     */
    public function test_ingredient_image_gets_deleted_if_parent_ingredient_gets_deleted(){
        $ingredient = Ingredient::factory()->create(['name'=>'test']);
        $image = IngredientImage::factory()->create(['path' => 'test1', 'ingredient_id' => $ingredient->id]);

        $this->assertDatabaseHas('ingredients', ['name'=>'test']);
        $this->assertDatabaseHas('ingredient_images', ['path'=>'test1', 'ingredient_id'=>$ingredient->id]);
        $this->assertEquals($ingredient->id, $image->ingredient_id);

        $ingredient->delete();

        $this->assertModelMissing($ingredient);
        $this->assertModelMissing($image);

        $this->assertDatabaseMissing('ingredients', ['name'=>'test']);
        $this->assertDatabaseMissing('ingredient_images', ['path'=>'test1', 'ingredient_id'=>$ingredient->id]);
    }

    /**
     * @test
     */
    public function test_ingredient_image_belongs_to_ingredient(){
        $ingredient = Ingredient::factory()->create(['name' => 'test']);
        $ingredient_image = IngredientImage::factory()->create(['path' => 'test', 'ingredient_id' => $ingredient->id]);
        $this->assertNotNull($ingredient_image->ingredient);
        $this->assertInstanceOf(Ingredient::class, $ingredient_image->ingredient);
        $this->assertEquals($ingredient_image->ingredient->id, $ingredient->id);
    }

    /**
     * @test
     */
    public function test_user_id_is_nullable(){
        IngredientImage::factory()->create(['path' => 'test', 'user_id'=>null]);
        $this->assertDatabaseHas('ingredient_images', ['path'=>'test', 'user_id'=>null]);
    }

    /**
     * @test
     */
    public function test_user_id_must_exists_in_users_table(){
        $user = Ingredient::factory()->create();
        IngredientImage::factory()->create(['path' => 'test', 'user_id' => $user->id]);
        $this->assertDatabaseHas('ingredient_images', ['path'=>'test', 'user_id'=>$user->id]);

        $this->expectException(QueryException::class);
        IngredientImage::factory()->create(['path' => 'test 2', 'user_id' => 111]);
    }

    /**
     * @test
     */
    public function test_ingredient_image_belongs_to_user(){
        $user = User::factory()->create(['first_name' => 'test']);
        $ingredient_image = IngredientImage::factory()->create(['path' => 'test', 'user_id' => $user->id]);
        $this->assertNotNull($ingredient_image->user);
        $this->assertInstanceOf(User::class, $ingredient_image->user);
        $this->assertEquals($ingredient_image->user->id, $user->id);
    }

    /**
     * @test
     */
    public function test_user_id_gets_set_as_null_if_user_gets_deleted(){
        $user = User::factory()->create(['first_name' => 'user_test']);
        $ingredient_image = IngredientImage::factory()->create(['path' => 'ingredient_image_test', 'user_id' => $user->id]);

        $this->assertDatabaseHas('users', ['first_name'=>'user_test']);
        $this->assertDatabaseHas('ingredient_images', ['path'=>'ingredient_image_test', 'user_id'=>$user->id]);
        $this->assertEquals($user->id, $ingredient_image->user_id);

        $user->delete();

        $this->assertModelMissing($user);
        $this->assertModelExists($ingredient_image);

        $this->assertDatabaseMissing('users', ['first_name'=>'user_test']);
        $this->assertDatabaseMissing('ingredient_images', ['path'=>'ingredient_image_test', 'user_id'=>$user->id]);

        $this->assertNull($ingredient_image->fresh()->user_id);
        $this->assertDatabaseHas('ingredient_images', ['path'=>'ingredient_image_test', 'user_id'=>null]);
    }
}
