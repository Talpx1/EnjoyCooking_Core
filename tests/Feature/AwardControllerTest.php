<?php

namespace Tests\Feature;

use App\Models\Award;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Testing\MimeType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;
use Intervention\Image\Facades\Image;
use Tests\Feature\Seeders\PermissionsAndRolesSeeder;
use Tests\TestCase;

class AwardControllerTest extends TestCase
{
    use RefreshDatabase;


    protected $seed = true;
    protected $seeder = PermissionsAndRolesSeeder::class;

    /**
     * @test
     */
    public function test_everyone_can_index_awards(){
        $awards = Award::factory(40)->create();
        $this->getJson(route('award.index'))->assertOk()->assertJson(function(AssertableJson $json) use ($awards){
            $json->has('data')->etc();
            $awards->splice(0,15)->each(fn($award) => $this->assertTrue(collect($json->toArray()['data'])->pluck('id')->contains($award->id)));
        });
    }

    public function test_award_index_gets_paginated(){
        $awards = Award::factory(40)->create();

        $this->getJson(route('award.index'))->assertOk()->assertJson(function(AssertableJson $json) use ($awards){
            $json->hasAll(['data', 'current_page', "next_page_url", "path","per_page", "prev_page_url", "to", "total", "first_page_url", "from", "last_page", "last_page_url", "links"])->etc();
            $awards->chunk(15)[0]->each(fn($award) => $this->assertTrue(collect($json->toArray()['data'])->pluck('id')->contains($award->id)));
            $awards->chunk(15)[1]->each(fn($award) => $this->assertFalse(collect($json->toArray()['data'])->pluck('id')->contains($award->id)));
        });

        $this->getJson(route('award.index',['page'=>2]))->assertOk()->assertJson(function(AssertableJson $json) use ($awards){
            $json->hasAll(['data', 'current_page', "next_page_url", "path","per_page", "prev_page_url", "to", "total", "first_page_url", "from", "last_page", "last_page_url", "links"])->etc();
            $awards->chunk(15)[0]->each(fn($award) => $this->assertFalse(collect($json->toArray()['data'])->pluck('id')->contains($award->id)));
            $awards->chunk(15)[1]->each(fn($award) => $this->assertTrue(collect($json->toArray()['data'])->pluck('id')->contains($award->id)));
        })->assertJsonFragment(['current_page'=>2]);
    }

    public function test_authorized_user_can_store_award(){
        Storage::fake('public');
        $this->actingAsAdmin();

        $award = Award::factory()->raw(['icon' => UploadedFile::fake()->image('test.png')->size(1000)->mimeType(MimeType::get('png'))]);
        unset($award['icon_path']);

        $this->postJson(route('award.store'), $award)->assertCreated()->assertJsonFragment(['name' => $award['name'], 'price' => $award['price']]);

        $this->actingAsUser();

        $award = Award::factory()->raw(['icon' => UploadedFile::fake()->image('test.png')->size(1000)->mimeType(MimeType::get('png'))]);
        unset($award['icon_path']);

        $this->postJson(route('award.store'), $award)->assertForbidden();
    }

    public function test_award_icon_gets_saved_on_store(){
        Storage::fake('public');
        $this->actingAsAdmin();

        $award = Award::factory()->raw(['icon' => UploadedFile::fake()->image('test.png')->size(1000)->mimeType(MimeType::get('png'))]);
        unset($award['icon_path']);

        $this->postJson(route('award.store'), $award)->assertCreated()->assertJsonFragment(['name' => $award['name'], 'price' => $award['price']]);

        $award = Award::latest()->first();

        $this->assertNotNull($award->icon_path);
        foreach(explode(',', config('upload.award.save_as')) as $format) Storage::disk('public')->assertExists($award->icon_path.".{$format}");
    }

    public function test_award_icon_gets_resized_on_store(){
        Storage::fake('public');
        $this->actingAsAdmin();

        //width and height get resized equally
        $award = Award::factory()->raw(['icon' => UploadedFile::fake()->image('test.png', config('upload.award.save_width')+5, config('upload.award.save_height')+5)->size(1000)->mimeType(MimeType::get('png'))]);
        unset($award['icon_path']);

        $this->postJson(route('award.store'), $award)->assertCreated()->assertJsonFragment(['name' => $award['name'], 'price' => $award['price']]);

        $award = Award::latest()->first();
        $this->assertNotNull($award->icon_path);

        foreach(explode(',', config('upload.award.save_as')) as $format){
            $this->assertTrue(Image::make(Storage::disk('public')->get($award->icon_path.".{$format}"))->width() == config('upload.award.save_width'));
            $this->assertTrue(Image::make(Storage::disk('public')->get($award->icon_path.".{$format}"))->height() == config('upload.award.save_height'));
        }

        //TODO: test with other sizing
    }

    public function test_award_icon_gets_saved_in_multiple_formats_on_store(){
        Storage::fake('public');
        $this->actingAsAdmin();

        //editing config value
        Config::set('upload.award.save_as', 'png,jpeg,webp');

        $award = Award::factory()->raw(['icon' => UploadedFile::fake()->image('test.png', config('upload.award.save_width'), config('upload.award.save_height'))->size(1000)->mimeType(MimeType::get('png'))]);
        unset($award['icon_path']);

        $this->postJson(route('award.store'), $award)->assertCreated()->assertJsonFragment(['name' => $award['name'], 'price' => $award['price']]);

        $award = Award::latest()->first();
        $this->assertNotNull($award->icon_path);

        foreach(explode(',', config('upload.award.save_as')) as $format){
            $this->assertTrue(Image::make(Storage::disk('public')->get($award->icon_path.".{$format}"))->width() == config('upload.award.save_width'));
            $this->assertTrue(Image::make(Storage::disk('public')->get($award->icon_path.".{$format}"))->height() == config('upload.award.save_height'));
        }
    }

    //TODO: test request (size, mime ...)
}
