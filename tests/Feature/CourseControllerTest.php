<?php

namespace Tests\Feature;

use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\Seeders\PermissionsAndRolesSeeder;
use Tests\TestCase;

class CourseControllerTest extends TestCase{

    use RefreshDatabase;

    protected $seed = true;
    protected $seeder = PermissionsAndRolesSeeder::class;

    /**
     * @test
     */
    public function test_everyone_can_index_courses(){
        $courses = Course::factory(40)->create();

        $this->simulateAllowedOrigin();
        $this->getJson(route('course.index'))->assertOk()->assertJson(function(AssertableJson $json) use ($courses){
            $json->has('data')->etc();
            $courses->splice(0,15)->each(fn($course) => $this->assertTrue(collect($json->toArray()['data'])->pluck('id')->contains($course->id)));
        });
    }

    /**
     * @test
     */
    public function test_course_index_gets_paginated(){
        $courses = Course::factory(40)->create();

        $this->simulateAllowedOrigin();
        $this->getJson(route('course.index'))->assertOk()->assertJson(function(AssertableJson $json) use ($courses){
            $json->hasAll(['data', 'current_page', "next_page_url", "path","per_page", "prev_page_url", "to", "total", "first_page_url", "from", "last_page", "last_page_url", "links"])->etc();
            $courses->chunk(15)[0]->each(fn($course) => $this->assertTrue(collect($json->toArray()['data'])->pluck('id')->contains($course->id)));
            $courses->chunk(15)[1]->each(fn($course) => $this->assertFalse(collect($json->toArray()['data'])->pluck('id')->contains($course->id)));
        });

        $this->getJson(route('course.index',['page'=>2]))->assertOk()->assertJson(function(AssertableJson $json) use ($courses){
            $json->hasAll(['data', 'current_page', "next_page_url", "path","per_page", "prev_page_url", "to", "total", "first_page_url", "from", "last_page", "last_page_url", "links"])->etc();
            $courses->chunk(15)[0]->each(fn($course) => $this->assertFalse(collect($json->toArray()['data'])->pluck('id')->contains($course->id)));
            $courses->chunk(15)[1]->each(fn($course) => $this->assertTrue(collect($json->toArray()['data'])->pluck('id')->contains($course->id)));
        })->assertJsonFragment(['current_page'=>2]);
    }

    /**
     * @test
     */
    public function test_authorized_user_can_store_course(){
        $this->actingAsAdmin();

        $course = Course::factory()->raw();

        $this->postJson(route('course.store'), $course)->assertCreated()->assertJsonFragment(['name' => $course['name']]);

        $this->actingAsUser();

        $course = Course::factory()->raw();

        $this->postJson(route('course.store'), $course)->assertForbidden();
    }

    /**
     * @test
     */
    public function test_course_name_is_required_on_store(){

        $this->actingAsAdmin();

        $course = Course::factory()->raw();

        unset($course['name']);

        $this->postJson(route('course.store'), $course)->assertJsonValidationErrorFor('name');
        $this->assertDatabaseMissing(Course::class, ['name' => null]);
    }

    /**
     * @test
     */
    public function test_course_name_must_be_string_on_store(){

        $this->actingAsAdmin();

        $course = Course::factory()->raw(['name' => 123]);


        $this->postJson(route('course.store'), $course)->assertJsonValidationErrorFor('name');
        $this->assertDatabaseMissing(Course::class, ['name' => 123]);
    }

    /**
     * @test
     */
    public function test_name_must_be_max_255_chars_on_store(){
        $this->actingAsAdmin();
        $nameErr = Str::random(256);
        $nameOk = Str::random(255);

        $course = Course::factory()->raw(['name' => $nameErr]);

        $this->postJson(route('course.store'), $course)->assertJsonValidationErrorFor('name');
        $this->assertDatabaseMissing(Course::class, ['name' => $nameErr]);

        $course = Course::factory()->raw(['name' => $nameOk]);

        $this->postJson(route('course.store'), $course)->assertCreated();
        $this->assertDatabaseHas(Course::class, ['name' => $nameOk]);
    }

    /**
     * @test
     */
    public function test_course_name_must_be_unique_on_store(){

        $this->actingAsAdmin();

        $course = Course::factory()->raw(['name' => 'test']);


        $this->postJson(route('course.store'), $course)->assertCreated()->assertJsonFragment(['name' => $course['name']]);
        $this->assertDatabaseHas(Course::class, ['name' => $course['name']]);

        $this->postJson(route('course.store'), $course)->assertJsonValidationErrorFor('name');
        $this->assertDatabaseCount(Course::class, 1);
    }

    /**
     * @test
     */
    public function test_slug_is_generated_from_name_on_store(){
        $this->actingAsAdmin();

        $data = Course::factory()->raw(['name'=>'test 123']);

        $this->postJson(route('course.store'), $data)
            ->assertCreated()
            ->assertJsonFragment(['name' => $data['name'], 'slug' => 'test-123'])
            ->assertJson(fn(AssertableJson $json) => $json->hasAll(['slug'])->etc());

        $course = Course::latest()->first();

        $this->assertNotNull($course->slug);
        $this->assertEquals('test-123', $course->slug);
        $this->assertDatabaseHas('courses', ['slug'=>'test-123', 'name'=>$data['name']]);
    }

