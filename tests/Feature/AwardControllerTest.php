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
use Tests\Seeders\PermissionsAndRolesSeeder;
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

        $this->simulateAllowedOrigin();
        $this->getJson(route('award.index'))->assertOk()->assertJson(function(AssertableJson $json) use ($awards){
            $json->has('data')->etc();
            $awards->splice(0,15)->each(fn($award) => $this->assertTrue(collect($json->toArray()['data'])->pluck('id')->contains($award->id)));
        });
    }

    /**
     * @test
     */
    public function test_award_index_gets_paginated(){
        $awards = Award::factory(40)->create();

        $this->simulateAllowedOrigin();
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

    /**
     * @test
     */
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

    /**
     * @test
     */
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

    /**
     * @test
     */
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

    /**
     * @test
     */
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

        Storage::disk('public')->assertExists($award->icon_path.".jpeg");
        Storage::disk('public')->assertExists($award->icon_path.".jpeg");
        Storage::disk('public')->assertExists($award->icon_path.".png");
        Storage::disk('public')->assertExists($award->icon_path.".png");
        Storage::disk('public')->assertExists($award->icon_path.".webp");
        Storage::disk('public')->assertExists($award->icon_path.".webp");
    }

    /**
     * @test
     */
    public function test_award_name_is_required_on_store(){
        Storage::fake('public');
        $this->actingAsAdmin();

        $award = Award::factory()->raw(['icon' => UploadedFile::fake()->image('test.png')]);
        unset($award['icon_path']);
        unset($award['name']);

        $this->postJson(route('award.store'), $award)->assertJsonValidationErrorFor('name');
        $this->assertDatabaseMissing(Award::class, ['name' => null, 'price' => $award['price']]);
    }

    /**
     * @test
     */
    public function test_award_name_must_be_string_on_store(){
        Storage::fake('public');
        $this->actingAsAdmin();

        $award = Award::factory()->raw(['name' => 123, 'icon' => UploadedFile::fake()->image('test.png')]);
        unset($award['icon_path']);

        $this->postJson(route('award.store'), $award)->assertJsonValidationErrorFor('name');
        $this->assertDatabaseMissing(Award::class, ['name' => 123, 'price' => $award['price']]);
    }

    /**
     * @test
     */
    public function test_award_name_must_be_unique_on_store(){
        Storage::fake('public');
        $this->actingAsAdmin();

        $award = Award::factory()->raw(['name' => 'test', 'icon' => UploadedFile::fake()->image('test.png')]);
        unset($award['icon_path']);

        $this->postJson(route('award.store'), $award)->assertCreated()->assertJsonFragment(['name' => $award['name'], 'price' => $award['price']]);
        $this->assertDatabaseHas(Award::class, ['name' => $award['name'], 'price' => $award['price']]);

        $this->postJson(route('award.store'), $award)->assertJsonValidationErrorFor('name');
        $this->assertDatabaseCount(Award::class, 1);
    }

    /**
     * @test
     */
    public function test_award_icon_is_required_on_store(){
        $this->actingAsAdmin();

        $award = Award::factory()->raw();
        unset($award['icon_path']);

        $this->postJson(route('award.store'), $award)->assertJsonValidationErrorFor('icon');
        $this->assertDatabaseMissing(Award::class, ['icon_path' => null, 'name' => $award['name']]);
    }

    /**
     * @test
     */
    public function test_award_icon_must_be_image_on_store(){
        Storage::fake('public');
        $this->actingAsAdmin();

        $award = Award::factory()->raw(['icon' => UploadedFile::fake()->create('test.pdf')]);
        unset($award['icon_path']);

        $this->postJson(route('award.store'), $award)->assertJsonValidationErrorFor('icon');
        $this->assertDatabaseMissing(Award::class, ['icon_path' => null, 'price' => $award['price']]);

        $award = Award::factory()->raw(['icon' => UploadedFile::fake()->image('test.png')]);
        unset($award['icon_path']);

        $this->postJson(route('award.store'), $award)->assertCreated()->assertJsonFragment(['name' => $award['name'], 'price' => $award['price']]);
        $this->assertDatabaseHas(Award::class, ['name' => $award['name'], 'price' => $award['price']]);
    }

    /**
     * @test
     */
    public function test_award_icon_must_have_valid_mime_type_on_store(){
        Storage::fake('public');
        $this->actingAsAdmin();

        Config::set('upload.award.accepted_file_types', 'png,jpg');

        $award = Award::factory()->raw(['icon' => UploadedFile::fake()->image('test.png')->mimeType(MimeType::get('webp'))]);
        unset($award['icon_path']);

        $this->postJson(route('award.store'), $award)->assertJsonValidationErrorFor('icon');
        $this->assertDatabaseMissing(Award::class, ['icon_path' => null, 'name' => $award['name']]);

        $award = Award::factory()->raw(['icon' => UploadedFile::fake()->image('test.png')->mimeType(MimeType::get('png'))]);
        unset($award['icon_path']);

        $this->postJson(route('award.store'), $award)->assertCreated()->assertJsonFragment(['name' => $award['name'], 'price' => $award['price']]);
        $this->assertDatabaseHas(Award::class, ['name' => $award['name'], 'price' => $award['price']]);
    }

    /**
     * @test
     */
    public function test_award_icon_must_have_valid_file_size_on_store(){
        Storage::fake('public');
        $this->actingAsAdmin();

        Config::set('upload.award.accepted_file_types', 'png,jpg');

        $award = Award::factory()->raw(['icon' => UploadedFile::fake()->image('test.png')->size(2048)]);
        unset($award['icon_path']);

        $this->postJson(route('award.store'), $award)->assertJsonValidationErrorFor('icon');
        $this->assertDatabaseMissing(Award::class, ['icon_path' => null, 'name' => $award['name']]);

        $award = Award::factory()->raw(['icon' => UploadedFile::fake()->image('test.png')->size(1024)]);
        unset($award['icon_path']);

        $this->postJson(route('award.store'), $award)->assertCreated()->assertJsonFragment(['name' => $award['name'], 'price' => $award['price']]);
        $this->assertDatabaseHas(Award::class, ['name' => $award['name'], 'price' => $award['price']]);
    }

    /**
     * @test
     */
    public function test_award_price_is_required_on_store(){
        Storage::fake('public');
        $this->actingAsAdmin();

        $award = Award::factory()->raw(['icon' => UploadedFile::fake()->image('test.png'), 'price' => null]);
        unset($award['icon_path']);

        $this->postJson(route('award.store'), $award)->assertJsonValidationErrorFor('price');
        $this->assertDatabaseMissing(Award::class, ['name' => $award['name'], 'price' => null]);
    }

    /**
     * @test
     */
    public function test_award_price_must_be_numeric_on_store(){
        Storage::fake('public');
        $this->actingAsAdmin();

        $award = Award::factory()->raw(['price' => 'aaa', 'icon' => UploadedFile::fake()->image('test.png')]);
        unset($award['icon_path']);

        $this->postJson(route('award.store'), $award)->assertJsonValidationErrorFor('price');
        $this->assertDatabaseMissing(Award::class, ['name' => $award['name'], 'price' => 'aaa']);

        $award = Award::factory()->raw(['price' => 111, 'icon' => UploadedFile::fake()->image('test.png')]);
        unset($award['icon_path']);

        $this->postJson(route('award.store'), $award)->assertCreated()->assertJsonFragment(['name' => $award['name'], 'price' => 111]);
        $this->assertDatabaseHas(Award::class, ['name' => $award['name'], 'price' => 111]);
    }

    /**
     * @test
     */
    public function test_award_price_minimum_value_is_0_on_store(){
        Storage::fake('public');
        $this->actingAsAdmin();

        $award = Award::factory()->raw(['price' => -1, 'icon' => UploadedFile::fake()->image('test.png')]);
        unset($award['icon_path']);

        $this->postJson(route('award.store'), $award)->assertJsonValidationErrorFor('price');
        $this->assertDatabaseMissing(Award::class, ['name' => $award['name'], 'price' => -1]);

        $award = Award::factory()->raw(['price' => 0, 'icon' => UploadedFile::fake()->image('test.png')]);
        unset($award['icon_path']);

        $this->postJson(route('award.store'), $award)->assertCreated()->assertJsonFragment(['name' => $award['name'], 'price' => 0]);
        $this->assertDatabaseHas(Award::class, ['name' => $award['name'], 'price' => 0]);
    }

    /**
     * @test
     */
    public function test_everyone_can_show_award(){
        $award = Award::factory()->create();

        $this->simulateAllowedOrigin();
        $this->getJson(route('award.show', $award->id))->assertOk()->assertJson($award->toArray());
    }

    /**
     * @test
     */
    public function test_authorized_user_can_update_award(){
        $this->actingAsAdmin();

        $award = Award::factory()->create();

        $this->putJson(route('award.update', $award->id), ['name' => 'test', 'price'=>1])->assertOk()->assertJsonFragment(['name' => 'test', 'price'=>1]);
        $this->assertDatabaseHas(Award::class, ['name' => 'test']);

        $this->actingAsUser();

        $this->putJson(route('award.update', $award->id), ['name' => 'test2', 'price'=>2])->assertForbidden();
        $this->assertDatabaseMissing(Award::class, ['name' => 'test2']);
    }


    /**
     * @test
     */
    public function test_award_icon_gets_saved_on_update(){
        Storage::fake('public');
        $this->actingAsAdmin();

        $award = Award::factory()->create();
        $old_icon_path = $award->icon_path;

        $this->putJson(route('award.update', $award->id), [
            'icon' => UploadedFile::fake()->image('test.png')->size(1000)->mimeType(MimeType::get('png')),
            'name' => 'test',
            'price'=>1
        ])->assertOk()->assertJsonFragment(['name' => 'test']);

        $this->assertNotEquals($award->fresh()->icon_path, $old_icon_path);
        $this->assertNotNull($award->fresh()->icon_path);
        foreach(explode(',', config('upload.award.save_as')) as $format) Storage::disk('public')->assertExists($award->fresh()->icon_path.".{$format}");
    }

    /**
     * @test
     */
    public function test_award_icons_get_deleted_when_new_one_is_uploaded(){
        Storage::fake('public');
        Config::set('upload.award.save_as', 'jpeg,png,webp');
        $this->actingAsAdmin();

        $award = Award::factory()->raw(['name'=>'test', 'icon' => UploadedFile::fake()->image('test.png')->size(1000)->mimeType(MimeType::get('png'))]);
        unset($award['icon_path']);

        $this->postJson(route('award.store'), $award)->assertCreated()->assertJsonFragment(['name' => $award['name'], 'price' => $award['price']]);

        $award = Award::latest()->first();

        $this->assertNotNull($award->icon_path);
        Storage::disk('public')->assertExists($award->icon_path.".jpeg");
        Storage::disk('public')->assertExists($award->icon_path.".png");
        Storage::disk('public')->assertExists($award->icon_path.".webp");

        $old_icon_path = $award->icon_path;

        $this->putJson(route('award.update', $award->id), array_merge($award->toArray(), [
            'icon' => UploadedFile::fake()->image('test.png')->size(1000)->mimeType(MimeType::get('png')),
        ]))->assertOk()->assertJsonFragment(['name' => 'test']);

        $this->assertNotEquals($award->fresh()->icon_path, $old_icon_path);
        $this->assertNotNull($award->fresh()->icon_path);

        Storage::disk('public')->assertExists($award->fresh()->icon_path.".jpeg");
        Storage::disk('public')->assertExists($award->fresh()->icon_path.".png");
        Storage::disk('public')->assertExists($award->fresh()->icon_path.".webp");

        Storage::disk('public')->assertMissing($old_icon_path.".jpeg");
        Storage::disk('public')->assertMissing($old_icon_path.".png");
        Storage::disk('public')->assertMissing($old_icon_path.".webp");
    }

    /**
     * @test
     */
    public function test_award_icon_gets_resized_on_update(){
        Storage::fake('public');
        Config::set('upload.award.save_as', 'jpeg,png,webp');
        $this->actingAsAdmin();

        //width and height get resized equally
        $award = Award::factory()->create(['name' => 'test']);
        unset($award['icon_path']);

        $this->putJson(route('award.update', $award->id), array_merge($award->toArray(),[
            'icon' => UploadedFile::fake()->image('test.png', config('upload.award.save_width')+5, config('upload.award.save_height')+5)->size(1000)->mimeType(MimeType::get('png'))
        ]))->assertOk()->assertJsonFragment(['name' => 'test']);

        $this->assertNotNull($award->fresh()->icon_path);

        $this->assertTrue(Image::make(Storage::disk('public')->get($award->fresh()->icon_path.".jpeg"))->width() == config('upload.award.save_width'));
        $this->assertTrue(Image::make(Storage::disk('public')->get($award->fresh()->icon_path.".jpeg"))->height() == config('upload.award.save_height'));
        $this->assertTrue(Image::make(Storage::disk('public')->get($award->fresh()->icon_path.".png"))->width() == config('upload.award.save_width'));
        $this->assertTrue(Image::make(Storage::disk('public')->get($award->fresh()->icon_path.".png"))->height() == config('upload.award.save_height'));
        $this->assertTrue(Image::make(Storage::disk('public')->get($award->fresh()->icon_path.".webp"))->width() == config('upload.award.save_width'));
        $this->assertTrue(Image::make(Storage::disk('public')->get($award->fresh()->icon_path.".webp"))->height() == config('upload.award.save_height'));

        //TODO: test with other sizing
    }

    /**
     * @test
     */
    public function test_award_icon_gets_saved_in_multiple_formats_on_update(){
        Storage::fake('public');
        $this->actingAsAdmin();

        //editing config value
        Config::set('upload.award.save_as', 'png,jpeg,webp');

        $award = Award::factory()->create(['name' => 'test']);

        $this->putJson(route('award.update', $award->id), array_merge($award->toArray(),[
            'icon' => UploadedFile::fake()->image('test.png', config('upload.award.save_width'), config('upload.award.save_height'))->size(1000)->mimeType(MimeType::get('png'))
        ]))->assertOk()->assertJsonFragment(['name' => 'test']);

        $this->assertNotNull($award->fresh()->icon_path);

        Storage::disk('public')->assertExists($award->fresh()->icon_path.".jpeg");
        Storage::disk('public')->assertExists($award->fresh()->icon_path.".png");
        Storage::disk('public')->assertExists($award->fresh()->icon_path.".webp");
    }

    /**
     * @test
     */
    public function test_award_name_is_required_on_update(){
        Storage::fake('public');
        $this->actingAsAdmin();

        $award = Award::factory()->create();
        $data = Award::factory()->raw(['icon' => UploadedFile::fake()->image('test.png')]);
        unset($data['icon_path']);
        unset($data['name']);

        $this->putJson(route('award.update', $award->id), $data)->assertJsonValidationErrorFor('name');
        $this->assertDatabaseMissing(Award::class, ['name' => null, 'price' => $data['price']]);
    }

    /**
     * @test
     */
    public function test_award_name_must_be_string_on_update(){
        $this->actingAsAdmin();

        $award = Award::factory()->create();

        $this->putJson(route('award.update', $award->id), array_merge($award->toArray(),['name'=>123]))->assertJsonValidationErrorFor('name');
        $this->assertDatabaseMissing(Award::class, ['name' => 123, 'price' => $award->price]);
    }

    /**
     * @test
     */
    public function test_award_name_must_be_unique_on_update(){
        $this->actingAsAdmin();

        Award::factory()->create(['name'=>'test']);
        $award = Award::factory()->create();

        $this->putJson(route('award.update', $award->id), array_merge($award->toArray(),['name'=>'test']))->assertJsonValidationErrorFor('name');

        $this->putJson(route('award.update', $award->id), array_merge($award->toArray(),['name'=>'test2']))->assertOk()->assertJsonFragment(['name' => 'test2', 'price' => $award->price]);
        $this->assertDatabaseHas(Award::class, ['name' => 'test2', 'price' => $award->price]);
    }

    /**
     * @test
     */
    public function test_award_icon_is_nullable_on_update(){
        $this->actingAsAdmin();

        $award = Award::factory()->create(['name'=>'test', 'icon_path'=>'test/123/']);

        $this->putJson(route('award.update', $award->id), array_merge($award->toArray(), ['name'=>'test123', 'icon' => null]))->assertOk()->assertJsonFragment(['name' => 'test123', 'price' => $award->price, 'icon_path' => 'test/123/']);
        $this->assertDatabaseHas(Award::class, ['name' => 'test123', 'price' => $award->price, 'icon_path' => 'test/123/']);
        $this->assertEquals($award->icon_path, 'test/123/');
    }

    /**
     * @test
     */
    public function test_award_icon_must_be_image_on_update(){
        Storage::fake('public');
        $this->actingAsAdmin();

        $award = Award::factory()->create(['name'=>'test', 'icon_path'=>'test/123/']);

        $this->putJson(route('award.update', $award->id), array_merge($award->toArray(),[
            'icon' => UploadedFile::fake()->create('test.pdf')
        ]))->assertJsonValidationErrorFor('icon');
        $this->assertDatabaseMissing(Award::class, ['icon_path' => null, 'name' => $award->name]);

        $this->putJson(route('award.update', $award->id), array_merge($award->toArray(),[
            'icon' => UploadedFile::fake()->image('test.png')
        ]))->assertOk()->assertJsonFragment(['name' => $award->name, 'price' => $award->price]);

        $this->assertDatabaseHas(Award::class, ['name' => $award->fresh()->name, 'price' => $award->fresh()->price]);

        $this->assertNotEquals($award->fresh()->icon_path, 'test/123/');
    }

    /**
     * @test
     */
    public function test_award_icon_must_have_valid_mime_type_on_update(){
        Storage::fake('public');
        $this->actingAsAdmin();

        Config::set('upload.award.accepted_file_types', 'png,jpg');
        $award = Award::factory()->create(['name'=>'test', 'icon_path'=>'test/123/']);

        $this->putJson(route('award.update', $award->id), array_merge($award->toArray(),[
            'icon' => UploadedFile::fake()->image('test.png')->mimeType(MimeType::get('webp'))
        ]))->assertJsonValidationErrorFor('icon');
        $this->assertDatabaseMissing(Award::class, ['icon_path' => null, 'name' => $award->name]);

        $this->putJson(route('award.update', $award->id), array_merge($award->toArray(),[
            'icon' => UploadedFile::fake()->image('test.png')->mimeType(MimeType::get('png'))
        ]))->assertOk()->assertJsonFragment(['name' => $award->fresh()->name, 'price' => $award->fresh()->price]);

        $this->assertDatabaseHas(Award::class, ['name' => $award->fresh()->name, 'price' => $award->fresh()->price]);

        $this->assertNotEquals($award->fresh()->icon_path, 'test/123/');
    }

    /**
     * @test
     */
    public function test_award_icon_must_have_valid_file_size_on_update(){
        Storage::fake('public');
        $this->actingAsAdmin();

        $award = Award::factory()->create(['name'=>'test', 'icon_path'=>'test/123/']);

        $this->putJson(route('award.update', $award->id), array_merge($award->toArray(),[
            'icon' => UploadedFile::fake()->image('test.png')->size(2048)
        ]))->assertJsonValidationErrorFor('icon');

        $this->assertDatabaseMissing(Award::class, ['icon_path' => null, 'name' => $award->name]);

        $this->putJson(route('award.update', $award->id), array_merge($award->toArray(),[
            'icon' => UploadedFile::fake()->image('test.png')->size(1024)
        ]))->assertOk()->assertJsonFragment(['name' => $award->fresh()->name, 'price' => $award->fresh()->price]);

        $this->assertDatabaseHas(Award::class, ['name' => $award->fresh()->name, 'price' => $award->fresh()->price]);
    }

    /**
     * @test
     */
    public function test_award_price_is_required_on_update(){
        $this->actingAsAdmin();

        $award = Award::factory()->create(['name'=>'test']);

        $this->putJson(route('award.update', $award->id), array_merge($award->toArray(),['price' => null]))
            ->assertJsonValidationErrorFor('price');
        $this->assertDatabaseMissing(Award::class, ['name' => 'test', 'price' => null]);
    }

    /**
     * @test
     */
    public function test_award_price_must_be_numeric_on_update(){
        $this->actingAsAdmin();

        $award = Award::factory()->create(['name'=>'test']);

        $this->putJson(route('award.update', $award->id), array_merge($award->toArray(),['price' => 'aaa']))->assertJsonValidationErrorFor('price');
        $this->assertDatabaseMissing(Award::class, ['name' => $award->name, 'price' => 'aaa']);

        $this->putJson(route('award.update', $award->id), array_merge($award->toArray(),['price' => 111]))->assertOk()->assertJsonFragment(['name' => $award->name, 'price' => 111]);
        $this->assertDatabaseHas(Award::class, ['name' => $award->name, 'price' => 111]);
    }

    /**
     * @test
     */
    public function test_award_price_minimum_value_is_0_on_update(){
        $this->actingAsAdmin();

        $award = Award::factory()->create(['name'=>'test']);

        $this->putJson(route('award.update', $award->id), array_merge($award->toArray(),['price' => -1]))->assertJsonValidationErrorFor('price');
        $this->assertDatabaseMissing(Award::class, ['name' => $award->name, 'price' => -1]);

        $this->putJson(route('award.update', $award->id), array_merge($award->toArray(),['price' => 0]))->assertOk()->assertJsonFragment(['name' => $award->name, 'price' => 0]);
        $this->assertDatabaseHas(Award::class, ['name' => $award->name, 'price' => 0]);
    }

    public function test_authorized_user_can_destroy_award(){
        $award = Award::factory()->create(['name' => 'test']);

        $this->actingAsUser();
        $this->deleteJson(route('award.destroy', $award->id))->assertForbidden();
        $this->assertModelExists($award);
        $this->assertDatabaseHas(Award::class, ['name' => 'test']);

        $this->actingAsAdmin();
        $this->deleteJson(route('award.destroy', $award->id))->assertOk();
        $this->assertModelMissing($award);
        $this->assertDatabaseMissing(Award::class, ['name' => 'test']);
    }

    public function test_award_icon_files_get_deleted_when_award_is_deleted(){
        $this->actingAsAdmin();
        Storage::fake('public');
        Config::set('upload.award.save_as', 'png,jpeg,webp');

        $this->postJson(route('award.store'), Award::factory()->raw(['name' => 'test', 'icon' => UploadedFile::fake()->image('test.png')]))->assertCreated()->assertJsonFragment(['name'=>'test']);
        $award = Award::latest()->first();

        $this->assertNotNull($award->icon_path);

        Storage::disk('public')->assertExists($award->icon_path.".jpeg");
        Storage::disk('public')->assertExists($award->icon_path.".png");
        Storage::disk('public')->assertExists($award->icon_path.".webp");

        $this->deleteJson(route('award.destroy', $award->id))->assertOk();
        $this->assertModelMissing($award);
        $this->assertDatabaseMissing(Award::class, ['name' => 'test']);

        Storage::disk('public')->assertMissing($award->icon_path.".jpeg");
        Storage::disk('public')->assertMissing($award->icon_path.".png");
        Storage::disk('public')->assertMissing($award->icon_path.".webp");
    }

}
