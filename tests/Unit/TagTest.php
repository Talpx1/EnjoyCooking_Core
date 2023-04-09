<?php

namespace Tests\Unit;

use App\Enums\ModerationStatuses;
use App\Models\Follow;
use App\Models\Ingredient;
use App\Models\ModerationStatus;
use App\Models\Recipe;
use App\Models\Snack;
use App\Models\Tag;
use App\Models\Taggable;
use App\Models\User;
use Database\Seeders\ModerationStatusSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase{

    use RefreshDatabase;
    protected $seed = true;
    protected $seeder = ModerationStatusSeeder::class;

    /**
     * @test
     */
    public function test_name_is_required(){
        $this->expectException(QueryException::class);
        Tag::factory()->create(['name'=>null]);
    }

    /**
     * @test
     */
    public function test_name_must_be_unique(){
        Tag::factory()->create(['name'=>'test']);
        $this->expectException(QueryException::class);
        Tag::factory()->create(['name'=>'test']);
    }

    /**
     * @test
     */
    public function test_slug_is_generated_from_name(){
        $tag = Tag::factory()->create(['name'=>'test 123']);
        $this->assertModelExists($tag);
        $this->assertNotNull($tag->slug);
        $this->assertEquals('test-123', $tag->slug);
        $this->assertDatabaseHas('tags', ['slug'=>'test-123', 'name'=>'test 123']);
    }

    /**
     * @test
     */
    public function test_slug_must_be_unique(){
        Tag::factory()->create(['slug'=>'test']);
        $this->expectException(QueryException::class);
        Tag::factory()->create(['slug'=>'test']);
    }

    /**
     * @test
     */
    public function test_unique_slug_is_generated(){
        Tag::factory()->create(['name'=>'test 123']);
        $this->assertDatabaseHas('tags', ['slug'=>'test-123', 'name'=>'test 123']);
        Tag::factory()->create(['name'=>'test 123 ']);
        $this->assertDatabaseHas('tags', ['slug'=>'test-123-2', 'name'=>'test 123 ']);
    }

    /**
     * @test
     */
    public function test_description_is_nullable(){
        Tag::factory()->create(['name' => 'test', 'description'=>null]);
        $this->assertDatabaseHas('tags', ['name'=>'test', 'description'=>null]);
    }

    /**
     * @test
     */
    public function test_tag_is_morphed_by_many_ingredients(){
        $tag = Tag::factory()->create(['name' => 'test']);
        $ingredients = Ingredient::factory(3)->create()->each(fn($ingredient) => Taggable::factory()->create(['tag_id'=>$tag->id, 'taggable_id' => $ingredient->id, 'taggable_type' => $ingredient::class]));
        $other_ingredients = Ingredient::factory(5)->create();

        $this->assertNotNull($tag->ingredients);
        $this->assertCount(3, $tag->ingredients);
        $this->assertInstanceOf(Collection::class, $tag->ingredients);

        $tag->ingredients->each(function($ingredient) use ($ingredients, $other_ingredients){
            $this->assertTrue($ingredients->contains($ingredient));
            $this->assertFalse($other_ingredients->contains($ingredient));
        });
    }

    /**
     * @test
     */
    public function test_tag_is_morphed_by_many_recipes(){
        $tag = Tag::factory()->create(['name' => 'test']);
        $recipes = Recipe::factory(3)->create()->each(fn($recipe) => Taggable::factory()->create(['tag_id'=>$tag->id, 'taggable_id' => $recipe->id, 'taggable_type' => $recipe::class]));
        $other_recipes = Recipe::factory(5)->create();

        $this->assertNotNull($tag->recipes);
        $this->assertCount(3, $tag->recipes);
        $this->assertInstanceOf(Collection::class, $tag->recipes);

        $tag->recipes->each(function($recipe) use ($recipes, $other_recipes){
            $this->assertTrue($recipes->contains($recipe));
            $this->assertFalse($other_recipes->contains($recipe));
        });
    }

    /**
     * @test
     */
    public function test_tag_is_morphed_by_many_snacks(){
        $tag = Tag::factory()->create(['name' => 'test']);
        $snacks = Snack::factory(3)->create()->each(fn($snack) => Taggable::factory()->create(['tag_id'=>$tag->id, 'taggable_id' => $snack->id, 'taggable_type' => $snack::class]));
        $other_snacks = Snack::factory(5)->create();

        $this->assertNotNull($tag->snacks);
        $this->assertCount(3, $tag->snacks);
        $this->assertInstanceOf(Collection::class, $tag->snacks);

        $tag->snacks->each(function($snack) use ($snacks, $other_snacks){
            $this->assertTrue($snacks->contains($snack));
            $this->assertFalse($other_snacks->contains($snack));
        });
    }

    /**
     * @test
     */
    public function test_tag_morphs_many_follows(){
        $tag = Tag::factory()->create(['name' => 'test']);
        $tag_followers = collect([
            Follow::factory()->create(['followable_id' => $tag->id, 'followable_type' => $tag::class, 'user_id' => User::factory()->create()->id]),
            Follow::factory()->create(['followable_id' => $tag->id, 'followable_type' => $tag::class, 'user_id' => User::factory()->create()->id]),
        ]);
        $other_tag_followers = collect([
            Follow::factory()->create(['followable_id'=>Tag::factory()->create()->id, 'followable_type' => $tag::class, 'user_id' => User::factory()->create()->id]),
            Follow::factory()->create(['followable_id'=>Tag::factory()->create()->id, 'followable_type' => $tag::class, 'user_id' => User::factory()->create()->id]),
            Follow::factory()->create(['followable_id'=>Tag::factory()->create()->id, 'followable_type' => $tag::class, 'user_id' => User::factory()->create()->id]),
            Follow::factory()->create(['followable_id'=>Tag::factory()->create()->id, 'followable_type' => $tag::class, 'user_id' => User::factory()->create()->id]),
        ]);

        $this->assertNotNull($tag->followers);

        $this->assertInstanceOf(Collection::class, $tag->followers);
        $tag->followers->each(fn($follow) => $this->assertInstanceOf(Follow::class, $follow));

        $this->assertCount(2, $tag->followers);

        $tag_followers->each(fn($follow) => $this->assertTrue($tag->followers->contains($follow)));
        $other_tag_followers->each(fn($follow) => $this->assertFalse($tag->followers->contains($follow)));
    }

    /**
     * @test
     */
    public function test_when_tag_gets_deleted_its_related_records_in_follows_table_get_deleted(){
        $tag = Tag::factory()->create();
        $follows = collect([
            Follow::factory()->create(['followable_id'=>$tag->id, 'followable_type'=>$tag::class, 'user_id' => User::factory()->create()->id]),
            Follow::factory()->create(['followable_id'=>$tag->id, 'followable_type'=>$tag::class, 'user_id' => User::factory()->create()->id]),
            Follow::factory()->create(['followable_id'=>$tag->id, 'followable_type'=>$tag::class, 'user_id' => User::factory()->create()->id]),
        ]);

        $tag->delete();
        $this->assertModelMissing($tag);
        $this->assertDatabaseMissing('tags', ['title'=>$tag->id]);
        $this->assertDatabaseMissing('follows', ['followable_id'=>$tag->id, 'followable_type'=>$tag::class]);

        $follows->each(fn($follow) => $this->assertModelMissing($follow));
    }

    /**
     * @test
     */
    public function test_moderation_status_id_is_required(){
        $this->expectException(QueryException::class);
        Tag::factory()->create(['moderation_status_id'=>null]);
    }

    /**
     * @test
     */
    public function test_moderation_status_id_must_exists_in_moderation_statuses_table(){
        $moderation_status = ModerationStatus::factory()->create();
        Tag::factory()->create(['name' => 'test', 'moderation_status_id' => $moderation_status->id]);
        $this->assertDatabaseHas(Tag::class, ['name'=>'test', 'moderation_status_id'=>$moderation_status->id]);

        $this->expectException(QueryException::class);
        Tag::factory()->create(['name' => 'test 2', 'moderation_status_id' => 111]);
    }

    /**
     * @test
     */
    public function test_moderation_status_elimination_gets_restricted_if_recipes_depends_on_it(){
        $moderation_status = ModerationStatus::factory()->create(['name'=>'test']);
        $tag = Tag::factory()->create(['name' => 'test', 'moderation_status_id' => $moderation_status->id]);

        $this->assertDatabaseHas('moderation_statuses', ['name'=>'test']);
        $this->assertDatabaseHas(Tag::class, ['name'=>'test', 'moderation_status_id'=>$moderation_status->id]);

        $this->expectException(QueryException::class);
        $moderation_status->delete();

        $this->assertModelExists($moderation_status);
        $this->assertModelExists($tag);

        $this->assertDatabaseHas('moderation_statuses', ['name'=>'test']);
        $this->assertDatabaseHas(Tag::class, ['name'=>'test', 'moderation_status_id'=>$moderation_status->id]);

        $tag->delete();
        $moderation_status->delete();

        $this->assertDatabaseMissing('moderation_statuses', ['name'=>'test']);
        $this->assertDatabaseMissing('tags', ['name'=>'test', 'moderation_status_id'=>$moderation_status->id]);

        $this->assertModelMissing($tag);
        $this->assertModelMissing($moderation_status);
    }

    /**
     * @test
     */
    public function test_tag_belongs_to_moderation_status(){
        $moderation_status = ModerationStatus::factory()->create();
        $tag = Tag::factory()->create(['moderation_status_id' => $moderation_status->id]);
        $this->assertNotNull($tag->moderationStatus);
        $this->assertInstanceOf(ModerationStatus::class, $tag->moderationStatus);
        $this->assertEquals($tag->moderationStatus->id, $moderation_status->id);
    }

    /**
     * @test
     */
    public function test_moderation_status_id_defaults_to_pending_moderation_status_id(){
        $tag = Tag::factory()->create();
        $this->assertDatabaseHas(Tag::class, ['id'=>$tag->id, 'name'=>$tag->name, 'moderation_status_id'=>ModerationStatuses::PENDING_MODERATION->value]);
    }

}
