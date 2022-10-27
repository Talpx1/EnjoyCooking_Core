<?php

namespace Tests\Unit;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;
use App\Models\DifficultyLevel;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DifficultyLevelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function test_name_is_required(){
        $this->expectException(QueryException::class);
        DifficultyLevel::factory()->create(['name'=>null]);
    }

    /**
     * @test
     */
    public function test_slug_is_generated_from_name(){
        $category = DifficultyLevel::factory()->create(['name'=>'test 123']);
        $this->assertModelExists($category);
        $this->assertNotNull($category->slug);
        $this->assertEquals('test-123', $category->slug);
        $this->assertDatabaseHas('difficulty_levels', ['slug'=>'test-123', 'name'=>'test 123']);
    }

    /**
     * @test
     */
    public function test_name_must_be_unique(){
        DifficultyLevel::factory()->create(['name'=>'test']);
        $this->expectException(QueryException::class);
        DifficultyLevel::factory()->create(['name'=>'test']);
    }

    /**
     * @test
     */
    public function test_slug_must_be_unique(){
        DifficultyLevel::factory()->create(['slug'=>'test']);
        $this->expectException(QueryException::class);
        DifficultyLevel::factory()->create(['slug'=>'test']);
    }

    /**
     * @test
     */
    public function test_unique_slug_is_generated(){
        DifficultyLevel::factory()->create(['name'=>'test 123']);
        $this->assertDatabaseHas('difficulty_levels', ['slug'=>'test-123', 'name'=>'test 123']);
        DifficultyLevel::factory()->create(['name'=>'test 123 ']);
        $this->assertDatabaseHas('difficulty_levels', ['slug'=>'test-123-2', 'name'=>'test 123 ']);
    }

    /**
     * @test
     */
    public function test_difficulty_level_has_many_recipes(){
        $difficulty_level = DifficultyLevel::factory()->create();
        $other_difficulty_level = DifficultyLevel::factory()->create();
        $recipes = Recipe::factory(2)->create(['difficulty_level_id' => $difficulty_level->id]);
        $other_recipes = Recipe::factory(4)->create(['difficulty_level_id' => $other_difficulty_level->id]);

        $this->assertNotNull($difficulty_level->recipes);
        $this->assertNotNull($other_difficulty_level->recipes);

        $this->assertInstanceOf(Collection::class, $difficulty_level->recipes);
        $this->assertInstanceOf(Collection::class, $other_difficulty_level->recipes);

        $difficulty_level->recipes->each(fn($recipe) => $this->assertInstanceOf(Recipe::class, $recipe));
        $other_difficulty_level->recipes->each(fn($recipe) => $this->assertInstanceOf(Recipe::class, $recipe));

        $this->assertCount(2, $difficulty_level->recipes);
        $this->assertCount(4, $other_difficulty_level->recipes);

        $difficulty_level->recipes->each(fn($recipe) => $this->assertTrue($recipes->contains($recipe)));
        $difficulty_level->recipes->each(fn($recipe) => $this->assertFalse($other_recipes->contains($recipe)));

        $other_difficulty_level->recipes->each(fn($recipe) => $this->assertTrue($other_recipes->contains($recipe)));
        $other_difficulty_level->recipes->each(fn($recipe) => $this->assertFalse($recipes->contains($recipe)));
    }
}
