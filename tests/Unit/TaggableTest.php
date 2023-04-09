<?php

namespace Tests\Unit;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\Tag;
use App\Models\Taggable;
use Database\Seeders\ModerationStatusSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaggableTest extends TestCase{
    use RefreshDatabase;
    protected $seed = true;
    protected $seeder = ModerationStatusSeeder::class;

    /**
     * @test
     */
    public function test_taggable_id_is_required(){
        $this->expectException(QueryException::class);
        Taggable::factory()->create(['taggable_id'=>null]);
    }

    public function test_taggable_type_is_required(){
        $this->expectException(QueryException::class);
        Taggable::factory()->create(['taggable_type'=>null]);
    }

    /**
     * @test
     */
    public function test_tag_id_must_exists_in_tags_table(){
        $tag = Tag::factory()->create();
        Taggable::factory()->create(['tag_id' => $tag->id]);
        $this->assertDatabaseHas('taggables', ['tag_id'=>$tag->id]);

        $this->expectException(QueryException::class);
        Taggable::factory()->create(['tag_id' => 111]);
        $this->assertDatabaseMissing('taggables', ['tag_id'=>111]);
    }

    /**
     * @test
     */
    public function test_taggable_gets_deleted_if_tag_gets_deleted(){
        $tag = Tag::factory()->create();
        $taggable = Taggable::factory()->create(['tag_id' => $tag->id]);
        $this->assertDatabaseHas('taggables', ['tag_id'=>$tag->id]);

        $tag->delete();
        $this->assertModelMissing($tag);

        $this->assertDatabaseMissing('taggables', ['tag_id'=>$tag->id]);

        $this->assertModelMissing($taggable);
    }

    /**
     * @test
     */
    public function test_combination_of_tag_id_taggable_id_and_taggable_type_must_be_unique(){
        $tag = Tag::factory()->create();
        $tag2=Tag::factory()->create();
        $recipe = Recipe::factory()->create();
        $ingredient = Ingredient::factory()->create();

        Taggable::factory()->create(['tag_id' => $tag->id, 'taggable_id' => $recipe->id, 'taggable_type' => $recipe::class]);
        $this->assertDatabaseHas('taggables', ['tag_id' => $tag->id, 'taggable_id' => $recipe->id, 'taggable_type' => $recipe::class]);

        Taggable::factory()->create(['tag_id' => $tag2->id, 'taggable_id' => $recipe->id, 'taggable_type' => $recipe::class]);
        $this->assertDatabaseHas('taggables', ['tag_id' => $tag2->id, 'taggable_id' => $recipe->id, 'taggable_type' => $recipe::class]);

        Taggable::factory()->create(['tag_id' => $tag->id, 'taggable_id' => $ingredient->id, 'taggable_type' => $ingredient::class]);
        $this->assertDatabaseHas('taggables', ['tag_id' => $tag->id, 'taggable_id' => $ingredient->id, 'taggable_type' => $ingredient::class]);

        Taggable::factory()->create(['tag_id' => $tag2->id, 'taggable_id' => $ingredient->id, 'taggable_type' => $ingredient::class]);
        $this->assertDatabaseHas('taggables', ['tag_id' => $tag2->id, 'taggable_id' => $ingredient->id, 'taggable_type' => $ingredient::class]);

        try{
            Taggable::factory()->create(['tag_id' => $tag->id, 'taggable_id' => $recipe->id, 'taggable_type' => $recipe::class]);
        }catch(QueryException $e){ $this->assertUniqueConstraintFails($e); }

        try{
            Taggable::factory()->create(['tag_id' => $tag2->id, 'taggable_id' => $recipe->id, 'taggable_type' => $recipe::class]);
        }catch(QueryException $e){ $this->assertUniqueConstraintFails($e); }

        try{
            Taggable::factory()->create(['tag_id' => $tag->id, 'taggable_id' => $ingredient->id, 'taggable_type' => $ingredient::class]);
        }catch(QueryException $e){ $this->assertUniqueConstraintFails($e); }

        try{
            Taggable::factory()->create(['tag_id' => $tag2->id, 'taggable_id' => $ingredient->id, 'taggable_type' => $ingredient::class]);
        }catch(QueryException $e){ $this->assertUniqueConstraintFails($e); }
    }
}
