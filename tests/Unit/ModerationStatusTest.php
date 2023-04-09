<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\Execution;
use App\Models\Ingredient;
use App\Models\IngredientImage;
use App\Models\IngredientVideo;
use App\Models\Recipe;
use App\Models\Snack;
use App\Models\Tag;
use Database\Seeders\ModerationStatusSeeder;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\ModerationStatus;
use Illuminate\Database\QueryException;

class ModerationStatusTest extends TestCase
{

    use RefreshDatabase;
    protected $seed = true;
    protected $seeder = ModerationStatusSeeder::class;

    /**
     * @test
     */
    public function test_name_is_required(){
        $this->expectException(QueryException::class);
        ModerationStatus::factory()->create(['name'=>null]);
    }

    /**
     * @test
     */
    public function test_name_must_be_unique(){
        ModerationStatus::factory()->create(['name'=>'test']);
        $this->expectException(QueryException::class);
        ModerationStatus::factory()->create(['name'=>'test']);
    }

    /**
     * @test
     */
    public function test_moderation_status_has_many_recipes(){
        $moderation_status = ModerationStatus::factory()->create();
        $other_moderation_status = ModerationStatus::factory()->create();
        $recipes = Recipe::factory(2)->create(['moderation_status_id' => $moderation_status->id]);
        $other_recipes = Recipe::factory(4)->create(['moderation_status_id' => $other_moderation_status->id]);

        $this->assertNotNull($moderation_status->recipes);
        $this->assertNotNull($other_moderation_status->recipes);

        $this->assertInstanceOf(Collection::class, $moderation_status->recipes);
        $this->assertInstanceOf(Collection::class, $other_moderation_status->recipes);

        $moderation_status->recipes->each(fn($recipe) => $this->assertInstanceOf(Recipe::class, $recipe));
        $other_moderation_status->recipes->each(fn($recipe) => $this->assertInstanceOf(Recipe::class, $recipe));

        $this->assertCount(2, $moderation_status->recipes);
        $this->assertCount(4, $other_moderation_status->recipes);

        $moderation_status->recipes->each(fn($recipe) => $this->assertTrue($recipes->contains($recipe)));
        $moderation_status->recipes->each(fn($recipe) => $this->assertFalse($other_recipes->contains($recipe)));

        $other_moderation_status->recipes->each(fn($recipe) => $this->assertTrue($other_recipes->contains($recipe)));
        $other_moderation_status->recipes->each(fn($recipe) => $this->assertFalse($recipes->contains($recipe)));
    }

    /**
     * @test
     */
    public function test_moderation_status_has_many_ingredients(){
        $moderation_status = ModerationStatus::factory()->create();
        $other_moderation_status = ModerationStatus::factory()->create();
        $ingredients = Ingredient::factory(2)->create(['moderation_status_id' => $moderation_status->id]);
        $other_ingredients = Ingredient::factory(4)->create(['moderation_status_id' => $other_moderation_status->id]);

        $this->assertNotNull($moderation_status->ingredients);
        $this->assertNotNull($other_moderation_status->ingredients);

        $this->assertInstanceOf(Collection::class, $moderation_status->ingredients);
        $this->assertInstanceOf(Collection::class, $other_moderation_status->ingredients);

        $moderation_status->ingredients->each(fn($ingredient) => $this->assertInstanceOf(Ingredient::class, $ingredient));
        $other_moderation_status->ingredients->each(fn($ingredient) => $this->assertInstanceOf(Ingredient::class, $ingredient));

        $this->assertCount(2, $moderation_status->ingredients);
        $this->assertCount(4, $other_moderation_status->ingredients);

        $moderation_status->ingredients->each(fn($ingredient) => $this->assertTrue($ingredients->contains($ingredient)));
        $moderation_status->ingredients->each(fn($ingredient) => $this->assertFalse($other_ingredients->contains($ingredient)));

        $other_moderation_status->ingredients->each(fn($ingredient) => $this->assertTrue($other_ingredients->contains($ingredient)));
        $other_moderation_status->ingredients->each(fn($ingredient) => $this->assertFalse($ingredients->contains($ingredient)));
    }

