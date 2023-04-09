<?php

namespace Tests\Feature;

use App\Models\DifficultyLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\Seeders\PermissionsAndRolesSeeder;
use Tests\TestCase;

class DifficultyLevelControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;
    protected $seeder = PermissionsAndRolesSeeder::class;

    /**
     * @test
     */
    public function test_everyone_can_index_difficulty_levels(){
        $difficulty_levels = DifficultyLevel::factory(40)->create();

        $this->simulateAllowedOrigin();
        $this->getJson(route('difficulty_level.index'))->assertOk()->assertJson(function(AssertableJson $json) use ($difficulty_levels){
            $json->has('data')->etc();
            $difficulty_levels->splice(0,15)->each(fn($difficulty_level) => $this->assertTrue(collect($json->toArray()['data'])->pluck('id')->contains($difficulty_level->id)));
        });
    }

    /**
     * @test
     */
    public function test_difficulty_level_index_gets_paginated(){
        $difficulty_levels = DifficultyLevel::factory(40)->create();

        $this->simulateAllowedOrigin();
        $this->getJson(route('difficulty_level.index'))->assertOk()->assertJson(function(AssertableJson $json) use ($difficulty_levels){
            $json->hasAll(['data', 'current_page', "next_page_url", "path","per_page", "prev_page_url", "to", "total", "first_page_url", "from", "last_page", "last_page_url", "links"])->etc();
            $difficulty_levels->chunk(15)[0]->each(fn($difficulty_level) => $this->assertTrue(collect($json->toArray()['data'])->pluck('id')->contains($difficulty_level->id)));
            $difficulty_levels->chunk(15)[1]->each(fn($difficulty_level) => $this->assertFalse(collect($json->toArray()['data'])->pluck('id')->contains($difficulty_level->id)));
        });

        $this->getJson(route('difficulty_level.index',['page'=>2]))->assertOk()->assertJson(function(AssertableJson $json) use ($difficulty_levels){
            $json->hasAll(['data', 'current_page', "next_page_url", "path","per_page", "prev_page_url", "to", "total", "first_page_url", "from", "last_page", "last_page_url", "links"])->etc();
            $difficulty_levels->chunk(15)[0]->each(fn($difficulty_level) => $this->assertFalse(collect($json->toArray()['data'])->pluck('id')->contains($difficulty_level->id)));
            $difficulty_levels->chunk(15)[1]->each(fn($difficulty_level) => $this->assertTrue(collect($json->toArray()['data'])->pluck('id')->contains($difficulty_level->id)));
        })->assertJsonFragment(['current_page'=>2]);
    }

    /**
     * @test
     */
    public function test_authorized_user_can_store_difficulty_level(){
        $this->actingAsAdmin();

        $difficulty_level = DifficultyLevel::factory()->raw();

        $this->postJson(route('difficulty_level.store'), $difficulty_level)->assertCreated()->assertJsonFragment(['name' => $difficulty_level['name']]);

        $this->actingAsUser();

        $difficulty_level = DifficultyLevel::factory()->raw();

        $this->postJson(route('difficulty_level.store'), $difficulty_level)->assertForbidden();
    }

    /**
     * @test
     */
    public function test_difficulty_level_name_is_required_on_store(){

        $this->actingAsAdmin();

        $difficulty_level = DifficultyLevel::factory()->raw();

        unset($difficulty_level['name']);

        $this->postJson(route('difficulty_level.store'), $difficulty_level)->assertJsonValidationErrorFor('name');
        $this->assertDatabaseMissing(DifficultyLevel::class, ['name' => null]);
    }

    /**
     * @test
     */
    public function test_difficulty_level_name_must_be_string_on_store(){

        $this->actingAsAdmin();

        $difficulty_level = DifficultyLevel::factory()->raw(['name' => 123]);


        $this->postJson(route('difficulty_level.store'), $difficulty_level)->assertJsonValidationErrorFor('name');
        $this->assertDatabaseMissing(DifficultyLevel::class, ['name' => 123]);
    }

    /**
     * @test
     */
    public function test_name_must_be_max_255_chars_on_store(){
        $this->actingAsAdmin();
        $nameErr = Str::random(256);
        $nameOk = Str::random(255);

        $difficulty_level = DifficultyLevel::factory()->raw(['name' => $nameErr]);

        $this->postJson(route('difficulty_level.store'), $difficulty_level)->assertJsonValidationErrorFor('name');
        $this->assertDatabaseMissing(DifficultyLevel::class, ['name' => $nameErr]);

        $difficulty_level = DifficultyLevel::factory()->raw(['name' => $nameOk]);

        $this->postJson(route('difficulty_level.store'), $difficulty_level)->assertCreated();
        $this->assertDatabaseHas(DifficultyLevel::class, ['name' => $nameOk]);
    }

    /**
     * @test
     */
    public function test_difficulty_level_name_must_be_unique_on_store(){

        $this->actingAsAdmin();

        $difficulty_level = DifficultyLevel::factory()->raw(['name' => 'test']);


        $this->postJson(route('difficulty_level.store'), $difficulty_level)->assertCreated()->assertJsonFragment(['name' => $difficulty_level['name']]);
        $this->assertDatabaseHas(DifficultyLevel::class, ['name' => $difficulty_level['name']]);

        $this->postJson(route('difficulty_level.store'), $difficulty_level)->assertJsonValidationErrorFor('name');
        $this->assertDatabaseCount(DifficultyLevel::class, 1);
    }

    /**
     * @test
     */
    public function test_slug_is_generated_from_name_on_store(){
        $this->actingAsAdmin();

        $data = DifficultyLevel::factory()->raw(['name'=>'test 123']);

        $this->postJson(route('difficulty_level.store'), $data)
            ->assertCreated()
            ->assertJsonFragment(['name' => $data['name'], 'slug' => 'test-123'])
            ->assertJson(fn(AssertableJson $json) => $json->hasAll(['slug'])->etc());

        $difficulty_level = DifficultyLevel::latest()->first();

        $this->assertNotNull($difficulty_level->slug);
        $this->assertEquals('test-123', $difficulty_level->slug);
        $this->assertDatabaseHas('difficulty_levels', ['slug'=>'test-123', 'name'=>$data['name']]);
    }

    /**
     * @test
     */
    public function test_generated_slug_is_unique_on_store(){
        $this->actingAsAdmin();

        $data = DifficultyLevel::factory()->raw(['name'=>'test 123']);

        $this->postJson(route('difficulty_level.store'), $data)
            ->assertCreated()
            ->assertJsonFragment(['name' => $data['name'], 'slug' => 'test-123'])
            ->assertJson(fn(AssertableJson $json) => $json->hasAll(['slug'])->etc());

        $difficulty_level = DifficultyLevel::latest()->first();

        $this->assertNotNull($difficulty_level->slug);
        $this->assertEquals('test-123', $difficulty_level->slug);
        $this->assertDatabaseHas('difficulty_levels', ['slug'=>'test-123', 'name'=>$data['name']]);


        $data = DifficultyLevel::factory()->raw(['name'=>'test_123']);

        $this->postJson(route('difficulty_level.store'), $data)
            ->assertCreated()
            ->assertJsonFragment(['name' => $data['name'], 'slug' => 'test-123-2'])
            ->assertJson(fn(AssertableJson $json) => $json->hasAll(['slug'])->etc());

        $difficulty_level = DifficultyLevel::latest()->first();

        $difficulty_level = DifficultyLevel::latest('id')->first();


        $this->assertNotNull($difficulty_level->slug);
        $this->assertEquals('test-123-2', $difficulty_level->slug);
        $this->assertDatabaseHas('difficulty_levels', ['slug'=>'test-123-2', 'name'=>$data['name']]);
    }

    /**
     * @test
     */
    public function test_everyone_can_show_difficulty_level(){
        $difficulty_level = DifficultyLevel::factory()->create();

        $this->simulateAllowedOrigin();
        $this->getJson(route('difficulty_level.show', $difficulty_level->id))->assertOk()->assertJson($difficulty_level->toArray());
    }

    /**
     * @test
     */
    public function test_authorized_user_can_update_difficulty_level(){
        $this->actingAsAdmin();

        $difficulty_level = DifficultyLevel::factory()->create();

        $this->putJson(route('difficulty_level.update', $difficulty_level->id), ['name' => 'test'])->assertOk()->assertJsonFragment(['name' => 'test']);
        $this->assertDatabaseHas(DifficultyLevel::class, ['name' => 'test']);

        $this->actingAsUser();

        $this->putJson(route('difficulty_level.update', $difficulty_level->id), ['name' => 'test2'])->assertForbidden();
        $this->assertDatabaseMissing(DifficultyLevel::class, ['name' => 'test2']);
    }

    /**
     * @test
     */
    public function test_difficulty_level_name_is_required_on_update(){

        $this->actingAsAdmin();

        $difficulty_level = DifficultyLevel::factory()->create();
        $data = DifficultyLevel::factory()->raw();
        unset($data['name']);

        $this->putJson(route('difficulty_level.update', $difficulty_level->id), $data)->assertJsonValidationErrorFor('name');
        $this->assertDatabaseMissing(DifficultyLevel::class, ['name' => null]);
    }

    /**
     * @test
     */
    public function test_name_must_be_max_255_chars_on_update(){
        $this->actingAsAdmin();

        $dataErr = DifficultyLevel::factory()->raw(['name' => Str::random(256)]);
        $dataOk = DifficultyLevel::factory()->raw(['name' => Str::random(255)]);

        $difficulty_level = DifficultyLevel::factory()->create();

        $this->putJson(route('difficulty_level.update', $difficulty_level->id), $dataErr)->assertJsonValidationErrorFor('name');
        $this->assertDatabaseMissing(DifficultyLevel::class, ['name' => $dataErr['name']]);

        $this->putJson(route('difficulty_level.update', $difficulty_level->id), $dataOk)->assertOk();
        $this->assertDatabaseHas(DifficultyLevel::class, ['name' => $dataOk['name']]);
    }

    /**
     * @test
     */
    public function test_difficulty_level_name_must_be_string_on_update(){
        $this->actingAsAdmin();

        $difficulty_level = DifficultyLevel::factory()->create();

        $this->putJson(route('difficulty_level.update', $difficulty_level->id), array_merge($difficulty_level->toArray(),['name'=>123]))->assertJsonValidationErrorFor('name');
        $this->assertDatabaseMissing(DifficultyLevel::class, ['name' => 123]);
    }

    /**
     * @test
     */
    public function test_difficulty_level_name_must_be_unique_on_update(){
        $this->actingAsAdmin();

        DifficultyLevel::factory()->create(['name'=>'test']);
        $difficulty_level = DifficultyLevel::factory()->create();

        $this->putJson(route('difficulty_level.update', $difficulty_level->id), array_merge($difficulty_level->toArray(),['name'=>'test']))->assertJsonValidationErrorFor('name');

        $this->putJson(route('difficulty_level.update', $difficulty_level->id), array_merge($difficulty_level->toArray(),['name'=>'test2']))->assertOk()->assertJsonFragment(['name' => 'test2']);
        $this->assertDatabaseHas(DifficultyLevel::class, ['name' => 'test2']);
    }

    /**
     * @test
     */
    public function test_slug_is_not_generated_from_name_on_update(){
        $this->actingAsAdmin();

        $difficulty_level = DifficultyLevel::factory()->create(['name'=>'test 123']);
        $this->assertNotNull($difficulty_level->slug);
        $this->assertEquals('test-123', $difficulty_level->slug);

        $this->putJson(route('difficulty_level.update', $difficulty_level->id), array_merge($difficulty_level->toArray(), ['name'=>'test 456']))
            ->assertOk()
            ->assertJsonFragment(['name' => 'test 456', 'slug' => 'test-123'])
            ->assertJson(fn(AssertableJson $json) => $json->hasAll(['slug'])->etc());

        $difficulty_level = DifficultyLevel::latest()->first();

        $this->assertNotNull($difficulty_level->fresh()->slug);
        $this->assertEquals('test-123', $difficulty_level->fresh()->slug);
        $this->assertDatabaseHas('difficulty_levels', ['slug'=>'test-123', 'name'=>'test 456']);
    }

    /**
     * @test
     */
    public function test_authorized_user_can_destroy_difficulty_level(){
        $difficulty_level = DifficultyLevel::factory()->create(['name' => 'test']);

        $this->actingAsUser();
        $this->deleteJson(route('difficulty_level.destroy', $difficulty_level->id))->assertForbidden();
        $this->assertModelExists($difficulty_level);
        $this->assertDatabaseHas(DifficultyLevel::class, ['name' => 'test']);

        $this->actingAsAdmin();
        $this->deleteJson(route('difficulty_level.destroy', $difficulty_level->id))->assertOk();
        $this->assertModelMissing($difficulty_level);
        $this->assertDatabaseMissing(DifficultyLevel::class, ['name' => 'test']);
    }
}
