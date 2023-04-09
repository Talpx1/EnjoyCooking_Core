<?php

namespace Tests\Feature;

use App\Models\Gender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\Seeders\PermissionsAndRolesSeeder;
use Tests\TestCase;

class GenderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;
    protected $seeder = PermissionsAndRolesSeeder::class;

    /**
     * @test
     */
    public function test_everyone_can_index_genders(){
        $genders = Gender::factory(40)->create();

        $this->simulateAllowedOrigin();
        $this->getJson(route('gender.index'))->assertOk()->assertJson(function(AssertableJson $json) use ($genders){
            $json->has('data')->etc();
            $genders->splice(0,15)->each(fn($gender) => $this->assertTrue(collect($json->toArray()['data'])->pluck('id')->contains($gender->id)));
        });
    }

    /**
     * @test
     */
    public function test_gender_index_gets_paginated(){
        $genders = Gender::factory(40)->create();

        $this->simulateAllowedOrigin();
        $this->getJson(route('gender.index'))->assertOk()->assertJson(function(AssertableJson $json) use ($genders){
            $json->hasAll(['data', 'current_page', "next_page_url", "path","per_page", "prev_page_url", "to", "total", "first_page_url", "from", "last_page", "last_page_url", "links"])->etc();
            $genders->chunk(15)[0]->each(fn($gender) => $this->assertTrue(collect($json->toArray()['data'])->pluck('id')->contains($gender->id)));
            $genders->chunk(15)[1]->each(fn($gender) => $this->assertFalse(collect($json->toArray()['data'])->pluck('id')->contains($gender->id)));
        });

        $this->getJson(route('gender.index',['page'=>2]))->assertOk()->assertJson(function(AssertableJson $json) use ($genders){
            $json->hasAll(['data', 'current_page', "next_page_url", "path","per_page", "prev_page_url", "to", "total", "first_page_url", "from", "last_page", "last_page_url", "links"])->etc();
            $genders->chunk(15)[0]->each(fn($gender) => $this->assertFalse(collect($json->toArray()['data'])->pluck('id')->contains($gender->id)));
            $genders->chunk(15)[1]->each(fn($gender) => $this->assertTrue(collect($json->toArray()['data'])->pluck('id')->contains($gender->id)));
        })->assertJsonFragment(['current_page'=>2]);
    }

    /**
     * @test
     */
    public function test_authorized_user_can_store_gender(){
        $this->actingAsAdmin();

        $gender = Gender::factory()->raw();

        $this->postJson(route('gender.store'), $gender)->assertCreated()->assertJsonFragment(['name' => $gender['name']]);

        $this->actingAsUser();

        $gender = Gender::factory()->raw();

        $this->postJson(route('gender.store'), $gender)->assertForbidden();
    }

    /**
     * @test
     */
    public function test_gender_name_is_required_on_store(){

        $this->actingAsAdmin();

        $gender = Gender::factory()->raw();

        unset($gender['name']);

        $this->postJson(route('gender.store'), $gender)->assertJsonValidationErrorFor('name');
        $this->assertDatabaseMissing(Gender::class, ['name' => null]);
    }

    /**
     * @test
     */
    public function test_gender_name_must_be_string_on_store(){

        $this->actingAsAdmin();

        $gender = Gender::factory()->raw(['name' => 123]);


        $this->postJson(route('gender.store'), $gender)->assertJsonValidationErrorFor('name');
        $this->assertDatabaseMissing(Gender::class, ['name' => 123]);
    }

    /**
     * @test
     */
    public function test_name_must_be_max_255_chars_on_store(){
        $this->actingAsAdmin();
        $nameErr = Str::random(256);
        $nameOk = Str::random(255);

        $gender = Gender::factory()->raw(['name' => $nameErr]);

        $this->postJson(route('gender.store'), $gender)->assertJsonValidationErrorFor('name');
        $this->assertDatabaseMissing(Gender::class, ['name' => $nameErr]);

        $gender = Gender::factory()->raw(['name' => $nameOk]);

        $this->postJson(route('gender.store'), $gender)->assertCreated();
        $this->assertDatabaseHas(Gender::class, ['name' => $nameOk]);
    }

    /**
     * @test
     */
    public function test_gender_name_must_be_unique_on_store(){
        $this->actingAsAdmin();

        $gender = Gender::factory()->raw(['name' => 'test']);

        $this->postJson(route('gender.store'), $gender)->assertCreated()->assertJsonFragment(['name' => $gender['name']]);
        $this->assertDatabaseHas(Gender::class, ['name' => $gender['name']]);

        $this->postJson(route('gender.store'), $gender)->assertJsonValidationErrorFor('name');
    }

    /**
     * @test
     */
    public function test_only_admin_can_show_gender(){
        $gender = Gender::factory()->create();
        $this->simulateAllowedOrigin();

        $this->getJson(route('gender.show', $gender->id))->assertUnauthorized();

        $this->actingAsApiUser();
        $this->getJson(route('gender.show', $gender->id))->assertForbidden();

        $this->actingAsApiModerator();
        $this->getJson(route('gender.show', $gender->id))->assertForbidden();

        $this->actingAsApiAdmin();
        $this->getJson(route('gender.show', $gender->id))->assertOk()->assertJson($gender->toArray());
    }

    /**
     * @test
     */
    public function test_authorized_user_can_update_gender(){
        $this->actingAsAdmin();

        $gender = Gender::factory()->create();

        $this->putJson(route('gender.update', $gender->id), ['name' => 'test'])->assertOk()->assertJsonFragment(['name' => 'test']);
        $this->assertDatabaseHas(Gender::class, ['name' => 'test']);

        $this->actingAsUser();

        $this->putJson(route('gender.update', $gender->id), ['name' => 'test2'])->assertForbidden();
        $this->assertDatabaseMissing(Gender::class, ['name' => 'test2']);
    }

    /**
     * @test
     */
    public function test_gender_name_is_required_on_update(){

        $this->actingAsAdmin();

        $gender = Gender::factory()->create();
        $data = Gender::factory()->raw();
        unset($data['name']);

        $this->putJson(route('gender.update', $gender->id), $data)->assertJsonValidationErrorFor('name');
        $this->assertDatabaseMissing(Gender::class, ['name' => null]);
    }

    /**
     * @test
     */
    public function test_name_must_be_max_255_chars_on_update(){
        $this->actingAsAdmin();

        $dataErr = Gender::factory()->raw(['name' => Str::random(256)]);
        $dataOk = Gender::factory()->raw(['name' => Str::random(255)]);

        $gender = Gender::factory()->create();

        $this->putJson(route('gender.update', $gender->id), $dataErr)->assertJsonValidationErrorFor('name');
        $this->assertDatabaseMissing(Gender::class, ['name' => $dataErr['name']]);

        $this->putJson(route('gender.update', $gender->id), $dataOk)->assertOk();
        $this->assertDatabaseHas(Gender::class, ['name' => $dataOk['name']]);
    }

    /**
     * @test
     */
    public function test_gender_name_must_be_string_on_update(){
        $this->actingAsAdmin();

        $gender = Gender::factory()->create();

        $this->putJson(route('gender.update', $gender->id), array_merge($gender->toArray(),['name'=>123]))->assertJsonValidationErrorFor('name');
        $this->assertDatabaseMissing(Gender::class, ['name' => 123]);
    }

    /**
     * @test
     */
    public function test_gender_name_must_be_unique_on_update(){
        $this->actingAsAdmin();

        Gender::factory()->create(['name'=>'test']);
        $gender = Gender::factory()->create();

        $this->putJson(route('gender.update', $gender->id), array_merge($gender->toArray(),['name'=>'test']))->assertJsonValidationErrorFor('name');

        $this->putJson(route('gender.update', $gender->id), array_merge($gender->toArray(),['name'=>'test2']))->assertOk()->assertJsonFragment(['name' => 'test2']);
        $this->assertDatabaseHas(Gender::class, ['name' => 'test2']);
    }

    /**
     * @test
     */
    public function test_authorized_user_can_destroy_gender(){
        // Defining users before creating the first gender because otherwise
        // they would create a foreign key to the newly created gender and
        // an SQL exception would be thrown on delete.
        // By doing it this way a the foreign key is created with a random
        // generated Gender and it's possible to test Gender deletion
        $admin = $this->actingAsAdmin();
        $this->actingAsUser();

        $gender = Gender::factory()->create(['name' => 'test']);

        $this->deleteJson(route('gender.destroy', $gender->id))->assertForbidden();
        $this->assertModelExists($gender);
        $this->assertDatabaseHas(Gender::class, ['name' => 'test']);

        $this->actingAs($admin);
        $this->deleteJson(route('gender.destroy', $gender->id))->assertOk();
        $this->assertModelMissing($gender);
        $this->assertDatabaseMissing(Gender::class, ['name' => 'test']);
    }
}