    /**
     * @test
     */
    public function test_generated_slug_is_unique_on_store(){
        $this->actingAsAdmin();

        $data = Course::factory()->raw(['name'=>'test 123']);

        $this->postJson(route('course.store'), $data)
            ->assertCreated()
            ->assertJsonFragment(['name' => $data['name'], 'slug' => 'test-123'])
            ->assertJson(fn(AssertableJson $json) => $json->hasAll(['slug'])->etc());

        $course = Course::latest()->first();

        $this->assertNotNull($course->slug);
        $this->assertEquals('test-123', $course->slug);
        $this->assertDatabaseHas('courses', ['slug'=>'test-123', 'name'=>$data['name']]);


        $data = Course::factory()->raw(['name'=>'test_123']);

        $this->postJson(route('course.store'), $data)
            ->assertCreated()
            ->assertJsonFragment(['name' => $data['name'], 'slug' => 'test-123-2'])
            ->assertJson(fn(AssertableJson $json) => $json->hasAll(['slug'])->etc());

        $course = Course::latest()->first();

        $course = Course::latest('id')->first();


        $this->assertNotNull($course->slug);
        $this->assertEquals('test-123-2', $course->slug);
        $this->assertDatabaseHas('courses', ['slug'=>'test-123-2', 'name'=>$data['name']]);
    }

    /**
     * @test
     */
    public function test_everyone_can_show_course(){
        $course = Course::factory()->create();

        $this->simulateAllowedOrigin();
        $this->getJson(route('course.show', $course->id))->assertOk()->assertJson($course->toArray());
    }

    /**
     * @test
     */
    public function test_authorized_user_can_update_course(){
        $this->actingAsAdmin();

        $course = Course::factory()->create();

        $this->putJson(route('course.update', $course->id), ['name' => 'test'])->assertOk()->assertJsonFragment(['name' => 'test']);
        $this->assertDatabaseHas(Course::class, ['name' => 'test']);

        $this->actingAsUser();

        $this->putJson(route('course.update', $course->id), ['name' => 'test2'])->assertForbidden();
        $this->assertDatabaseMissing(Course::class, ['name' => 'test2']);
    }

    /**
     * @test
     */
    public function test_course_name_is_required_on_update(){

        $this->actingAsAdmin();

        $course = Course::factory()->create();
        $data = Course::factory()->raw();
        unset($data['name']);

        $this->putJson(route('course.update', $course->id), $data)->assertJsonValidationErrorFor('name');
        $this->assertDatabaseMissing(Course::class, ['name' => null]);
    }

    /**
     * @test
     */
    public function test_name_must_be_max_255_chars_on_update(){
        $this->actingAsAdmin();

        $dataErr = Course::factory()->raw(['name' => Str::random(256)]);
        $dataOk = Course::factory()->raw(['name' => Str::random(255)]);

        $course = Course::factory()->create();

        $this->putJson(route('course.update', $course->id), $dataErr)->assertJsonValidationErrorFor('name');
        $this->assertDatabaseMissing(Course::class, ['name' => $dataErr['name']]);

        $this->putJson(route('course.update', $course->id), $dataOk)->assertOk();
        $this->assertDatabaseHas(Course::class, ['name' => $dataOk['name']]);
    }

    /**
     * @test
     */
    public function test_course_name_must_be_string_on_update(){
        $this->actingAsAdmin();

        $course = Course::factory()->create();

        $this->putJson(route('course.update', $course->id), array_merge($course->toArray(),['name'=>123]))->assertJsonValidationErrorFor('name');
        $this->assertDatabaseMissing(Course::class, ['name' => 123]);
    }

    /**
     * @test
     */
    public function test_course_name_must_be_unique_on_update(){
        $this->actingAsAdmin();

        Course::factory()->create(['name'=>'test']);
        $course = Course::factory()->create();

        $this->putJson(route('course.update', $course->id), array_merge($course->toArray(),['name'=>'test']))->assertJsonValidationErrorFor('name');

        $this->putJson(route('course.update', $course->id), array_merge($course->toArray(),['name'=>'test2']))->assertOk()->assertJsonFragment(['name' => 'test2']);
        $this->assertDatabaseHas(Course::class, ['name' => 'test2']);
    }

    /**
     * @test
     */
    public function test_slug_is_not_generated_from_name_on_update(){
        $this->actingAsAdmin();

        $course = Course::factory()->create(['name'=>'test 123']);
        $this->assertNotNull($course->slug);
        $this->assertEquals('test-123', $course->slug);

        $this->putJson(route('course.update', $course->id), array_merge($course->toArray(), ['name'=>'test 456']))
            ->assertOk()
            ->assertJsonFragment(['name' => 'test 456', 'slug' => 'test-123'])
            ->assertJson(fn(AssertableJson $json) => $json->hasAll(['slug'])->etc());

        $course = Course::latest()->first();

        $this->assertNotNull($course->fresh()->slug);
        $this->assertEquals('test-123', $course->fresh()->slug);
        $this->assertDatabaseHas('courses', ['slug'=>'test-123', 'name'=>'test 456']);
    }

    /**
     * @test
     */
    public function test_authorized_user_can_destroy_course(){
        $course = Course::factory()->create(['name' => 'test']);

        $this->actingAsUser();
        $this->deleteJson(route('course.destroy', $course->id))->assertForbidden();
        $this->assertModelExists($course);
        $this->assertDatabaseHas(Course::class, ['name' => 'test']);

        $this->actingAsAdmin();
        $this->deleteJson(route('course.destroy', $course->id))->assertOk();
        $this->assertModelMissing($course);
        $this->assertDatabaseMissing(Course::class, ['name' => 'test']);
    }

}
