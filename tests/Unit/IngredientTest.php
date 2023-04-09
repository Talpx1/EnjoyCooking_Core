<?php

namespace Tests\Unit;

use App\Enums\ModerationStatuses;
use App\Models\Ingredient;
use App\Models\IngredientImage;
use App\Models\IngredientRecipe;
use App\Models\IngredientVideo;
use App\Models\ModerationStatus;
use App\Models\Recipe;
use App\Models\Tag;
use App\Models\Taggable;
use App\Models\User;
use Database\Seeders\ModerationStatusSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IngredientTest extends TestCase{
    use RefreshDatabase;
    protected $seed = true;
    protected $seeder = ModerationStatusSeeder::class;

    /**
     * @test
     */
    public function test_name_is_required(){
        $this->expectException(QueryException::class);
        Ingredient::factory()->create(['name'=>null]);
    }

    /**
     * @test
     */
    public function test_name_must_be_unique(){
        Ingredient::factory()->create(['name'=>'test']);
        $this->expectException(QueryException::class);
        Ingredient::factory()->create(['name'=>'test']);
    }

    /**
     * @test
     */
    public function test_slug_is_generated_from_name(){
        $ingredient = Ingredient::factory()->create(['name'=>'test 123']);
        $this->assertModelExists($ingredient);
        $this->assertNotNull($ingredient->slug);
        $this->assertEquals('test-123', $ingredient->slug);
        $this->assertDatabaseHas('ingredients', ['slug'=>'test-123', 'name'=>'test 123']);
    }

    /**
     * @test
     */
    public function test_slug_must_be_unique(){
        Ingredient::factory()->create(['slug'=>'test']);
        $this->expectException(QueryException::class);
        Ingredient::factory()->create(['slug'=>'test']);
    }

    /**
     * @test
     */
    public function test_unique_slug_is_generated(){
        Ingredient::factory()->create(['name'=>'test 123']);
        $this->assertDatabaseHas('ingredients', ['slug'=>'test-123', 'name'=>'test 123']);
        Ingredient::factory()->create(['name'=>'test 123 ']);
        $this->assertDatabaseHas('ingredients', ['slug'=>'test-123-2', 'name'=>'test 123 ']);
    }

    /**
     * @test
     */
    public function test_featured_image_path_is_nullable(){
        Ingredient::factory()->create(['name' => 'test 123', 'featured_image_path'=>null]);
        $this->assertDatabaseHas('ingredients', ['name'=>'test 123', 'featured_image_path'=>null]);
    }

    /**
     * @test
     */
    public function test_featured_image_path_must_be_unique(){
        Ingredient::factory()->create(['featured_image_path'=>'test']);
        $this->expectException(QueryException::class);
        Ingredient::factory()->create(['featured_image_path'=>'test']);
    }

    /**
     * @test
     */
    public function test_featured_image_thumbnail_path_is_nullable(){
        Ingredient::factory()->create(['name' => 'test 123', 'featured_image_thumbnail_path'=>null]);
        $this->assertDatabaseHas('ingredients', ['name'=>'test 123', 'featured_image_thumbnail_path'=>null]);
    }

    /**
     * @test
     */
    public function test_featured_image_thumbnail_path_must_be_unique(){
        Ingredient::factory()->create(['featured_image_thumbnail_path'=>'test']);
        $this->expectException(QueryException::class);
        Ingredient::factory()->create(['featured_image_thumbnail_path'=>'test']);
    }

    /**
     * @test
     */
    public function test_description_is_required(){
        $this->expectException(QueryException::class);
        Ingredient::factory()->create(['description'=>null]);
    }

    /**
     * @test
     */
    public function test_user_id_is_nullable(){
        Ingredient::factory()->create(['name' => 'test 123', 'user_id'=>null]);
        $this->assertDatabaseHas('ingredients', ['name'=>'test 123', 'user_id'=>null]);
    }

    /**
     * @test
     */
    public function test_user_id_must_exists_in_users_table(){
        $user = User::factory()->create();
        Ingredient::factory()->create(['name' => 'test', 'user_id' => $user->id]);
        $this->assertDatabaseHas('ingredients', ['name'=>'test', 'user_id'=>$user->id]);

        $this->expectException(QueryException::class);
        Ingredient::factory()->create(['name' => 'test 2', 'user_id' => 111]);
    }

    /**
     * @test
     */
    public function test_user_id_gets_set_as_null_if_user_gets_deleted(){
        $user = User::factory()->create(['first_name' => 'user_test']);
        $ingredient = Ingredient::factory()->create(['name' => 'ingredient_test', 'user_id' => $user->id]);

        $this->assertDatabaseHas('users', ['first_name'=>'user_test']);
        $this->assertDatabaseHas('ingredients', ['name'=>'ingredient_test', 'user_id'=>$user->id]);
        $this->assertEquals($user->id, $ingredient->user_id);

        $user->delete();

        $this->assertModelMissing($user);
        $this->assertModelExists($ingredient);

        $this->assertDatabaseMissing('users', ['first_name'=>'user_test']);
        $this->assertDatabaseMissing('ingredients', ['name'=>'ingredient_test', 'user_id'=>$user->id]);

        $this->assertNull($ingredient->fresh()->user_id);
        $this->assertDatabaseHas('ingredients', ['name'=>'ingredient_test', 'user_id'=>null]);
    }

    /**
     * @test
     */
    public function test_ingredient_belongs_to_user(){
        $user = User::factory()->create();
        $ingredient = Ingredient::factory()->create(['user_id' => $user->id]);
        $this->assertNotNull($ingredient->user);
        $this->assertInstanceOf(User::class, $ingredient->user);
        $this->assertEquals($ingredient->user->id, $user->id);
    }

    /**
     * @test
     */
    public function test_ingredient_has_many_ingredient_images(){
        $ingredient = Ingredient::factory()->create(['name' => 'test']);
        $ingredient_images = IngredientImage::factory(2)->create(['ingredient_id' => $ingredient->id]);
        $other_ingredient_images = IngredientImage::factory(4)->create(['ingredient_id'=>Ingredient::factory()->create()->id]);

        $this->assertNotNull($ingredient->images);

        $this->assertInstanceOf(Collection::class, $ingredient->images);
        $ingredient->images->each(fn($image) => $this->assertInstanceOf(IngredientImage::class, $image));

        $this->assertCount(2, $ingredient->images);

        $ingredient->images->each(fn($image) => $this->assertTrue($ingredient_images->contains($image)));
        $ingredient->images->each(fn($image) => $this->assertFalse($other_ingredient_images->contains($image)));
    }

    /**
     * @test
     */
    public function test_ingredient_has_many_ingredient_videos(){
        $ingredient = Ingredient::factory()->create(['name' => 'test']);
        $ingredient_videos = IngredientVideo::factory(2)->create(['ingredient_id' => $ingredient->id]);
        $other_ingredient_videos = IngredientVideo::factory(4)->create(['ingredient_id'=>Ingredient::factory()->create()->id]);

        $this->assertNotNull($ingredient->videos);

        $this->assertInstanceOf(Collection::class, $ingredient->videos);
        $ingredient->videos->each(fn($video) => $this->assertInstanceOf(IngredientVideo::class, $video));

        $this->assertCount(2, $ingredient->videos);

        $ingredient->videos->each(fn($video) => $this->assertTrue($ingredient_videos->contains($video)));
        $ingredient->videos->each(fn($video) => $this->assertFalse($other_ingredient_videos->contains($video)));
    }

    /**
     * @test
     */
    public function test_ingredient_belongs_to_many_recipes(){
        $ingredient = Ingredient::factory()->create();
        $recipes = Recipe::factory(3)->create()->each(fn($recipe)=>IngredientRecipe::factory()->create(['ingredient_id'=>$ingredient->id,'recipe_id'=>$recipe->id]));
        $other_recipes = Recipe::factory(5)->create()->each(fn($recipe)=>IngredientRecipe::factory()->create(['ingredient_id'=>Ingredient::factory()->create()->id,'recipe_id'=>$recipe->id]));

        $recipes->each(fn($recipe) => $this->assertDatabaseHas('ingredient_recipe', ['ingredient_id'=>$ingredient->id,'recipe_id'=>$recipe->id]));
        $other_recipes->each(fn($recipe) => $this->assertDatabaseHas('ingredient_recipe', ['ingredient_id'=>$ingredient->id]));
        $other_recipes->each(fn($recipe) => $this->assertDatabaseMissing('ingredient_recipe', ['ingredient_id'=>$ingredient->id, 'recipe_id'=>$recipe->id]));

        $this->assertNotNull($ingredient->recipes);
        $this->assertInstanceOf(Collection::class, $ingredient->recipes);
        $this->assertCount(3, $ingredient->recipes);
        $ingredient->recipes->each(fn($recipe)=>$this->assertInstanceOf(Recipe::class, $recipe));

        $ingredient->recipes->each(fn($recipe)=>$this->assertTrue($recipes->contains($recipe)));
        $ingredient->recipes->each(fn($recipe)=>$this->assertFalse($other_recipes->contains($recipe)));
    }

    /**
     * @test
     */
    public function test_when_ingredient_gets_deleted_its_related_records_in_taggables_table_get_deleted(){
        $ingredient = Ingredient::factory()->create();
        $tags = Tag::factory(3)->create()->each(function($tag) use ($ingredient){
            Taggable::factory()->create(['tag_id'=>$tag->id, 'taggable_id'=>$ingredient->id, 'taggable_type'=>$ingredient::class]);
            $this->assertDatabaseHas('taggables', ['tag_id'=>$tag->id, 'taggable_id'=>$ingredient->id, 'taggable_type'=>$ingredient::class]);
        });

        $ingredient->delete();
        $this->assertModelMissing($ingredient);
        $this->assertDatabaseMissing('ingredients', ['title'=>$ingredient->id]);

        $tags->each(function($tag) use ($ingredient){
            $this->assertDatabaseMissing('taggables', ['tag_id'=>$tag->id, 'taggable_id'=>$ingredient->id, 'taggable_type'=>$ingredient::class]);
        });
    }

    /**
     * @test
     */
    public function test_moderation_status_id_is_required(){
        $this->expectException(QueryException::class);
        Ingredient::factory()->create(['moderation_status_id'=>null]);
    }

    /**
     * @test
     */
    public function test_moderation_status_id_must_exists_in_moderation_statuses_table(){
        $moderation_status = ModerationStatus::factory()->create();
        Ingredient::factory()->create(['name' => 'test', 'moderation_status_id' => $moderation_status->id]);
        $this->assertDatabaseHas(Ingredient::class, ['name'=>'test', 'moderation_status_id'=>$moderation_status->id]);

        $this->expectException(QueryException::class);
        Ingredient::factory()->create(['name' => 'test 2', 'moderation_status_id' => 111]);
    }

    /**
     * @test
     */
    public function test_moderation_status_elimination_gets_restricted_if_ingredients_depends_on_it(){
        $moderation_status = ModerationStatus::factory()->create(['name'=>'test']);
        $ingredient = Ingredient::factory()->create(['name' => 'test', 'moderation_status_id' => $moderation_status->id]);

        $this->assertDatabaseHas('moderation_statuses', ['name'=>'test']);
        $this->assertDatabaseHas(Ingredient::class, ['name'=>'test', 'moderation_status_id'=>$moderation_status->id]);

        $this->expectException(QueryException::class);
        $moderation_status->delete();

        $this->assertModelExists($moderation_status);
        $this->assertModelExists($ingredient);

        $this->assertDatabaseHas('moderation_statuses', ['name'=>'test']);
        $this->assertDatabaseHas(Ingredient::class, ['name'=>'test', 'moderation_status_id'=>$moderation_status->id]);

        $ingredient->delete();
        $moderation_status->delete();

        $this->assertDatabaseMissing('moderation_statuses', ['name'=>'test']);
        $this->assertDatabaseMissing('ingredients', ['name'=>'test', 'moderation_status_id'=>$moderation_status->id]);

        $this->assertModelMissing($ingredient);
        $this->assertModelMissing($moderation_status);
    }

    /**
     * @test
     */
    public function test_ingredient_belongs_to_moderation_status(){
        $moderation_status = ModerationStatus::factory()->create();
        $ingredient = Ingredient::factory()->create(['moderation_status_id' => $moderation_status->id]);
        $this->assertNotNull($ingredient->moderationStatus);
        $this->assertInstanceOf(ModerationStatus::class, $ingredient->moderationStatus);
        $this->assertEquals($ingredient->moderationStatus->id, $moderation_status->id);
    }

    /**
     * @test
     */
    public function test_moderation_status_id_defaults_to_pending_moderation_status_id(){
        $ingredient = Ingredient::factory()->create();
        $this->assertDatabaseHas(Ingredient::class, ['id'=>$ingredient->id, 'name'=>$ingredient->name, 'moderation_status_id'=>ModerationStatuses::PENDING_MODERATION->value]);
    }
}