    /**
     * @test
     */
    public function test_moderation_status_has_many_ingredient_images(){
        $moderation_status = ModerationStatus::factory()->create();
        $other_moderation_status = ModerationStatus::factory()->create();
        $ingredient_images = IngredientImage::factory(2)->create(['moderation_status_id' => $moderation_status->id]);
        $other_ingredient_images = IngredientImage::factory(4)->create(['moderation_status_id' => $other_moderation_status->id]);

        $this->assertNotNull($moderation_status->ingredientImages);
        $this->assertNotNull($other_moderation_status->ingredientImages);

        $this->assertInstanceOf(Collection::class, $moderation_status->ingredientImages);
        $this->assertInstanceOf(Collection::class, $other_moderation_status->ingredientImages);

        $moderation_status->ingredientImages->each(fn($ingredient_image) => $this->assertInstanceOf(IngredientImage::class, $ingredient_image));
        $other_moderation_status->ingredientImages->each(fn($ingredient_image) => $this->assertInstanceOf(IngredientImage::class, $ingredient_image));

        $this->assertCount(2, $moderation_status->ingredientImages);
        $this->assertCount(4, $other_moderation_status->ingredientImages);

        $moderation_status->ingredientImages->each(fn($ingredient_image) => $this->assertTrue($ingredient_images->contains($ingredient_image)));
        $moderation_status->ingredientImages->each(fn($ingredient_image) => $this->assertFalse($other_ingredient_images->contains($ingredient_image)));

        $other_moderation_status->ingredientImages->each(fn($ingredient_image) => $this->assertTrue($other_ingredient_images->contains($ingredient_image)));
        $other_moderation_status->ingredientImages->each(fn($ingredient_image) => $this->assertFalse($ingredient_images->contains($ingredient_image)));
    }

    /**
     * @test
     */
    public function test_moderation_status_has_many_ingredient_videos(){
        $moderation_status = ModerationStatus::factory()->create();
        $other_moderation_status = ModerationStatus::factory()->create();
        $ingredient_videos = IngredientVideo::factory(2)->create(['moderation_status_id' => $moderation_status->id]);
        $other_ingredient_videos = IngredientVideo::factory(4)->create(['moderation_status_id' => $other_moderation_status->id]);

        $this->assertNotNull($moderation_status->ingredientVideos);
        $this->assertNotNull($other_moderation_status->ingredientVideos);

        $this->assertInstanceOf(Collection::class, $moderation_status->ingredientVideos);
        $this->assertInstanceOf(Collection::class, $other_moderation_status->ingredientVideos);

        $moderation_status->ingredientVideos->each(fn($ingredient_video) => $this->assertInstanceOf(IngredientVideo::class, $ingredient_video));
        $other_moderation_status->ingredientVideos->each(fn($ingredient_video) => $this->assertInstanceOf(IngredientVideo::class, $ingredient_video));

        $this->assertCount(2, $moderation_status->ingredientVideos);
        $this->assertCount(4, $other_moderation_status->ingredientVideos);

        $moderation_status->ingredientVideos->each(fn($ingredient_video) => $this->assertTrue($ingredient_videos->contains($ingredient_video)));
        $moderation_status->ingredientVideos->each(fn($ingredient_video) => $this->assertFalse($other_ingredient_videos->contains($ingredient_video)));

        $other_moderation_status->ingredientVideos->each(fn($ingredient_video) => $this->assertTrue($other_ingredient_videos->contains($ingredient_video)));
        $other_moderation_status->ingredientVideos->each(fn($ingredient_video) => $this->assertFalse($ingredient_videos->contains($ingredient_video)));
    }

    /**
     * @test
     */
    public function test_moderation_status_has_many_tags(){
        $moderation_status = ModerationStatus::factory()->create();
        $other_moderation_status = ModerationStatus::factory()->create();
        $tags = Tag::factory(2)->create(['moderation_status_id' => $moderation_status->id]);
        $other_tags = Tag::factory(4)->create(['moderation_status_id' => $other_moderation_status->id]);

        $this->assertNotNull($moderation_status->tags);
        $this->assertNotNull($other_moderation_status->tags);

        $this->assertInstanceOf(Collection::class, $moderation_status->tags);
        $this->assertInstanceOf(Collection::class, $other_moderation_status->tags);

        $moderation_status->tags->each(fn($tag) => $this->assertInstanceOf(Tag::class, $tag));
        $other_moderation_status->tags->each(fn($tag) => $this->assertInstanceOf(Tag::class, $tag));

        $this->assertCount(2, $moderation_status->tags);
        $this->assertCount(4, $other_moderation_status->tags);

        $moderation_status->tags->each(fn($tag) => $this->assertTrue($tags->contains($tag)));
        $moderation_status->tags->each(fn($tag) => $this->assertFalse($other_tags->contains($tag)));

        $other_moderation_status->tags->each(fn($tag) => $this->assertTrue($other_tags->contains($tag)));
        $other_moderation_status->tags->each(fn($tag) => $this->assertFalse($tags->contains($tag)));
    }

