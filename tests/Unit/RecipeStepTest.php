<?php

namespace Tests\Unit;

use App\Models\Recipe;
use App\Models\RecipeStep;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeStepTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function test_step_path_is_nullable(){
        RecipeStep::factory()->create(['description' => 'test', 'image_path'=>null]);
        $this->assertDatabaseHas('recipe_steps', ['description'=>'test', 'image_path'=>null]);
    }

    /**
     * @test
     */
    public function test_step_path_must_be_unique(){
        RecipeStep::factory()->create(['image_path'=>'test']);
        $this->expectException(QueryException::class);
        RecipeStep::factory()->create(['image_path'=>'test']);
    }

    /**
     * @test
     */
    public function test_thumbnail_path_is_nullable(){
        RecipeStep::factory()->create(['description' => 'test', 'thumbnail_path'=>null]);
        $this->assertDatabaseHas('recipe_steps', ['description'=>'test', 'thumbnail_path'=>null]);
    }

    /**
     * @test
     */
    public function test_thumbnail_path_must_be_unique(){
        RecipeStep::factory()->create(['thumbnail_path'=>'test']);
        $this->expectException(QueryException::class);
        RecipeStep::factory()->create(['thumbnail_path'=>'test']);
    }

    /**
     * @test
     */
    public function test_description_is_required(){
        $this->expectException(QueryException::class);
        RecipeStep::factory()->create(['step_path' => 'test', 'description'=>null]);
    }

    /**
     * @test
     */
    public function test_recipe_id_is_required(){
        $this->expectException(QueryException::class);
        RecipeStep::factory()->create(['description' => 'test', 'recipe_id'=>null]);
    }

    /**
     * @test
     */
    public function test_recipe_id_must_exists_in_recipes_table(){
        $recipe = Recipe::factory()->create();
        RecipeStep::factory()->create(['description' => 'test', 'recipe_id' => $recipe->id]);
        $this->assertDatabaseHas('recipe_steps', ['description'=>'test', 'recipe_id'=>$recipe->id]);

        $this->expectException(QueryException::class);
        RecipeStep::factory()->create(['description' => 'test 2', 'recipe_id' => 111]);
    }

    /**
     * @test
     */
    public function test_recipe_step_gets_deleted_if_parent_recipe_gets_deleted(){
        $recipe = Recipe::factory()->create(['title'=>'test']);
        $step = RecipeStep::factory()->create(['description' => 'test1', 'recipe_id' => $recipe->id]);

        $this->assertDatabaseHas('recipes', ['title'=>'test']);
        $this->assertDatabaseHas('recipe_steps', ['description'=>'test1', 'recipe_id'=>$recipe->id]);
        $this->assertEquals($recipe->id, $step->recipe_id);

        $recipe->delete();

        $this->assertModelMissing($recipe);
        $this->assertModelMissing($step);

        $this->assertDatabaseMissing('recipes', ['title'=>'test']);
        $this->assertDatabaseMissing('recipe_steps', ['description'=>'test1', 'recipe_id'=>$recipe->id]);
    }

    /**
     * @test
     */
    public function test_recipe_step_belongs_to_recipe(){
        $recipe = Recipe::factory()->create(['title' => 'test']);
        $recipe_step = RecipeStep::factory()->create(['description' => 'test', 'recipe_id' => $recipe->id]);
        $this->assertNotNull($recipe_step->recipe);
        $this->assertInstanceOf(Recipe::class, $recipe_step->recipe);
        $this->assertEquals($recipe_step->recipe->id, $recipe->id);
    }


    /**
     * @test
     */
    public function test_combination_of_recipe_id_description_must_be_unique(){
        $recipe = Recipe::factory()->create();
        $recipe2 = Recipe::factory()->create();

        RecipeStep::factory()->create(['description'=>'test', 'recipe_id'=>$recipe->id]);
        $this->assertDatabaseHas('recipe_steps', ['recipe_id' => $recipe->id,'description' => 'test']);

        RecipeStep::factory()->create(['description'=>'test2', 'recipe_id'=>$recipe->id]);
        $this->assertDatabaseHas('recipe_steps', ['recipe_id' => $recipe->id,'description' => 'test2']);

        RecipeStep::factory()->create(['description'=>'test', 'recipe_id'=>$recipe2->id]);
        $this->assertDatabaseHas('recipe_steps', ['recipe_id' => $recipe2->id,'description' => 'test']);

        try{
            RecipeStep::factory()->create(['description'=>'test', 'recipe_id'=>$recipe->id]);
        }catch(QueryException $e){$this->assertUniqueConstraintFails($e);}

        try{
            RecipeStep::factory()->create(['description'=>'test', 'recipe_id'=>$recipe2->id]);
        }catch(QueryException $e){$this->assertUniqueConstraintFails($e);}
    }
}
