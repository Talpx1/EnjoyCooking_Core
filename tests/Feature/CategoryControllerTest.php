<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\Seeders\PermissionsAndRolesSeeder;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;


    protected $seed = true;
    protected $seeder = PermissionsAndRolesSeeder::class;

    /**
     * @test
     */
    public function test_everyone_can_index_categories(){
        $categories = Category::factory(40)->create();

        $this->simulateAllowedOrigin();
        $this->getJson(route('category.index'))->assertOk()->assertJson(function(AssertableJson $json) use ($categories){
            $json->has('data')->etc();
            $categories->splice(0,15)->each(fn($category) => $this->assertTrue(collect($json->toArray()['data'])->pluck('id')->contains($category->id)));
        });
    }

    /**
     * @test
     */
    public function test_category_index_gets_paginated(){
        $categories = Category::factory(40)->create();

        $this->simulateAllowedOrigin();
        $this->getJson(route('category.index'))->assertOk()->assertJson(function(AssertableJson $json) use ($categories){
            $json->hasAll(['data', 'current_page', "next_page_url", "path","per_page", "prev_page_url", "to", "total", "first_page_url", "from", "last_page", "last_page_url", "links"])->etc();
            $categories->chunk(15)[0]->each(fn($category) => $this->assertTrue(collect($json->toArray()['data'])->pluck('id')->contains($category->id)));
            $categories->chunk(15)[1]->each(fn($category) => $this->assertFalse(collect($json->toArray()['data'])->pluck('id')->contains($category->id)));
        });

        $this->getJson(route('category.index',['page'=>2]))->assertOk()->assertJson(function(AssertableJson $json) use ($categories){
            $json->hasAll(['data', 'current_page', "next_page_url", "path","per_page", "prev_page_url", "to", "total", "first_page_url", "from", "last_page", "last_page_url", "links"])->etc();
            $categories->chunk(15)[0]->each(fn($category) => $this->assertFalse(collect($json->toArray()['data'])->pluck('id')->contains($category->id)));
            $categories->chunk(15)[1]->each(fn($category) => $this->assertTrue(collect($json->toArray()['data'])->pluck('id')->contains($category->id)));
        })->assertJsonFragment(['current_page'=>2]);
    }

    /**
     * @test
     */
    public function test_authorized_user_can_store_category(){
        $this->actingAsAdmin();

        $category = Category::factory()->raw();

        $this->postJson(route('category.store'), $category)->assertCreated()->assertJsonFragment(['name' => $category['name']]);

        $this->actingAsUser();

        $category = Category::factory()->raw();

        $this->postJson(route('category.store'), $category)->assertForbidden();
    }

    /**
     * @test
     */
    public function test_category_name_is_required_on_store(){

        $this->actingAsAdmin();

        $category = Category::factory()->raw();

        unset($category['name']);

        $this->postJson(route('category.store'), $category)->assertJsonValidationErrorFor('name');
        $this->assertDatabaseMissing(Category::class, ['name' => null]);
    }

    /**
     * @test
     */
    public function test_category_name_must_be_string_on_store(){

        $this->actingAsAdmin();

        $category = Category::factory()->raw(['name' => 123]);


        $this->postJson(route('category.store'), $category)->assertJsonValidationErrorFor('name');
        $this->assertDatabaseMissing(Category::class, ['name' => 123]);
    }

    /**
     * @test
     */
    public function test_name_must_be_max_255_chars_on_store(){
        $this->actingAsAdmin();
        $nameErr = Str::random(256);
        $nameOk = Str::random(255);

        $category = Category::factory()->raw(['name' => $nameErr]);

        $this->postJson(route('category.store'), $category)->assertJsonValidationErrorFor('name');
        $this->assertDatabaseMissing(Category::class, ['name' => $nameErr]);

        $category = Category::factory()->raw(['name' => $nameOk]);

        $this->postJson(route('category.store'), $category)->assertCreated();
        $this->assertDatabaseHas(Category::class, ['name' => $nameOk]);
    }

    /**
     * @test
     */
    public function test_category_name_must_be_unique_on_store(){

        $this->actingAsAdmin();

        $category = Category::factory()->raw(['name' => 'test']);


        $this->postJson(route('category.store'), $category)->assertCreated()->assertJsonFragment(['name' => $category['name']]);
        $this->assertDatabaseHas(Category::class, ['name' => $category['name']]);

        $this->postJson(route('category.store'), $category)->assertJsonValidationErrorFor('name');
        $this->assertDatabaseCount(Category::class, 1);
    }

    /**
     * @test
     */
    public function test_parent_category_id_is_nullable_on_store(){

        $this->actingAsAdmin();

        $category = Category::factory()->raw(['name' => 'test_123', 'parent_category_id' => null]);

        $this->postJson(route('category.store'), $category)->assertCreated();
        $this->assertDatabaseHas(Category::class, ['name' => $category['name'], 'parent_category_id' => null]);
    }

    /**
     * @test
     */
    public function test_slug_is_generated_from_name_on_store(){
        $this->actingAsAdmin();

        $data = Category::factory()->raw(['name'=>'test 123']);

        $this->postJson(route('category.store'), $data)
            ->assertCreated()
            ->assertJsonFragment(['name' => $data['name'], 'slug' => 'test-123'])
            ->assertJson(fn(AssertableJson $json) => $json->hasAll(['slug'])->etc());

        $category = Category::latest()->first();

        $this->assertNotNull($category->slug);
        $this->assertEquals('test-123', $category->slug);
        $this->assertDatabaseHas('categories', ['slug'=>'test-123', 'name'=>$data['name']]);
    }

    /**
     * @test
     */
    public function test_generated_slug_is_unique_on_store(){
        $this->actingAsAdmin();

        $data = Category::factory()->raw(['name'=>'test 123']);

        $this->postJson(route('category.store'), $data)
            ->assertCreated()
            ->assertJsonFragment(['name' => $data['name'], 'slug' => 'test-123'])
            ->assertJson(fn(AssertableJson $json) => $json->hasAll(['slug'])->etc());

        $category = Category::latest()->first();

        $this->assertNotNull($category->slug);
        $this->assertEquals('test-123', $category->slug);
        $this->assertDatabaseHas('categories', ['slug'=>'test-123', 'name'=>$data['name']]);


        $data = Category::factory()->raw(['name'=>'test_123']);

        $this->postJson(route('category.store'), $data)
            ->assertCreated()
            ->assertJsonFragment(['name' => $data['name'], 'slug' => 'test-123-2'])
            ->assertJson(fn(AssertableJson $json) => $json->hasAll(['slug'])->etc());

        $category = Category::latest()->first();

        $category = Category::latest('id')->first();


        $this->assertNotNull($category->slug);
        $this->assertEquals('test-123-2', $category->slug);
        $this->assertDatabaseHas('categories', ['slug'=>'test-123-2', 'name'=>$data['name']]);
    }

    /**
     * @test
     */
    public function test_everyone_can_show_category(){
        $category = Category::factory()->create();

        $this->simulateAllowedOrigin();
        $this->getJson(route('category.show', $category->id))->assertOk()->assertJson($category->toArray());
    }

    /**
     * @test
     */
    public function test_authorized_user_can_update_category(){
        $this->actingAsAdmin();

        $category = Category::factory()->create();

        $this->putJson(route('category.update', $category->id), ['name' => 'test'])->assertOk()->assertJsonFragment(['name' => 'test']);
        $this->assertDatabaseHas(Category::class, ['name' => 'test']);

        $this->actingAsUser();

        $this->putJson(route('category.update', $category->id), ['name' => 'test2'])->assertForbidden();
        $this->assertDatabaseMissing(Category::class, ['name' => 'test2']);
    }

    /**
     * @test
     */
    public function test_category_name_is_required_on_update(){

        $this->actingAsAdmin();

        $category = Category::factory()->create();
        $data = Category::factory()->raw();
        unset($data['name']);

        $this->putJson(route('category.update', $category->id), $data)->assertJsonValidationErrorFor('name');
        $this->assertDatabaseMissing(Category::class, ['name' => null]);
    }

    /**
     * @test
     */
    public function test_name_must_be_max_255_chars_on_update(){
        $this->actingAsAdmin();

        $dataErr = Category::factory()->raw(['name' => Str::random(256)]);
        $dataOk = Category::factory()->raw(['name' => Str::random(255)]);

        $category = Category::factory()->create();

        $this->putJson(route('category.update', $category->id), $dataErr)->assertJsonValidationErrorFor('name');
        $this->assertDatabaseMissing(Category::class, ['name' => $dataErr['name']]);

        $this->putJson(route('category.update', $category->id), $dataOk)->assertOk();
        $this->assertDatabaseHas(Category::class, ['name' => $dataOk['name']]);
    }

    /**
     * @test
     */
    public function test_category_name_must_be_string_on_update(){
        $this->actingAsAdmin();

        $category = Category::factory()->create();

        $this->putJson(route('category.update', $category->id), array_merge($category->toArray(),['name'=>123]))->assertJsonValidationErrorFor('name');
        $this->assertDatabaseMissing(Category::class, ['name' => 123]);
    }

    /**
     * @test
     */
    public function test_category_name_must_be_unique_on_update(){
        $this->actingAsAdmin();

        Category::factory()->create(['name'=>'test']);
        $category = Category::factory()->create();

        $this->putJson(route('category.update', $category->id), array_merge($category->toArray(),['name'=>'test']))->assertJsonValidationErrorFor('name');

        $this->putJson(route('category.update', $category->id), array_merge($category->toArray(),['name'=>'test2']))->assertOk()->assertJsonFragment(['name' => 'test2']);
        $this->assertDatabaseHas(Category::class, ['name' => 'test2']);
    }

    /**
     * @test
     */
    public function test_parent_category_id_is_nullable_on_update(){
        $this->actingAsAdmin();

        $category = Category::factory()->create(['name'=>'test']);

        $this->putJson(route('category.update', $category->id), array_merge($category->toArray(),['parent_category_id' => null]))->assertOk();
        $this->assertDatabaseHas(Category::class, ['name' => 'test', 'parent_category_id' => null]);
    }

    /**
     * @test
     */
    public function test_slug_is_not_generated_from_name_on_update(){
        $this->actingAsAdmin();

        $category = Category::factory()->create(['name'=>'test 123']);
        $this->assertNotNull($category->slug);
        $this->assertEquals('test-123', $category->slug);

        $this->putJson(route('category.update', $category->id), array_merge($category->toArray(), ['name'=>'test 456']))
            ->assertOk()
            ->assertJsonFragment(['name' => 'test 456', 'slug' => 'test-123'])
            ->assertJson(fn(AssertableJson $json) => $json->hasAll(['slug'])->etc());

        $category = Category::latest()->first();

        $this->assertNotNull($category->fresh()->slug);
        $this->assertEquals('test-123', $category->fresh()->slug);
        $this->assertDatabaseHas('categories', ['slug'=>'test-123', 'name'=>'test 456']);
    }

    /**
     * @test
     */
    public function test_authorized_user_can_destroy_category(){
        $category = Category::factory()->create(['name' => 'test']);

        $this->actingAsUser();
        $this->deleteJson(route('category.destroy', $category->id))->assertForbidden();
        $this->assertModelExists($category);
        $this->assertDatabaseHas(Category::class, ['name' => 'test']);

        $this->actingAsAdmin();
        $this->deleteJson(route('category.destroy', $category->id))->assertOk();
        $this->assertModelMissing($category);
        $this->assertDatabaseMissing(Category::class, ['name' => 'test']);
    }

}
