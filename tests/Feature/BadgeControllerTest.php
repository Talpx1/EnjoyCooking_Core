<?php

namespace Tests\Feature;

use App\Models\Badge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\Seeders\PermissionsAndRolesSeeder;
use Tests\TestCase;

class BadgeControllerTest extends TestCase
{
    use RefreshDatabase;


    protected $seed = true;
    protected $seeder = PermissionsAndRolesSeeder::class;

    /**
     * @test
     */
    public function test_everyone_can_index_badges(){
        $badges = Badge::factory(40)->create();

        $this->simulateAllowedOrigin();
        $this->getJson(route('badge.index'))->assertOk()->assertJson(function(AssertableJson $json) use ($badges){
            $json->has('data')->etc();
            $badges->splice(0,15)->each(fn($badge) => $this->assertTrue(collect($json->toArray()['data'])->pluck('id')->contains($badge->id)));
        });
    }

    /**
     * @test
     */
    public function test_badge_index_gets_paginated(){
        $badges = Badge::factory(40)->create();

        $this->simulateAllowedOrigin();
        $this->getJson(route('badge.index'))->assertOk()->assertJson(function(AssertableJson $json) use ($badges){
            $json->hasAll(['data', 'current_page', "next_page_url", "path","per_page", "prev_page_url", "to", "total", "first_page_url", "from", "last_page", "last_page_url", "links"])->etc();
            $badges->chunk(15)[0]->each(fn($badge) => $this->assertTrue(collect($json->toArray()['data'])->pluck('id')->contains($badge->id)));
            $badges->chunk(15)[1]->each(fn($badge) => $this->assertFalse(collect($json->toArray()['data'])->pluck('id')->contains($badge->id)));
        });

        $this->getJson(route('badge.index',['page'=>2]))->assertOk()->assertJson(function(AssertableJson $json) use ($badges){
            $json->hasAll(['data', 'current_page', "next_page_url", "path","per_page", "prev_page_url", "to", "total", "first_page_url", "from", "last_page", "last_page_url", "links"])->etc();
            $badges->chunk(15)[0]->each(fn($badge) => $this->assertFalse(collect($json->toArray()['data'])->pluck('id')->contains($badge->id)));
            $badges->chunk(15)[1]->each(fn($badge) => $this->assertTrue(collect($json->toArray()['data'])->pluck('id')->contains($badge->id)));
        })->assertJsonFragment(['current_page'=>2]);
    }

    /**
     * @test
     */
    public function test_authorized_user_can_store_badge(){
        $this->actingAsAdmin();

        $badge = Badge::factory()->raw();

        $this->postJson(route('badge.store'), $badge)->assertCreated()->assertJsonFragment(['title' => $badge['title'], 'description' => $badge['description']]);

        $this->actingAsUser();

        $badge = Badge::factory()->raw();

        $this->postJson(route('badge.store'), $badge)->assertForbidden();
    }

    /**
     * @test
     */
    public function test_badge_title_is_required_on_store(){

        $this->actingAsAdmin();

        $badge = Badge::factory()->raw();

        unset($badge['title']);

        $this->postJson(route('badge.store'), $badge)->assertJsonValidationErrorFor('title');
        $this->assertDatabaseMissing(Badge::class, ['title' => null, 'description' => $badge['description']]);
    }

    /**
     * @test
     */
    public function test_badge_title_must_be_string_on_store(){

        $this->actingAsAdmin();

        $badge = Badge::factory()->raw(['title' => 123, 'description' => 'test_description_123']);


        $this->postJson(route('badge.store'), $badge)->assertJsonValidationErrorFor('title');
        $this->assertDatabaseMissing(Badge::class, ['title' => 123, 'description' => $badge['description']]);
    }

    /**
     * @test
     */
    public function test_title_must_be_max_255_chars_on_store(){
        $this->actingAsAdmin();
        $nameErr = Str::random(256);
        $nameOk = Str::random(255);

        $badge = Badge::factory()->raw(['title' => $nameErr]);

        $this->postJson(route('badge.store'), $badge)->assertJsonValidationErrorFor('title');
        $this->assertDatabaseMissing(Badge::class, ['title' => $nameErr, 'description' => $badge['description']]);

        $badge = Badge::factory()->raw(['title' => $nameOk]);

        $this->postJson(route('badge.store'), $badge)->assertCreated();
        $this->assertDatabaseHas(Badge::class, ['title' => $nameOk, 'description' => $badge['description']]);
    }

