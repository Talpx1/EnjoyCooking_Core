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
    public function test_everyone_can_index_categories_filtering_by_name(){
        $categories = Category::factory(40)->create();
        Category::factory()->create(['name'=>'test']);
        Category::factory()->create(['name'=>'test1']);
        Category::factory()->create(['name'=>'test2']);
        Category::factory()->create(['name'=>'test3']);
        Category::factory()->create(['name'=>'test4']);

        $this->simulateAllowedOrigin();

        $this->getJson(route('category.index', ['search'=>'test']))->assertOk()->assertJson(function(AssertableJson $json){
            $json->has('data')->etc();
            $json->count('data', 5);
            collect($json->toArray()['data'])->pluck('name')->each(fn($name) => $this->assertStringContainsString('test', $name));
        });

        $this->getJson(route('category.index'))->assertOk()->assertJson(function(AssertableJson $json) use ($categories){
            $json->has('data')->etc();
            $json->count('data', 15);
            $categories->splice(0,15)->each(fn($category) => $this->assertTrue(collect($json->toArray()['data'])->pluck('id')->contains($category->id)));
        });
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

    /**
     * @test
     */
    public function test_everyone_can_get_categorys_subcategory(){
        $category = Category::factory()->create();
        $subcategories = Category::factory(40)->create(['parent_category_id'=>$category->id]);

        $this->simulateAllowedOrigin();

        $this->getJson(route('category.subcategories', ['category' => $category->id]))->assertOk()->assertJson(function(AssertableJson $json) use ($subcategories){
            $json->has('data')->etc();
            $subcategories->chunk(15)->first()->each(fn($category) => $this->assertTrue(collect($json->toArray()['data'])->pluck('id')->contains($category->id)));
        });
    }

    public function test_categorys_subcategories_are_paginated(){
        $category = Category::factory()->create();
        $subcategories = Category::factory(40)->create(['parent_category_id'=>$category->id]);
        $other_category = Category::factory()->create();
        $other_subcategories = Category::factory(40)->create(['parent_category_id'=>$other_category->id]);

        $this->simulateAllowedOrigin();

        $this->getJson(route('category.subcategories', ['category' => $category->id]))->assertOk()->assertJson(function(AssertableJson $json) use ($subcategories, $category){
            $json->hasAll(['data', 'current_page', "next_page_url", "path","per_page", "prev_page_url", "to", "total", "first_page_url", "from", "last_page", "last_page_url", "links"])->etc();
            $subcategories->chunk(15)->first()->each(fn($subcategory) => $this->assertTrue(collect($json->toArray()['data'])->pluck('id')->contains($subcategory->id)));
            $subcategories->chunk(15)->first()->each(fn($subcategory) => $this->assertEquals($category->id, $subcategory->parent_category_id));
        });

        $this->getJson(route('category.subcategories', ['category' => $category->id, 'page'=>2]))->assertOk()->assertJson(function(AssertableJson $json) use ($subcategories, $category){
            $json->hasAll(['data', 'current_page', "next_page_url", "path","per_page", "prev_page_url", "to", "total", "first_page_url", "from", "last_page", "last_page_url", "links"])->etc();
            $subcategories->chunk(15)->get(1)->each(fn($subcategory) => $this->assertTrue(collect($json->toArray()['data'])->pluck('id')->contains($subcategory->id)));
            $subcategories->chunk(15)->get(1)->each(fn($subcategory) => $this->assertEquals($category->id, $subcategory->parent_category_id));
        });

        $this->getJson(route('category.subcategories', ['category' => $category->id, 'page'=>3]))->assertOk()->assertJson(function(AssertableJson $json) use ($subcategories, $category){
            $json->hasAll(['data', 'current_page', "next_page_url", "path","per_page", "prev_page_url", "to", "total", "first_page_url", "from", "last_page", "last_page_url", "links"])->etc();
            $subcategories->chunk(15)->get(2)->each(fn($subcategory) => $this->assertTrue(collect($json->toArray()['data'])->pluck('id')->contains($subcategory->id)));
            $subcategories->chunk(15)->get(2)->each(fn($subcategory) => $this->assertEquals($category->id, $subcategory->parent_category_id));
        });



        $this->getJson(route('category.subcategories', ['category' => $other_category->id]))->assertOk()->assertJson(function(AssertableJson $json) use ($other_subcategories, $other_category){
            $json->hasAll(['data', 'current_page', "next_page_url", "path","per_page", "prev_page_url", "to", "total", "first_page_url", "from", "last_page", "last_page_url", "links"])->etc();
            $other_subcategories->chunk(15)->first()->each(fn($subcategory) => $this->assertTrue(collect($json->toArray()['data'])->pluck('id')->contains($subcategory->id)));
            $other_subcategories->chunk(15)->first()->each(fn($subcategory) => $this->assertEquals($other_category->id, $subcategory->parent_category_id));
        });

        $this->getJson(route('category.subcategories', ['category' => $other_category->id, 'page'=>2]))->assertOk()->assertJson(function(AssertableJson $json) use ($other_subcategories, $other_category){
            $json->hasAll(['data', 'current_page', "next_page_url", "path","per_page", "prev_page_url", "to", "total", "first_page_url", "from", "last_page", "last_page_url", "links"])->etc();
            $other_subcategories->chunk(15)->get(1)->each(fn($subcategory) => $this->assertTrue(collect($json->toArray()['data'])->pluck('id')->contains($subcategory->id)));
            $other_subcategories->chunk(15)->get(1)->each(fn($subcategory) => $this->assertEquals($other_category->id, $subcategory->parent_category_id));
        });

        $this->getJson(route('category.subcategories', ['category' => $other_category->id, 'page'=>3]))->assertOk()->assertJson(function(AssertableJson $json) use ($other_subcategories, $other_category){
            $json->hasAll(['data', 'current_page', "next_page_url", "path","per_page", "prev_page_url", "to", "total", "first_page_url", "from", "last_page", "last_page_url", "links"])->etc();
            $other_subcategories->chunk(15)->get(2)->each(fn($subcategory) => $this->assertTrue(collect($json->toArray()['data'])->pluck('id')->contains($subcategory->id)));
            $other_subcategories->chunk(15)->get(2)->each(fn($subcategory) => $this->assertEquals($other_category->id, $subcategory->parent_category_id));
        });

    }

    /**
     * @test
     */
    public function test_everyone_can_get_first_level_categories(){
        Category::factory()->create(['name' => 'fist_lvl_1']);
        Category::factory()->create(['name' => 'fist_lvl_2']);
        Category::factory()->create(['name' => 'fist_lvl_3']);
        Category::factory()->create(['name' => 'fist_lvl_4']);
        Category::factory()->create(['name' => 'fist_lvl_5']);
        $first_lvl_categories = Category::all();

        $first_lvl_categories->each(function($parent_category) {
            Category::factory()->create(['name'=>uniqid('sucategory_'), 'parent_category_id'=>$parent_category->id]);
            Category::factory()->create(['name'=>uniqid('sucategory_'), 'parent_category_id'=>$parent_category->id]);
        });

        $this->simulateAllowedOrigin();

        $this->getJson(route('category.first_level'))->assertOk()->assertJson(function(AssertableJson $json) use ($first_lvl_categories){
            $json->has('data')->etc();
            $json->count('data', 5);
            $first_lvl_categories->each(fn($category) => $this->assertTrue(collect($json->toArray()['data'])->pluck('id')->contains($category->id)));
            collect($json->toArray()['data'])->pluck('parent_category_id')->each(fn($parent_category_id) => $this->assertNull($parent_category_id));
        });
    }

    public function test_first_level_categories_get_paginated(){
        $categories = Category::factory(40)->create();

        $this->simulateAllowedOrigin();

        $this->getJson(route('category.first_level'))->assertOk()->assertJson(function(AssertableJson $json) use ($categories){
            $json->hasAll(['data', 'current_page', "next_page_url", "path","per_page", "prev_page_url", "to", "total", "first_page_url", "from", "last_page", "last_page_url", "links"])->etc();
            $categories->chunk(15)->first()->each(fn($category) => $this->assertContains($category->id, collect($json->toArray()['data'])->pluck('id')));
        });

        $this->getJson(route('category.first_level', ['page'=>2]))->assertOk()->assertJson(function(AssertableJson $json) use ($categories){
            $json->hasAll(['data', 'current_page', "next_page_url", "path","per_page", "prev_page_url", "to", "total", "first_page_url", "from", "last_page", "last_page_url", "links"])->etc();
            $categories->chunk(15)->get(1)->each(fn($category) => $this->assertContains($category->id, collect($json->toArray()['data'])->pluck('id')));
        });

        $this->getJson(route('category.first_level', ['page'=>3]))->assertOk()->assertJson(function(AssertableJson $json) use ($categories){
            $json->hasAll(['data', 'current_page', "next_page_url", "path","per_page", "prev_page_url", "to", "total", "first_page_url", "from", "last_page", "last_page_url", "links"])->etc();
            $categories->chunk(15)->get(2)->each(fn($category) => $this->assertContains($category->id, collect($json->toArray()['data'])->pluck('id')));
        });

    }

}
