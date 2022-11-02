<?php

namespace Tests\Unit;

use App\Models\RecipeImage;
use App\Models\RecipeStep;
use App\Models\RecipeVideo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use App\Models\Recipe;
use App\Models\DifficultyLevel;
use App\Models\Course;
use App\Models\User;
use App\Models\Category;
use App\Models\ModerationStatus;
use App\Models\VisibilityStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class RecipeTest extends TestCase
{

    use RefreshDatabase, WithFaker;

    /**
     * @test
     */
    public function test_title_is_required(){
        $this->expectException(QueryException::class);
        Recipe::factory()->create(['title'=>null]);
    }

    /**
     * @test
     */
    public function test_slug_is_generated_from_title(){
        $recipe = Recipe::factory()->create(['title'=>'test 123']);
        $this->assertModelExists($recipe);
        $this->assertNotNull($recipe->slug);
        $this->assertEquals('test-123', $recipe->slug);
        $this->assertDatabaseHas('recipes', ['slug'=>'test-123', 'title'=>'test 123']);
    }

    /**
     * @test
     */
    public function test_slug_must_be_unique(){
        Recipe::factory()->create(['slug'=>'test']);
        $this->expectException(QueryException::class);
        Recipe::factory()->create(['slug'=>'test']);
    }

    /**
     * @test
     */
    public function test_unique_slug_is_generated(){
        Recipe::factory()->create(['title'=>'test 123']);
        $this->assertDatabaseHas('recipes', ['slug'=>'test-123', 'title'=>'test 123']);
        Recipe::factory()->create(['title'=>'test 123 ']);
        $this->assertDatabaseHas('recipes', ['slug'=>'test-123-2', 'title'=>'test 123 ']);
    }

    /**
     * @test
     */
    public function test_featured_image_path_is_nullable(){
        Recipe::factory()->create(['title' => 'test 123', 'featured_image_path'=>null]);
        $this->assertDatabaseHas('recipes', ['title'=>'test 123', 'featured_image_path'=>null]);
    }

    /**
     * @test
     */
    public function test_featured_image_path_must_be_unique(){
        Recipe::factory()->create(['featured_image_path'=>'test']);
        $this->expectException(QueryException::class);
        Recipe::factory()->create(['featured_image_path'=>'test']);
    }

    /**
     * @test
     */
    public function test_featured_image_thumbnail_path_is_nullable(){
        Recipe::factory()->create(['title' => 'test 123', 'featured_image_thumbnail_path'=>null]);
        $this->assertDatabaseHas('recipes', ['title'=>'test 123', 'featured_image_thumbnail_path'=>null]);
    }

    /**
     * @test
     */
    public function test_featured_image_thumbnail_path_must_be_unique(){
        Recipe::factory()->create(['featured_image_thumbnail_path'=>'test']);
        $this->expectException(QueryException::class);
        Recipe::factory()->create(['featured_image_thumbnail_path'=>'test']);
    }

    /**
     * @test
     */
    public function test_baking_minutes_is_nullable(){
        Recipe::factory()->create(['title' => 'test 123', 'baking_minutes'=>null]);
        $this->assertDatabaseHas('recipes', ['title'=>'test 123', 'baking_minutes'=>null]);
    }

    /**
     * @test
     */
    public function test_preparation_minutes_is_required(){
        $this->expectException(QueryException::class);
        Recipe::factory()->create(['preparation_minutes'=>null]);
    }

    /**
     * @test
     */
    public function test_description_is_nullable(){
        Recipe::factory()->create(['title' => 'test 123', 'description'=>null]);
        $this->assertDatabaseHas('recipes', ['title'=>'test 123', 'description'=>null]);
    }

    /**
     * @test
     */
    public function test_share_count_is_required(){
        $this->expectException(QueryException::class);
        Recipe::factory()->create(['share_count'=>null]);
    }

    /**
     * @test
     */
    public function test_share_count_default_is_0(){
        Recipe::factory()->newModel(array_merge(
            ['title' => 'test 123'],
            Arr::except(Recipe::factory()->definition(), ['share_count', 'title'])
        ))->save();
        $this->assertDatabaseHas('recipes', ['title'=>'test 123', 'share_count'=>0]);
    }

    /**
     * @test
     */
    public function test_difficulty_level_id_is_required(){
        $this->expectException(QueryException::class);
        Recipe::factory()->create(['difficulty_level_id'=>null]);
    }

    /**
     * @test
     */
    public function test_difficulty_level_id_must_exists_in_difficulty_levels_table(){
        $difficulty_level = DifficultyLevel::factory()->create();
        Recipe::factory()->create(['title' => 'test', 'difficulty_level_id' => $difficulty_level->id]);
        $this->assertDatabaseHas('recipes', ['title'=>'test', 'difficulty_level_id'=>$difficulty_level->id]);

        $this->expectException(QueryException::class);
        Recipe::factory()->create(['title' => 'test 2', 'difficulty_level_id' => 111]);
    }

    /**
     * @test
     */
    public function test_difficulty_level_elimination_gets_restricted_if_recipes_depends_on_it(){
        $difficulty_level = DifficultyLevel::factory()->create(['name'=>'test']);
        $recipe = Recipe::factory()->create(['title' => 'test', 'difficulty_level_id' => $difficulty_level->id]);

        $this->assertDatabaseHas('difficulty_levels', ['name'=>'test']);
        $this->assertDatabaseHas('recipes', ['title'=>'test', 'difficulty_level_id'=>$difficulty_level->id]);

        $this->expectException(QueryException::class);
        $difficulty_level->delete();

        $this->assertModelExists($difficulty_level);
        $this->assertModelExists($recipe);

        $this->assertDatabaseHas('difficulty_levels', ['name'=>'test']);
        $this->assertDatabaseHas('recipes', ['title'=>'test', 'difficulty_level_id'=>$difficulty_level->id]);

        $recipe->delete();
        $difficulty_level->delete();

        $this->assertDatabaseMissing('difficulty_levels', ['name'=>'test']);
        $this->assertDatabaseMissing('recipes', ['title'=>'test', 'difficulty_level_id'=>$difficulty_level->id]);

        $this->assertModelMissing($recipe);
        $this->assertModelMissing($difficulty_level);
    }

    /**
     * @test
     */
    public function test_parent_recipe_id_is_nullable(){
        Recipe::factory()->create(['title' => 'test 123', 'parent_recipe_id'=>null]);
        $this->assertDatabaseHas('recipes', ['title'=>'test 123', 'parent_recipe_id'=>null]);
    }

    /**
     * @test
     */
    public function test_parent_recipe_id_must_exists_in_recipes_table(){
        $parent_recipe = Recipe::factory()->create();
        Recipe::factory()->create(['title' => 'test', 'parent_recipe_id' => $parent_recipe->id]);
        $this->assertDatabaseHas('recipes', ['title'=>'test', 'parent_recipe_id'=>$parent_recipe->id]);

        $this->expectException(QueryException::class);
        Recipe::factory()->create(['title' => 'test 2', 'parent_recipe_id' => 111]);
    }

    /**
     * @test
     */
    public function test_childs_parent_recipe_id_gets_set_as_null_if_parent_recipe_gets_deleted(){
        $parent_recipe = Recipe::factory()->create(['title'=>'parent']);
        $child_recipe = Recipe::factory()->create(['title' => 'child', 'parent_recipe_id' => $parent_recipe->id]);

        $this->assertDatabaseHas('recipes', ['title'=>'parent']);
        $this->assertDatabaseHas('recipes', ['title'=>'child', 'parent_recipe_id'=>$parent_recipe->id]);
        $this->assertEquals($parent_recipe->id, $child_recipe->parent_recipe_id);

        $parent_recipe->delete();

        $this->assertModelMissing($parent_recipe);
        $this->assertModelExists($child_recipe);

        $this->assertDatabaseMissing('recipes', ['title'=>'parent']);
        $this->assertDatabaseMissing('recipes', ['title'=>'child', 'parent_recipe_id'=>$parent_recipe->id]);

        $this->assertNull($child_recipe->fresh()->parent_recipe_id);
        $this->assertDatabaseHas('recipes', ['title'=>'child', 'parent_recipe_id'=>null]);


    }

    /**
     * @test
     */
    public function test_course_id_is_required(){
        $this->expectException(QueryException::class);
        Recipe::factory()->create(['course_id'=>null]);
    }

    /**
     * @test
     */
    public function test_course_id_must_exists_in_courses_table(){
        $course = Course::factory()->create();
        Recipe::factory()->create(['title' => 'test', 'course_id' => $course->id]);
        $this->assertDatabaseHas('recipes', ['title'=>'test', 'course_id'=>$course->id]);

        $this->expectException(QueryException::class);
        Recipe::factory()->create(['title' => 'test 2', 'course_id' => 111]);
    }

    /**
     * @test
     */
    public function test_course_elimination_gets_restricted_if_recipes_depends_on_it(){
        $course = Course::factory()->create(['name'=>'test']);
        $recipe = Recipe::factory()->create(['title' => 'test', 'course_id' => $course->id]);

        $this->assertDatabaseHas('courses', ['name'=>'test']);
        $this->assertDatabaseHas('recipes', ['title'=>'test', 'course_id'=>$course->id]);

        $this->expectException(QueryException::class);
        $course->delete();

        $this->assertModelExists($course);
        $this->assertModelExists($recipe);

        $this->assertDatabaseHas('courses', ['name'=>'test']);
        $this->assertDatabaseHas('recipes', ['title'=>'test', 'course_id'=>$course->id]);

        $recipe->delete();
        $course->delete();

        $this->assertDatabaseMissing('courses', ['name'=>'test']);
        $this->assertDatabaseMissing('recipes', ['title'=>'test', 'course_id'=>$course->id]);

        $this->assertModelMissing($recipe);
        $this->assertModelMissing($course);
    }

    /**
     * @test
     */
    public function test_user_id_is_required(){
        $this->expectException(QueryException::class);
        Recipe::factory()->create(['user_id'=>null]);
    }

    /**
     * @test
     */
    public function test_user_id_must_exists_in_users_table(){
        $user = Course::factory()->create();
        Recipe::factory()->create(['title' => 'test', 'user_id' => $user->id]);
        $this->assertDatabaseHas('recipes', ['title'=>'test', 'user_id'=>$user->id]);

        $this->expectException(QueryException::class);
        Recipe::factory()->create(['title' => 'test 2', 'user_id' => 111]);
    }

    /**
     * @test
     */
    public function test_recipes_get_deleted_if_related_user_gets_deleted(){
        $user = User::factory()->create(['username' => 'user_test']);
        $other_user = User::factory()->create(['username' => 'other_user_test']);
        $recipe = Recipe::factory()->create(['title' => 'user recipe', 'user_id' => $user->id]);
        $recipe2 = Recipe::factory()->create(['title' => 'user recipe 2', 'user_id' => $user->id]);
        $other_recipe = Recipe::factory()->create(['title' => 'other user recipe', 'user_id' => $other_user->id]);

        $this->assertDatabaseHas('users', ['username'=>'user_test']);
        $this->assertDatabaseHas('users', ['username'=>'other_user_test']);
        $this->assertDatabaseHas('recipes', ['title' => 'user recipe', 'user_id' => $user->id]);
        $this->assertDatabaseHas('recipes', ['title' => 'user recipe 2', 'user_id' => $user->id]);
        $this->assertDatabaseHas('recipes', ['title' => 'other user recipe', 'user_id' => $other_user->id]);

        $user->delete();

        $this->assertDatabaseMissing('users', ['username'=>'user_test']);
        $this->assertDatabaseMissing('recipes', ['title' => 'user recipe', 'user_id' => $user->id]);
        $this->assertDatabaseMissing('recipes', ['title' => 'user recipe 2', 'user_id' => $user->id]);

        $this->assertModelMissing($user);
        $this->assertModelMissing($recipe);
        $this->assertModelMissing($recipe2);

        $this->assertDatabaseHas('users', ['username'=>'other_user_test']);
        $this->assertDatabaseHas('recipes', ['title' => 'other user recipe', 'user_id' => $other_user->id]);

        $this->assertModelExists($other_recipe);
    }

    /**
     * @test
     */
    public function test_category_id_is_required(){
        $this->expectException(QueryException::class);
        Recipe::factory()->create(['category_id'=>null]);
    }

    /**
     * @test
     */
    public function test_category_id_must_exists_in_categories_table(){
        $category = Category::factory()->create();
        Recipe::factory()->create(['title' => 'test', 'category_id' => $category->id]);
        $this->assertDatabaseHas('recipes', ['title'=>'test', 'category_id'=>$category->id]);

        $this->expectException(QueryException::class);
        Recipe::factory()->create(['title' => 'test 2', 'category_id' => 111]);
    }

    /**
     * @test
     */
    public function test_category_elimination_gets_restricted_if_recipes_depends_on_it(){
        $category = Category::factory()->create(['name'=>'test']);
        $recipe = Recipe::factory()->create(['title' => 'test', 'category_id' => $category->id]);

        $this->assertDatabaseHas('categories', ['name'=>'test']);
        $this->assertDatabaseHas('recipes', ['title'=>'test', 'category_id'=>$category->id]);

        $this->expectException(QueryException::class);
        $category->delete();

        $this->assertModelExists($category);
        $this->assertModelExists($recipe);

        $this->assertDatabaseHas('categories', ['name'=>'test']);
        $this->assertDatabaseHas('recipes', ['title'=>'test', 'category_id'=>$category->id]);

        $recipe->delete();
        $category->delete();

        $this->assertDatabaseMissing('categories', ['name'=>'test']);
        $this->assertDatabaseMissing('recipes', ['title'=>'test', 'category_id'=>$category->id]);

        $this->assertModelMissing($recipe);
        $this->assertModelMissing($category);
    }

    /**
     * @test
     */
    public function test_moderation_status_id_is_required(){
        $this->expectException(QueryException::class);
        Recipe::factory()->create(['moderation_status_id'=>null]);
    }

    /**
     * @test
     */
    public function test_moderation_status_id_must_exists_in_categories_table(){
        $moderation_status = ModerationStatus::factory()->create();
        Recipe::factory()->create(['title' => 'test', 'moderation_status_id' => $moderation_status->id]);
        $this->assertDatabaseHas('recipes', ['title'=>'test', 'moderation_status_id'=>$moderation_status->id]);

        $this->expectException(QueryException::class);
        Recipe::factory()->create(['title' => 'test 2', 'moderation_status_id' => 111]);
    }

    /**
     * @test
     */
    public function test_moderation_status_elimination_gets_restricted_if_recipes_depends_on_it(){
        $moderation_status = ModerationStatus::factory()->create(['name'=>'test']);
        $recipe = Recipe::factory()->create(['title' => 'test', 'moderation_status_id' => $moderation_status->id]);

        $this->assertDatabaseHas('moderation_statuses', ['name'=>'test']);
        $this->assertDatabaseHas('recipes', ['title'=>'test', 'moderation_status_id'=>$moderation_status->id]);

        $this->expectException(QueryException::class);
        $moderation_status->delete();

        $this->assertModelExists($moderation_status);
        $this->assertModelExists($recipe);

        $this->assertDatabaseHas('moderation_statuses', ['name'=>'test']);
        $this->assertDatabaseHas('recipes', ['title'=>'test', 'moderation_status_id'=>$moderation_status->id]);

        $recipe->delete();
        $moderation_status->delete();

        $this->assertDatabaseMissing('moderation_statuses', ['name'=>'test']);
        $this->assertDatabaseMissing('recipes', ['title'=>'test', 'moderation_status_id'=>$moderation_status->id]);

        $this->assertModelMissing($recipe);
        $this->assertModelMissing($moderation_status);
    }

    /**
     * @test
     */
    public function test_visibility_status_id_is_required(){
        $this->expectException(QueryException::class);
        Recipe::factory()->create(['visibility_status_id'=>null]);
    }

    /**
     * @test
     */
    public function test_visibility_status_id_must_exists_in_categories_table(){
        $visibility_status = VisibilityStatus::factory()->create();
        Recipe::factory()->create(['title' => 'test', 'visibility_status_id' => $visibility_status->id]);
        $this->assertDatabaseHas('recipes', ['title'=>'test', 'visibility_status_id'=>$visibility_status->id]);

        $this->expectException(QueryException::class);
        Recipe::factory()->create(['title' => 'test 2', 'visibility_status_id' => 111]);
    }

    /**
     * @test
     */
    public function test_visibility_status_elimination_gets_restricted_if_recipes_depends_on_it(){
        $visibility_status = VisibilityStatus::factory()->create(['name'=>'test']);
        $recipe = Recipe::factory()->create(['title' => 'test', 'visibility_status_id' => $visibility_status->id]);

        $this->assertDatabaseHas('visibility_statuses', ['name'=>'test']);
        $this->assertDatabaseHas('recipes', ['title'=>'test', 'visibility_status_id'=>$visibility_status->id]);

        $this->expectException(QueryException::class);
        $visibility_status->delete();

        $this->assertModelExists($visibility_status);
        $this->assertModelExists($recipe);

        $this->assertDatabaseHas('visibility_statuses', ['name'=>'test']);
        $this->assertDatabaseHas('recipes', ['title'=>'test', 'visibility_status_id'=>$visibility_status->id]);

        $recipe->delete();
        $visibility_status->delete();

        $this->assertDatabaseMissing('visibility_statuses', ['name'=>'test']);
        $this->assertDatabaseMissing('recipes', ['title'=>'test', 'visibility_status_id'=>$visibility_status->id]);

        $this->assertModelMissing($recipe);
        $this->assertModelMissing($visibility_status);
    }

    /**
     * @test
     */
    public function test_recipe_has_many_recipe_videos(){
        $recipe = Recipe::factory()->create(['title' => 'test']);
        $recipe_videos = RecipeVideo::factory(2)->create(['recipe_id' => $recipe->id]);
        $other_recipe_videos = RecipeVideo::factory(4)->create(['recipe_id'=>Recipe::factory()->create()->id]);

        $this->assertNotNull($recipe->videos);

        $this->assertInstanceOf(Collection::class, $recipe->videos);
        $recipe->videos->each(fn($video) => $this->assertInstanceOf(RecipeVideo::class, $video));

        $this->assertCount(2, $recipe->videos);

        $recipe->videos->each(fn($video) => $this->assertTrue($recipe_videos->contains($video)));
        $recipe->videos->each(fn($video) => $this->assertFalse($other_recipe_videos->contains($video)));
    }

    /**
     * @test
     */
    public function test_recipe_belongs_to_difficulty_level(){
        $difficulty_level = DifficultyLevel::factory()->create();
        $recipe = Recipe::factory()->create(['difficulty_level_id' => $difficulty_level->id]);
        $this->assertNotNull($recipe->difficultyLevel);
        $this->assertInstanceOf(DifficultyLevel::class, $recipe->difficultyLevel);
        $this->assertEquals($recipe->difficultyLevel->id, $difficulty_level->id);
    }

    /**
     * @test
     */
    public function test_child_recipe_belongs_to_parent_recipe(){
        $parent = Recipe::factory()->create();
        $child = Recipe::factory()->create(['parent_recipe_id' => $parent->id]);
        $this->assertNotNull($child->parentRecipe);
        $this->assertInstanceOf(Recipe::class, $child->parentRecipe);
        $this->assertEquals($child->parentRecipe->id, $parent->id);
    }

    /**
     * @test
     */
    public function test_recipe_has_many_child_recipes(){
        $recipe = Recipe::factory()->create();
        $other_recipe = Recipe::factory()->create();
        $recipe_children = Recipe::factory(2)->create(['parent_recipe_id' => $recipe->id]);
        $other_recipe_children = Recipe::factory(4)->create(['parent_recipe_id'=>$other_recipe->id]);

        $this->assertNotNull($recipe->childRecipes);
        $this->assertNotNull($other_recipe->childRecipes);

        $this->assertInstanceOf(Collection::class, $recipe->childRecipes);
        $this->assertInstanceOf(Collection::class, $other_recipe->childRecipes);

        $recipe->childRecipes->each(fn($recipe) => $this->assertInstanceOf(Recipe::class, $recipe));
        $other_recipe->childRecipes->each(fn($recipe) => $this->assertInstanceOf(Recipe::class, $recipe));

        $this->assertCount(2, $recipe->childRecipes);
        $this->assertCount(4, $other_recipe->childRecipes);

        $recipe->childRecipes->each(fn($recipe) => $this->assertTrue($recipe_children->contains($recipe)));
        $recipe->childRecipes->each(fn($recipe) => $this->assertFalse($other_recipe_children->contains($recipe)));

        $other_recipe->childRecipes->each(fn($recipe) => $this->assertFalse($recipe_children->contains($recipe)));
        $other_recipe->childRecipes->each(fn($recipe) => $this->assertTrue($other_recipe_children->contains($recipe)));
    }

    /**
     * @test
     */
    public function test_recipe_belongs_to_course(){
        $course = Course::factory()->create();
        $recipe = Recipe::factory()->create(['course_id' => $course->id]);
        $this->assertNotNull($recipe->course);
        $this->assertInstanceOf(Course::class, $recipe->course);
        $this->assertEquals($recipe->course->id, $course->id);
    }

    /**
     * @test
     */
    public function test_recipe_belongs_to_user(){
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $this->assertNotNull($recipe->user);
        $this->assertInstanceOf(User::class, $recipe->user);
        $this->assertEquals($recipe->user->id, $user->id);
    }

    /**
     * @test
     */
    public function test_has_children_attribute(){
        $recipe = Recipe::factory()->create();
        $other_recipe = Recipe::factory()->create();

        $recipe_children = Recipe::factory(5)->create(['parent_recipe_id' => $recipe->id]);

        $this->assertNotEmpty($recipe->childRecipes);
        $this->assertEmpty($other_recipe->childRecipes);

        $this->assertCount(5, $recipe->childRecipes);

        $this->assertTrue($recipe->hasChildren);
        $this->assertFalse($other_recipe->hasChildren);
    }

    /**
     * @test
     */
    public function test_is_children_attribute(){
        $recipe = Recipe::factory()->create();
        $other_recipe = Recipe::factory()->create();

        $recipe_children = Recipe::factory(5)->create(['parent_recipe_id' => $recipe->id]);

        $recipe_children->each(fn($child_recipe) => $this->assertTrue($child_recipe->isChild));
        $this->assertFalse($other_recipe->isChild);
        $this->assertFalse($recipe->isChild);
    }

    /**
     * @test
     */
    public function test_recipe_belongs_to_category(){
        $category = Category::factory()->create();
        $recipe = Recipe::factory()->create(['category_id' => $category->id]);
        $this->assertNotNull($recipe->category);
        $this->assertInstanceOf(Category::class, $recipe->category);
        $this->assertEquals($recipe->category->id, $category->id);
    }

    /**
     * @test
     */
    public function test_recipe_belongs_to_moderation_status(){
        $moderation_status = ModerationStatus::factory()->create();
        $recipe = Recipe::factory()->create(['moderation_status_id' => $moderation_status->id]);
        $this->assertNotNull($recipe->moderationStatus);
        $this->assertInstanceOf(ModerationStatus::class, $recipe->moderationStatus);
        $this->assertEquals($recipe->moderationStatus->id, $moderation_status->id);
    }

    /**
     * @test
     */
    public function test_recipe_belongs_to_visibility_status(){
        $visibility_status = VisibilityStatus::factory()->create();
        $recipe = Recipe::factory()->create(['visibility_status_id' => $visibility_status->id]);
        $this->assertNotNull($recipe->visibilityStatus);
        $this->assertInstanceOf(VisibilityStatus::class, $recipe->visibilityStatus);
        $this->assertEquals($recipe->visibilityStatus->id, $visibility_status->id);
    }

    /**
     * @test
     */
    public function test_recipe_has_many_recipe_images(){
        $recipe = Recipe::factory()->create(['title' => 'test']);
        $recipe_images = RecipeImage::factory(2)->create(['recipe_id' => $recipe->id]);
        $other_recipe_images = RecipeImage::factory(4)->create(['recipe_id'=>Recipe::factory()->create()->id]);

        $this->assertNotNull($recipe->images);

        $this->assertInstanceOf(Collection::class, $recipe->images);
        $recipe->images->each(fn($image) => $this->assertInstanceOf(RecipeImage::class, $image));

        $this->assertCount(2, $recipe->images);

        $recipe->images->each(fn($image) => $this->assertTrue($recipe_images->contains($image)));
        $recipe->images->each(fn($image) => $this->assertFalse($other_recipe_images->contains($image)));
    }

    /**
     * @test
     */
    public function test_recipe_has_many_recipe_steps(){
        $recipe = Recipe::factory()->create(['title' => 'test']);
        $recipe_steps = RecipeStep::factory(2)->create(['recipe_id' => $recipe->id]);
        $other_recipe_steps = RecipeStep::factory(4)->create(['recipe_id'=>Recipe::factory()->create()->id]);

        $this->assertNotNull($recipe->steps);

        $this->assertInstanceOf(Collection::class, $recipe->steps);
        $recipe->steps->each(fn($step) => $this->assertInstanceOf(RecipeStep::class, $step));

        $this->assertCount(2, $recipe->steps);

        $recipe->steps->each(fn($step) => $this->assertTrue($recipe_steps->contains($step)));
        $recipe->steps->each(fn($step) => $this->assertFalse($other_recipe_steps->contains($step)));
    }
}