    /**
     * @test
     */
    public function test_badge_title_must_be_unique_on_store(){

        $this->actingAsAdmin();

        $badge = Badge::factory()->raw(['title' => 'test', 'description' => 'test_description_123']);


        $this->postJson(route('badge.store'), $badge)->assertCreated()->assertJsonFragment(['title' => $badge['title'], 'description' => $badge['description']]);
        $this->assertDatabaseHas(Badge::class, ['title' => $badge['title'], 'description' => $badge['description']]);

        $this->postJson(route('badge.store'), $badge)->assertJsonValidationErrorFor('title');
        $this->assertDatabaseCount(Badge::class, 1);
    }

    /**
     * @test
     */
    public function test_badge_description_is_nullable_on_store(){

        $this->actingAsAdmin();

        $badge = Badge::factory()->raw(['title' => 'test_123', 'description' => null]);

        $this->postJson(route('badge.store'), $badge)->assertCreated();
        $this->assertDatabaseHas(Badge::class, ['title' => $badge['title'], 'description' => null]);
    }

    /**
     * @test
     */
    public function test_badge_description_must_be_string_on_store(){

        $this->actingAsAdmin();

        $badge = Badge::factory()->raw(['title' => 'test', 'description' => 123]);

        $this->postJson(route('badge.store'), $badge)->assertJsonValidationErrorFor('description');
        $this->assertDatabaseMissing(Badge::class, ['title' => $badge['title'], 'description' => 123]);
    }

    /**
     * @test
     */
    public function test_description_must_be_max_255_chars_on_store(){
        $this->actingAsAdmin();
        $nameErr = Str::random(256);
        $nameOk = Str::random(255);

        $badge = Badge::factory()->raw(['description' => $nameErr]);

        $this->postJson(route('badge.store'), $badge)->assertJsonValidationErrorFor('description');
        $this->assertDatabaseMissing(Badge::class, ['description' => $nameErr, 'title' => $badge['title']]);

        $badge = Badge::factory()->raw(['description' => $nameOk]);

        $this->postJson(route('badge.store'), $badge)->assertCreated();
        $this->assertDatabaseHas(Badge::class, ['description' => $nameOk, 'title' => $badge['title']]);
    }

    /**
     * @test
     */
    public function test_everyone_can_show_badge(){
        $badge = Badge::factory()->create();

        $this->simulateAllowedOrigin();
        $this->getJson(route('badge.show', $badge->id))->assertOk()->assertJson($badge->toArray());
    }

    /**
     * @test
     */
    public function test_authorized_user_can_update_badge(){
        $this->actingAsAdmin();

        $badge = Badge::factory()->create();

        $this->putJson(route('badge.update', $badge->id), ['title' => 'test', 'description' => 'test_description_123'])->assertOk()->assertJsonFragment(['title' => 'test', 'description' => 'test_description_123']);
        $this->assertDatabaseHas(Badge::class, ['title' => 'test']);

        $this->actingAsUser();

        $this->putJson(route('badge.update', $badge->id), ['title' => 'test2', 'description' => 'test_description_345'])->assertForbidden();
        $this->assertDatabaseMissing(Badge::class, ['title' => 'test2']);
    }

    /**
     * @test
     */
    public function test_badge_title_is_required_on_update(){

        $this->actingAsAdmin();

        $badge = Badge::factory()->create();
        $data = Badge::factory()->raw();
        unset($data['title']);

        $this->putJson(route('badge.update', $badge->id), $data)->assertJsonValidationErrorFor('title');
        $this->assertDatabaseMissing(Badge::class, ['title' => null, 'description' => $data['description']]);
    }