    /**
     * @test
     */
    public function test_moderation_status_has_many_comments(){

        $moderation_status = ModerationStatus::factory()->create();
        $other_moderation_status = ModerationStatus::factory()->create();
        $comments = Comment::factory(2)->create(['moderation_status_id' => $moderation_status->id]);
        $other_comments = Comment::factory(4)->create(['moderation_status_id' => $other_moderation_status->id]);

        $this->assertNotNull($moderation_status->comments);
        $this->assertNotNull($other_moderation_status->comments);

        $this->assertInstanceOf(Collection::class, $moderation_status->comments);
        $this->assertInstanceOf(Collection::class, $other_moderation_status->comments);

        $moderation_status->comments->each(fn($comment) => $this->assertInstanceOf(Comment::class, $comment));
        $other_moderation_status->comments->each(fn($comment) => $this->assertInstanceOf(Comment::class, $comment));

        $this->assertCount(2, $moderation_status->comments);
        $this->assertCount(4, $other_moderation_status->comments);

        $moderation_status->comments->each(fn($comment) => $this->assertTrue($comments->contains($comment)));
        $moderation_status->comments->each(fn($comment) => $this->assertFalse($other_comments->contains($comment)));

        $other_moderation_status->comments->each(fn($comment) => $this->assertTrue($other_comments->contains($comment)));
        $other_moderation_status->comments->each(fn($comment) => $this->assertFalse($comments->contains($comment)));
    }

    /**
     * @test
     */
    public function test_moderation_status_has_many_executions(){
        $moderation_status = ModerationStatus::factory()->create();
        $other_moderation_status = ModerationStatus::factory()->create();
        $executions = Execution::factory(2)->create(['moderation_status_id' => $moderation_status->id]);
        $other_executions = Execution::factory(4)->create(['moderation_status_id' => $other_moderation_status->id]);

        $this->assertNotNull($moderation_status->executions);
        $this->assertNotNull($other_moderation_status->executions);

        $this->assertInstanceOf(Collection::class, $moderation_status->executions);
        $this->assertInstanceOf(Collection::class, $other_moderation_status->executions);

        $moderation_status->executions->each(fn($execution) => $this->assertInstanceOf(Execution::class, $execution));
        $other_moderation_status->executions->each(fn($execution) => $this->assertInstanceOf(Execution::class, $execution));

        $this->assertCount(2, $moderation_status->executions);
        $this->assertCount(4, $other_moderation_status->executions);

        $moderation_status->executions->each(fn($execution) => $this->assertTrue($executions->contains($execution)));
        $moderation_status->executions->each(fn($execution) => $this->assertFalse($other_executions->contains($execution)));

        $other_moderation_status->executions->each(fn($execution) => $this->assertTrue($other_executions->contains($execution)));
        $other_moderation_status->executions->each(fn($execution) => $this->assertFalse($executions->contains($execution)));
    }

    /**
     * @test
     */
    public function test_moderation_status_has_many_snacks(){
        $moderation_status = ModerationStatus::factory()->create();
        $other_moderation_status = ModerationStatus::factory()->create();
        $snacks = Snack::factory(2)->create(['moderation_status_id' => $moderation_status->id]);
        $other_snacks = Snack::factory(4)->create(['moderation_status_id' => $other_moderation_status->id]);

        $this->assertNotNull($moderation_status->snacks);
        $this->assertNotNull($other_moderation_status->snacks);

        $this->assertInstanceOf(Collection::class, $moderation_status->snacks);
        $this->assertInstanceOf(Collection::class, $other_moderation_status->snacks);

        $moderation_status->snacks->each(fn($snack) => $this->assertInstanceOf(Snack::class, $snack));
        $other_moderation_status->snacks->each(fn($snack) => $this->assertInstanceOf(Snack::class, $snack));

        $this->assertCount(2, $moderation_status->snacks);
        $this->assertCount(4, $other_moderation_status->snacks);

        $moderation_status->snacks->each(fn($snack) => $this->assertTrue($snacks->contains($snack)));
        $moderation_status->snacks->each(fn($snack) => $this->assertFalse($other_snacks->contains($snack)));

        $other_moderation_status->snacks->each(fn($snack) => $this->assertTrue($other_snacks->contains($snack)));
        $other_moderation_status->snacks->each(fn($snack) => $this->assertFalse($snacks->contains($snack)));
    }
}
