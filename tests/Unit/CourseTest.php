<?php

namespace Tests\Unit;

use App\Models\Recipe;
use Database\Seeders\ModerationStatusSeeder;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Course;
use Illuminate\Database\QueryException;

class CourseTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;
    protected $seeder = ModerationStatusSeeder::class;

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

    /**
     * @test
     */
    public function test_course_has_many_recipes(){
        $course = Course::factory()->create();
        $other_course = Course::factory()->create();
        $recipes = Recipe::factory(2)->create(['course_id' => $course->id]);
        $other_recipes = Recipe::factory(4)->create(['course_id' => $other_course->id]);

        $this->assertNotNull($course->recipes);
        $this->assertNotNull($other_course->recipes);

        $this->assertInstanceOf(Collection::class, $course->recipes);
        $this->assertInstanceOf(Collection::class, $other_course->recipes);

        $course->recipes->each(fn($recipe) => $this->assertInstanceOf(Recipe::class, $recipe));
        $other_course->recipes->each(fn($recipe) => $this->assertInstanceOf(Recipe::class, $recipe));

        $this->assertCount(2, $course->recipes);
        $this->assertCount(4, $other_course->recipes);

        $course->recipes->each(fn($recipe) => $this->assertTrue($recipes->contains($recipe)));
        $course->recipes->each(fn($recipe) => $this->assertFalse($other_recipes->contains($recipe)));

        $other_course->recipes->each(fn($recipe) => $this->assertTrue($other_recipes->contains($recipe)));
        $other_course->recipes->each(fn($recipe) => $this->assertFalse($recipes->contains($recipe)));
    }
}