    /**
     * @test
     */
    public function test_title_must_be_max_255_chars_on_update(){
        $this->actingAsAdmin();

        $dataErr = Badge::factory()->raw(['title' => Str::random(256)]);
        $dataOk = Badge::factory()->raw(['title' => Str::random(255)]);

        $badge = Badge::factory()->create();

        $this->putJson(route('badge.update', $badge->id), $dataErr)->assertJsonValidationErrorFor('title');
        $this->assertDatabaseMissing(Badge::class, ['title' => $dataErr['title'], 'description' => $dataErr['description']]);

        $this->putJson(route('badge.update', $badge->id), $dataOk)->assertOk();
        $this->assertDatabaseHas(Badge::class, ['title' => $dataOk['title'], 'description' => $dataOk['description']]);
    }

    /**
     * @test
     */
    public function test_badge_title_must_be_string_on_update(){
        $this->actingAsAdmin();

        $badge = Badge::factory()->create();

        $this->putJson(route('badge.update', $badge->id), array_merge($badge->toArray(),['title'=>123]))->assertJsonValidationErrorFor('title');
        $this->assertDatabaseMissing(Badge::class, ['title' => 123, 'description' => $badge->description]);
    }

    /**
     * @test
     */
    public function test_badge_title_must_be_unique_on_update(){
        $this->actingAsAdmin();

        Badge::factory()->create(['title'=>'test']);
        $badge = Badge::factory()->create();

        $this->putJson(route('badge.update', $badge->id), array_merge($badge->toArray(),['title'=>'test']))->assertJsonValidationErrorFor('title');

        $this->putJson(route('badge.update', $badge->id), array_merge($badge->toArray(),['title'=>'test2']))->assertOk()->assertJsonFragment(['title' => 'test2', 'description' => $badge->description]);
        $this->assertDatabaseHas(Badge::class, ['title' => 'test2', 'description' => $badge->description]);
    }

    /**
     * @test
     */
    public function test_badge_description_is_nullable_on_update(){
        $this->actingAsAdmin();

        $badge = Badge::factory()->create(['title'=>'test']);

        $this->putJson(route('badge.update', $badge->id), array_merge($badge->toArray(),['description' => null]))->assertOk();
        $this->assertDatabaseHas(Badge::class, ['title' => 'test', 'description' => null]);
    }

    /**
     * @test
     */
    public function test_badge_description_must_be_string_on_update(){
        $this->actingAsAdmin();

        $badge = Badge::factory()->create(['title'=>'test']);

        $this->putJson(route('badge.update', $badge->id), array_merge($badge->toArray(),['description' => 111]))->assertJsonValidationErrorFor('description');
        $this->assertDatabaseMissing(Badge::class, ['title' => $badge->title, 'description' => 111]);

        $this->putJson(route('badge.update', $badge->id), array_merge($badge->toArray(),['description' => 'aaa']))->assertOk()->assertJsonFragment(['title' => $badge->title, 'description' => 'aaa']);
        $this->assertDatabaseHas(Badge::class, ['title' => $badge->title, 'description' => 'aaa']);
    }

    /**
     * @test
     */
    public function test_description_must_be_max_255_chars_on_update(){
        $this->actingAsAdmin();

        $dataErr = Badge::factory()->raw(['description' => Str::random(256)]);
        $dataOk = Badge::factory()->raw(['description' => Str::random(255)]);

        $badge = Badge::factory()->create();

        $this->putJson(route('badge.update', $badge->id), $dataErr)->assertJsonValidationErrorFor('description');
        $this->assertDatabaseMissing(Badge::class, ['description' => $dataErr['description'], 'title' => $dataErr['title']]);

        $this->putJson(route('badge.update', $badge->id), $dataOk)->assertOk();
        $this->assertDatabaseHas(Badge::class, ['description' => $dataOk['description'], 'title' => $dataOk['title']]);
    }

    /**
     * @test
     */
    public function test_authorized_user_can_destroy_badge(){
        $badge = Badge::factory()->create(['title' => 'test']);

        $this->actingAsUser();
        $this->deleteJson(route('badge.destroy', $badge->id))->assertForbidden();
        $this->assertModelExists($badge);
        $this->assertDatabaseHas(Badge::class, ['title' => 'test']);

        $this->actingAsAdmin();
        $this->deleteJson(route('badge.destroy', $badge->id))->assertOk();
        $this->assertModelMissing($badge);
        $this->assertDatabaseMissing(Badge::class, ['title' => 'test']);
    }

}
