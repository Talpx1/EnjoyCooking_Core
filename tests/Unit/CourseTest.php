<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Course;
use Illuminate\Database\QueryException;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function test_name_is_required(){
        $this->expectException(QueryException::class);
        Course::factory()->create(['name'=>null]);
    }

    /**
     * @test
     */
    public function test_slug_is_generated_from_name(){
        $category = Course::factory()->create(['name'=>'test 123']);
        $this->assertModelExists($category);
        $this->assertNotNull($category->slug);
        $this->assertEquals('test-123', $category->slug);
        $this->assertDatabaseHas('courses', ['slug'=>'test-123', 'name'=>'test 123']);
    }

    /**
     * @test
     */
    public function test_name_must_be_unique(){
        Course::factory()->create(['name'=>'test']);
        $this->expectException(QueryException::class);
        Course::factory()->create(['name'=>'test']);
    }

    /**
     * @test
     */
    public function test_slug_must_be_unique(){
        Course::factory()->create(['slug'=>'test']);
        $this->expectException(QueryException::class);
        Course::factory()->create(['slug'=>'test']);
    }

    /**
     * @test
     */
    public function test_unique_slug_is_generated(){
        Course::factory()->create(['name'=>'test 123']);
        $this->assertDatabaseHas('courses', ['slug'=>'test-123', 'name'=>'test 123']);
        Course::factory()->create(['name'=>'test 123 ']);
        $this->assertDatabaseHas('courses', ['slug'=>'test-123-2', 'name'=>'test 123 ']);
    }
}
