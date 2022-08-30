<?php

namespace Tests\Unit;

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
}
