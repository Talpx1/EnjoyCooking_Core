<?php

namespace Tests\Unit;

use App\Models\Recipe;
use App\Models\RecipeVideo;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeVideoTest extends TestCase {

    use RefreshDatabase;

    /**
     * @test
     */
    public function test_path_is_required(){
        $this->expectException(QueryException::class);
        RecipeVideo::factory()->create(['path'=>null]);
    }

    /**
     * @test
     */
    public function test_path_must_be_unique(){
        RecipeVideo::factory()->create(['path'=>'test']);
        $this->expectException(QueryException::class);
        RecipeVideo::factory()->create(['path'=>'test']);
    }

    /**
     * @test
     */
    public function test_description_is_nullable(){
        RecipeVideo::factory()->create(['path' => 'test', 'description'=>null]);
        $this->assertDatabaseHas('recipe_videos', ['path'=>'test', 'description'=>null]);
    }

    /**
     * @test
     */
    public function test_recipe_id_is_required(){
        $this->expectException(QueryException::class);
        RecipeVideo::factory()->create(['path' => 'test', 'recipe_id'=>null]);
    }

    /**
     * @test
     */
    public function test_recipe_id_must_exists_in_recipes_table(){
        $recipe = Recipe::factory()->create();
        RecipeVideo::factory()->create(['path' => 'test', 'recipe_id' => $recipe->id]);
        $this->assertDatabaseHas('recipe_videos', ['path'=>'test', 'recipe_id'=>$recipe->id]);

        $this->expectException(QueryException::class);
        RecipeVideo::factory()->create(['path' => 'test 2', 'recipe_id' => 111]);
    }

    /**
     * @test
     */
    public function test_recipe_video_gets_deleted_if_parent_recipe_gets_deleted(){
        $recipe = Recipe::factory()->create(['title'=>'test']);
        $video = RecipeVideo::factory()->create(['path' => 'test1', 'recipe_id' => $recipe->id]);

        $this->assertDatabaseHas('recipes', ['title'=>'test']);
        $this->assertDatabaseHas('recipe_videos', ['path'=>'test1', 'recipe_id'=>$recipe->id]);
        $this->assertEquals($recipe->id, $video->recipe_id);

        $recipe->delete();

        $this->assertModelMissing($recipe);
        $this->assertModelMissing($video);

        $this->assertDatabaseMissing('recipes', ['title'=>'test']);
        $this->assertDatabaseMissing('recipe_videos', ['path'=>'test1', 'recipe_id'=>$recipe->id]);
    }

    /**
     * @test
     */
    public function test_recipe_video_belongs_to_recipe(){
        $recipe = Recipe::factory()->create(['title' => 'test']);
        $recipe_video = RecipeVideo::factory()->create(['path' => 'test']);
        $this->assertNotNull($recipe_video->recipe);
        $this->assertInstanceOf(Recipe::class, $recipe_video->recipe);
        $this->assertEquals($recipe_video->recipe->id, $recipe->id);
    }
}
