<?php

namespace Tests\Unit;

use App\Models\Ingredient;
use App\Models\IngredientRecipe;
use App\Models\MeasureUnit;
use App\Models\Recipe;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IngredientRecipeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function test_recipe_id_is_required(){
        $this->expectException(QueryException::class);
        IngredientRecipe::factory()->create(['recipe_id'=>null]);
    }

    /**
     * @test
     */
    public function test_recipe_id_must_exists_in_recipes_table(){
        $recipe = Recipe::factory()->create();
        IngredientRecipe::factory()->create(['recipe_id' => $recipe->id]);
        $this->assertDatabaseHas('ingredient_recipe', ['recipe_id'=>$recipe->id]);

        $this->expectException(QueryException::class);
        IngredientRecipe::factory()->create(['recipe_id' => 111]);
        $this->assertDatabaseMissing('ingredient_recipe', ['recipe_id'=>111]);
    }

    /**
     * @test
     */
    public function test_ingredient_id_must_exists_in_ingredients_table(){
        $ingredient = Ingredient::factory()->create();
        IngredientRecipe::factory()->create(['ingredient_id' => $ingredient->id]);
        $this->assertDatabaseHas('ingredient_recipe', ['ingredient_id'=>$ingredient->id]);

        $this->expectException(QueryException::class);
        IngredientRecipe::factory()->create(['ingredient_id' => 111]);
        $this->assertDatabaseMissing('ingredient_recipe', ['ingredient_id'=>111]);
    }

    /**
     * @test
     */
    public function test_ingredient_elimination_gets_restricted_if_ingredient_recipe_depends_on_it(){
        $ingredient = Ingredient::factory()->create(['name'=>'test']);
        $recipe = Recipe::factory()->create(['title'=>'test']);
        $ingredient_recipe = IngredientRecipe::factory()->create(['recipe_id' => $recipe->id, 'ingredient_id' => $ingredient->id]);

        $this->assertDatabaseHas('ingredients', ['name'=>'test']);
        $this->assertDatabaseHas('recipes', ['title'=>'test']);
        $this->assertDatabaseHas('ingredient_recipe', ['recipe_id' => $recipe->id, 'ingredient_id'=>$ingredient->id]);

        $this->expectException(QueryException::class);
        $ingredient->delete();

        $this->assertModelExists($ingredient);
        $this->assertModelExists($ingredient_recipe);

        $this->assertDatabaseHas('ingredients', ['name'=>'test']);
        $this->assertDatabaseHas('ingredient_recipes', ['recipe_id' => $recipe->id, 'ingredient_id'=>$ingredient->id]);

        $ingredient_recipe->delete();
        $ingredient->delete();

        $this->assertDatabaseMissing('ingredients', ['name'=>'test']);
        $this->assertDatabaseMissing('ingredient_recipes', ['recipe_id' => $recipe->id, 'ingredient_id'=>$ingredient->id]);

        $this->assertModelMissing($ingredient_recipe);
        $this->assertModelMissing($ingredient);
    }

    /**
     * @test
     */
    public function test_ingredient_recipe_gets_deleted_if_recipe_gets_deleted(){
        $recipe = Recipe::factory()->create();
        $ingredient_recipe = IngredientRecipe::factory()->create(['recipe_id' => $recipe->id]);
        $this->assertDatabaseHas('ingredient_recipe', ['recipe_id'=>$recipe->id]);

        $recipe->delete();
        $this->assertModelMissing($recipe);

        $this->assertDatabaseMissing('ingredient_recipe', ['recipe_id'=>$recipe->id]);

        $this->assertModelMissing($ingredient_recipe);
    }

    /**
     * @test
     */
    public function test_combination_of_ingredient_id_and_recipe_id_must_be_unique(){
        $ingredient = Ingredient::factory()->create();
        $recipe = Recipe::factory()->create();
        $recipe2 = Recipe::factory()->create();

        IngredientRecipe::factory()->create(['ingredient_id' => $ingredient->id, 'recipe_id' => $recipe->id]);
        $this->assertDatabaseHas('ingredient_recipe', ['ingredient_id' => $ingredient->id, 'recipe_id' => $recipe->id]);

        IngredientRecipe::factory()->create(['ingredient_id' => $ingredient->id, 'recipe_id' => $recipe2->id]);
        $this->assertDatabaseHas('ingredient_recipe', ['ingredient_id' => $ingredient->id, 'recipe_id' => $recipe2->id]);

        IngredientRecipe::factory()->create(['ingredient_id' => Ingredient::factory()->create()->id, 'recipe_id' => $recipe->id]);

        $this->expectException(QueryException::class);
        IngredientRecipe::factory()->create(['ingredient_id' => $ingredient->id, 'recipe_id' => $recipe->id]);
    }

    /**
     * @test
     */
    public function test_quantity_is_nullable(){
        IngredientRecipe::factory()->create(['quantity'=>null]);
        $this->assertDatabaseCount(IngredientRecipe::class, 1);
        $this->assertDatabaseHas(IngredientRecipe::class, ['quantity'=>null]);
    }

    /**
     * @test
     */
    public function test_measure_unit_id_is_nullable(){
        IngredientRecipe::factory()->create(['measure_unit_id'=>null]);
        $this->assertDatabaseCount(IngredientRecipe::class, 1);
        $this->assertDatabaseHas(IngredientRecipe::class, ['measure_unit_id'=>null]);
    }

    /**
     * @test
     */
    public function test_measure_unit_id_must_exists_in_measure_units_table(){
        $measure_unit = MeasureUnit::factory()->create();
        IngredientRecipe::factory()->create(['measure_unit_id' => $measure_unit->id]);
        $this->assertDatabaseHas('ingredient_recipe', ['measure_unit_id'=>$measure_unit->id]);

        $this->expectException(QueryException::class);
        IngredientRecipe::factory()->create(['measure_unit_id' => 111]);
        $this->assertDatabaseMissing('ingredient_recipe', ['measure_unit_id'=>111]);
    }
}
