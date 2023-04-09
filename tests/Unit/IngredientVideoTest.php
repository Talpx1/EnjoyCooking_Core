<?php

namespace Tests\Unit;

use App\Enums\ModerationStatuses;
use App\Models\Ingredient;
use App\Models\IngredientVideo;
use App\Models\ModerationStatus;
use App\Models\User;
use Database\Seeders\ModerationStatusSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IngredientVideoTest extends TestCase
{

    use RefreshDatabase;
    protected $seed = true;
    protected $seeder = ModerationStatusSeeder::class;

    /**
     * @test
     */
    public function test_path_is_required(){
        $this->expectException(QueryException::class);
        IngredientVideo::factory()->create(['path'=>null]);
    }

    /**
     * @test
     */
    public function test_path_must_be_unique(){
        IngredientVideo::factory()->create(['path'=>'test']);
        $this->expectException(QueryException::class);
        IngredientVideo::factory()->create(['path'=>'test']);
    }

    /**
     * @test
     */
    public function test_description_is_nullable(){
        IngredientVideo::factory()->create(['path' => 'test', 'description'=>null]);
        $this->assertDatabaseHas('ingredient_videos', ['path'=>'test', 'description'=>null]);
    }

    /**
     * @test
     */
    public function test_ingredient_id_is_required(){
        $this->expectException(QueryException::class);
        IngredientVideo::factory()->create(['path' => 'test', 'ingredient_id'=>null]);
    }

    /**
     * @test
     */
    public function test_ingredient_id_must_exists_in_ingredients_table(){
        $ingredient = Ingredient::factory()->create();
        IngredientVideo::factory()->create(['path' => 'test', 'ingredient_id' => $ingredient->id]);
        $this->assertDatabaseHas('ingredient_videos', ['path'=>'test', 'ingredient_id'=>$ingredient->id]);

        $this->expectException(QueryException::class);
        IngredientVideo::factory()->create(['path' => 'test 2', 'ingredient_id' => 111]);
    }

    /**
     * @test
     */
    public function test_ingredient_video_gets_deleted_if_parent_ingredient_gets_deleted(){
        $ingredient = Ingredient::factory()->create(['name'=>'test']);
        $video = IngredientVideo::factory()->create(['path' => 'test1', 'ingredient_id' => $ingredient->id]);

        $this->assertDatabaseHas('ingredients', ['name'=>'test']);
        $this->assertDatabaseHas('ingredient_videos', ['path'=>'test1', 'ingredient_id'=>$ingredient->id]);
        $this->assertEquals($ingredient->id, $video->ingredient_id);

        $ingredient->delete();

        $this->assertModelMissing($ingredient);
        $this->assertModelMissing($video);

        $this->assertDatabaseMissing('ingredients', ['name'=>'test']);
        $this->assertDatabaseMissing('ingredient_videos', ['path'=>'test1', 'ingredient_id'=>$ingredient->id]);
    }

    /**
     * @test
     */
    public function test_ingredient_video_belongs_to_ingredient(){
        $ingredient = Ingredient::factory()->create(['name' => 'test']);
        $ingredient_video = IngredientVideo::factory()->create(['path' => 'test', 'ingredient_id' => $ingredient->id]);
        $this->assertNotNull($ingredient_video->ingredient);
        $this->assertInstanceOf(Ingredient::class, $ingredient_video->ingredient);
        $this->assertEquals($ingredient_video->ingredient->id, $ingredient->id);
    }

    /**
     * @test
     */
    public function test_user_id_is_nullable(){
        IngredientVideo::factory()->create(['path' => 'test', 'user_id'=>null]);
        $this->assertDatabaseHas('ingredient_videos', ['path'=>'test', 'user_id'=>null]);
    }

    /**
     * @test
     */
    public function test_user_id_must_exists_in_users_table(){
        $user = Ingredient::factory()->create();
        IngredientVideo::factory()->create(['path' => 'test', 'user_id' => $user->id]);
        $this->assertDatabaseHas('ingredient_videos', ['path'=>'test', 'user_id'=>$user->id]);

        $this->expectException(QueryException::class);
        IngredientVideo::factory()->create(['path' => 'test 2', 'user_id' => 111]);
    }

    /**
     * @test
     */
    public function test_ingredient_video_belongs_to_user(){
        $user = User::factory()->create(['first_name' => 'test']);
        $ingredient_video = IngredientVideo::factory()->create(['path' => 'test', 'user_id' => $user->id]);
        $this->assertNotNull($ingredient_video->user);
        $this->assertInstanceOf(User::class, $ingredient_video->user);
        $this->assertEquals($ingredient_video->user->id, $user->id);
    }

    /**
     * @test
     */
    public function test_user_id_gets_set_as_null_if_user_gets_deleted(){
        $user = User::factory()->create(['first_name' => 'user_test']);
        $ingredient_video = IngredientVideo::factory()->create(['path' => 'ingredient_video_test', 'user_id' => $user->id]);

        $this->assertDatabaseHas('users', ['first_name'=>'user_test']);
        $this->assertDatabaseHas('ingredient_videos', ['path'=>'ingredient_video_test', 'user_id'=>$user->id]);
        $this->assertEquals($user->id, $ingredient_video->user_id);

        $user->delete();

        $this->assertModelMissing($user);
        $this->assertModelExists($ingredient_video);

        $this->assertDatabaseMissing('users', ['first_name'=>'user_test']);
        $this->assertDatabaseMissing('ingredient_videos', ['path'=>'ingredient_video_test', 'user_id'=>$user->id]);

        $this->assertNull($ingredient_video->fresh()->user_id);
        $this->assertDatabaseHas('ingredient_videos', ['path'=>'ingredient_video_test', 'user_id'=>null]);
    }

    /**
     * @test
     */
    public function test_moderation_status_id_is_required(){
        $this->expectException(QueryException::class);
        IngredientVideo::factory()->create(['moderation_status_id'=>null]);
    }

    /**
     * @test
     */
    public function test_moderation_status_id_must_exists_in_moderation_statuses_table(){
        $moderation_status = ModerationStatus::factory()->create();
        $ingredient = Ingredient::factory()->create();
        IngredientVideo::factory()->create(['ingredient_id' => $ingredient->id, 'moderation_status_id' => $moderation_status->id]);
        $this->assertDatabaseHas(IngredientVideo::class, ['ingredient_id' => $ingredient->id, 'moderation_status_id'=>$moderation_status->id]);

        $this->expectException(QueryException::class);
        IngredientVideo::factory()->create(['ingredient_id' => $ingredient->id, 'moderation_status_id' => 111]);
    }

    /**
     * @test
     */
    public function test_moderation_status_elimination_gets_restricted_if_recipes_depends_on_it(){
        $moderation_status = ModerationStatus::factory()->create(['name'=>'test']);
        $ingredient = Ingredient::factory()->create();
        $ingredient_video = IngredientVideo::factory()->create(['ingredient_id' => $ingredient->id, 'moderation_status_id' => $moderation_status->id]);

        $this->assertDatabaseHas('moderation_statuses', ['name'=>'test']);
        $this->assertDatabaseHas(IngredientVideo::class, ['ingredient_id' => $ingredient->id, 'moderation_status_id'=>$moderation_status->id]);

        $this->expectException(QueryException::class);
        $moderation_status->delete();

        $this->assertModelExists($moderation_status);
        $this->assertModelExists($ingredient_video);

        $this->assertDatabaseHas('moderation_statuses', ['name'=>'test']);
        $this->assertDatabaseHas(IngredientVideo::class, ['ingredient_id' => $ingredient->id, 'moderation_status_id'=>$moderation_status->id]);

        $ingredient_video->delete();
        $moderation_status->delete();

        $this->assertDatabaseMissing('moderation_statuses', ['name'=>'test']);
        $this->assertDatabaseMissing('ingredient_videos', ['ingredient_id' => $ingredient->id, 'moderation_status_id'=>$moderation_status->id]);

        $this->assertModelMissing($ingredient_video);
        $this->assertModelMissing($moderation_status);
    }

    /**
     * @test
     */
    public function test_ingredient_video_belongs_to_moderation_status(){
        $moderation_status = ModerationStatus::factory()->create();
        $ingredient_video = IngredientVideo::factory()->create(['moderation_status_id' => $moderation_status->id]);
        $this->assertNotNull($ingredient_video->moderationStatus);
        $this->assertInstanceOf(ModerationStatus::class, $ingredient_video->moderationStatus);
        $this->assertEquals($ingredient_video->moderationStatus->id, $moderation_status->id);
    }

    /**
     * @test
     */
    public function test_moderation_status_id_defaults_to_pending_moderation_status_id(){
        $ingredient = Ingredient::factory()->create();
        $ingredient_video = IngredientVideo::factory()->create(['ingredient_id' => $ingredient->id]);
        $this->assertDatabaseHas(IngredientVideo::class, ['id'=>$ingredient_video->id, 'ingredient_id'=>$ingredient->id, 'moderation_status_id'=>ModerationStatuses::PENDING_MODERATION->value]);
    }
}
