<?php

namespace Tests\Unit;

use App\Models\Recipe;
use App\Models\RecipeImage;
use Database\Seeders\ModerationStatusSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeImageTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;
    protected $seeder = ModerationStatusSeeder::class;

    /**
     * @test
     */
    public function test_path_is_required(){
        $this->expectException(QueryException::class);
        RecipeImage::factory()->create(['path'=>null]);
    }

    /**
     * @test
     */
    public function test_path_must_be_unique(){
        RecipeImage::factory()->create(['path'=>'test']);
        $this->expectException(QueryException::class);
        RecipeImage::factory()->create(['path'=>'test']);
    }

    /**
     * @test
     */
    public function test_thumbnail_path_is_required(){
        $this->expectException(QueryException::class);
        RecipeImage::factory()->create(['thumbnail_path'=>null]);
    }

    /**
     * @test
     */
    public function test_thumbnail_path_must_be_unique(){
        RecipeImage::factory()->create(['thumbnail_path'=>'test']);
        $this->expectException(QueryException::class);
        RecipeImage::factory()->create(['thumbnail_path'=>'test']);
    }

    /**
     * @test
     */
    public function test_description_is_nullable(){
        RecipeImage::factory()->create(['path' => 'test', 'description'=>null]);
        $this->assertDatabaseHas('recipe_images', ['path'=>'test', 'description'=>null]);
    }

    /**
     * @test
     */
    public function test_recipe_id_is_required(){
        $this->expectException(QueryException::class);
        RecipeImage::factory()->create(['path' => 'test', 'recipe_id'=>null]);
    }

    /**
     * @test
     */
    public function test_recipe_id_must_exists_in_recipes_table(){
        $recipe = Recipe::factory()->create();
        RecipeImage::factory()->create(['path' => 'test', 'recipe_id' => $recipe->id]);
        $this->assertDatabaseHas('recipe_images', ['path'=>'test', 'recipe_id'=>$recipe->id]);

        $this->expectException(QueryException::class);
        RecipeImage::factory()->create(['path' => 'test 2', 'recipe_id' => 111]);
    }

    /**
     * @test
     */
    public function test_recipe_image_gets_deleted_if_parent_recipe_gets_deleted(){
        $recipe = Recipe::factory()->create(['title'=>'test']);
        $image = RecipeImage::factory()->create(['path' => 'test1', 'recipe_id' => $recipe->id]);

        $this->assertDatabaseHas('recipes', ['title'=>'test']);
        $this->assertDatabaseHas('recipe_images', ['path'=>'test1', 'recipe_id'=>$recipe->id]);
        $this->assertEquals($recipe->id, $image->recipe_id);

        $recipe->delete();

        $this->assertModelMissing($recipe);
        $this->assertModelMissing($image);

        $this->assertDatabaseMissing('recipes', ['title'=>'test']);
        $this->assertDatabaseMissing('recipe_images', ['path'=>'test1', 'recipe_id'=>$recipe->id]);
    }

    /**
     * @test
     */
    public function test_recipe_image_belongs_to_recipe(){
        $recipe = Recipe::factory()->create(['title' => 'test']);
        $recipe_image = RecipeImage::factory()->create(['path' => 'test']);
        $this->assertNotNull($recipe_image->recipe);
        $this->assertInstanceOf(Recipe::class, $recipe_image->recipe);
        $this->assertEquals($recipe_image->recipe->id, $recipe->id);
    }
}
