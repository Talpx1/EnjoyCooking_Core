<?php

namespace Tests\Unit;

use App\Models\Award;
use App\Models\Awardable;
use App\Models\Comment;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\Seeders\PermissionsAndRolesSeeder;
use Tests\TestCase;

class AwardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function test_name_is_required(){
        $this->expectException(QueryException::class);
        Award::factory()->create(['name'=>null]);
    }

    /**
     * @test
     */
    public function test_name_must_be_unique(){
        Award::factory()->create(['name'=>'test']);
        $this->expectException(QueryException::class);
        Award::factory()->create(['name'=>'test']);
    }

    /**
     * @test
     */
    public function test_icon_path_is_required(){
        $this->expectException(QueryException::class);
        Award::factory()->create(['icon_path'=>null]);
    }

    /**
     * @test
     */
    public function test_icon_path_must_be_unique(){
        Award::factory()->create(['icon_path'=>'test']);
        $this->expectException(QueryException::class);
        Award::factory()->create(['icon_path'=>'test']);
    }

    /**
     * @test
     */
    public function test_price_is_required(){
        $this->expectException(QueryException::class);
        Award::factory()->create(['price'=>null]);
    }

    /**
     * @test
     */
    public function test_award_is_morphed_by_many_recipes(){
        $award = Award::factory()->create(['name' => 'test']);
        $recipes = Recipe::factory(3)->create()->each(fn($recipe) => Awardable::factory()->create(['award_id'=>$award->id, 'awardable_id' => $recipe->id, 'awardable_type' => $recipe::class]));
        $other_recipes = Recipe::factory(5)->create();

        $this->assertNotNull($award->recipes);
        $this->assertCount(3, $award->recipes);
        $this->assertInstanceOf(Collection::class, $award->recipes);

        $award->recipes->each(function($recipe) use ($recipes, $other_recipes){
            $this->assertTrue($recipes->contains($recipe));
            $this->assertFalse($other_recipes->contains($recipe));
        });
    }

    /**
     * @test
     */
    public function test_award_is_morphed_by_many_comments(){
        $award = Award::factory()->create(['name' => 'test']);
        $comments = Comment::factory(3)->create()->each(fn($comment) => Awardable::factory()->create(['award_id'=>$award->id, 'awardable_id' => $comment->id, 'awardable_type' => $comment::class]));
        $other_comments = Comment::factory(5)->create();

        $this->assertNotNull($award->comments);
        $this->assertCount(3, $award->comments);
        $this->assertInstanceOf(Collection::class, $award->comments);

        $award->comments->each(function($comment) use ($comments, $other_comments){
            $this->assertTrue($comments->contains($comment));
            $this->assertFalse($other_comments->contains($comment));
        });
    }


    /**
     * @test
     */
    public function test_icon_paths_attribute(){
        $this->seed(PermissionsAndRolesSeeder::class);
        $this->actingAsAdmin();

        $disk = 'public';
        Config::set('upload.award.disk', $disk);
        Storage::fake($disk);

        Config::set('upload.award.save_as', ['jpeg','png','webp']);

        $award = Award::factory()->raw(['icon' => UploadedFile::fake()->image('test.png')]);
        $this->postJson(route('award.store'), $award)->assertCreated();

        $award = Award::latest()->first();


        $this->assertNotEmpty($award->iconPaths);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $award->iconPaths);
        $this->assertCount(3, $award->iconPaths);

        $this->assertArrayHasKey('jpeg', $award->iconPaths->toArray());
        $this->assertArrayHasKey('png', $award->iconPaths->toArray());
        $this->assertArrayHasKey('webp', $award->iconPaths->toArray());

        $this->assertNotEmpty($award->iconPaths->get('jpeg'));
        $this->assertNotEmpty($award->iconPaths->get('png'));
        $this->assertNotEmpty($award->iconPaths->get('webp'));

        $this->assertStringContainsString('.jpeg',$award->iconPaths->get('jpeg'));
        $this->assertStringContainsString('.png',$award->iconPaths->get('png'));
        $this->assertStringContainsString('.webp',$award->iconPaths->get('webp'));

        $this->assertEquals($award->iconPaths->get('jpeg'), storage_path("framework/testing/disks/public{$award->icon_path}.jpeg"));
        $this->assertEquals($award->iconPaths->get('png'), storage_path("framework/testing/disks/public{$award->icon_path}.png"));
        $this->assertEquals($award->iconPaths->get('webp'), storage_path("framework/testing/disks/public{$award->icon_path}.webp"));
    }

    /**
     * @test
     */
    public function test_icon_urls_attribute(){
        $this->seed(PermissionsAndRolesSeeder::class);
        $this->actingAsAdmin();

        $disk = 'public';
        Config::set('upload.award.disk', $disk);
        Storage::fake($disk);

        Config::set('upload.award.save_as', ['jpeg','png','webp']);

        $award = Award::factory()->raw(['icon' => UploadedFile::fake()->image('test.png')]);
        $this->postJson(route('award.store'), $award)->assertCreated();

        $award = Award::latest()->first();

        $this->assertNotEmpty($award->iconUrls);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $award->iconUrls);
        $this->assertCount(3, $award->iconUrls);

        $this->assertArrayHasKey('jpeg', $award->iconUrls->toArray());
        $this->assertArrayHasKey('png', $award->iconUrls->toArray());
        $this->assertArrayHasKey('webp', $award->iconUrls->toArray());

        $this->assertNotEmpty($award->iconUrls->get('jpeg'));
        $this->assertNotEmpty($award->iconUrls->get('png'));
        $this->assertNotEmpty($award->iconUrls->get('webp'));

        $this->assertStringContainsString('.jpeg',$award->iconUrls->get('jpeg'));
        $this->assertStringContainsString('.png',$award->iconUrls->get('png'));
        $this->assertStringContainsString('.webp',$award->iconUrls->get('webp'));

        $this->assertEquals($award->iconUrls->get('jpeg'), "/storage/{$award->icon_path}.jpeg");
        $this->assertEquals($award->iconUrls->get('png'), "/storage/{$award->icon_path}.png");
        $this->assertEquals($award->iconUrls->get('webp'), "/storage/{$award->icon_path}.webp");
    }

    /**
     * @test
     */
    public function test_icon_attribute(){
        $this->seed(PermissionsAndRolesSeeder::class);
        $this->actingAsAdmin();

        $disk = 'public';
        Config::set('upload.award.disk', $disk);
        Storage::fake($disk);

        Config::set('upload.award.save_as', ['jpeg','png','webp']);

        $award = Award::factory()->raw(['icon' => UploadedFile::fake()->image('test.png')]);
        $this->postJson(route('award.store'), $award)->assertCreated();

        $award = Award::latest()->first();

        $this->assertNotEmpty($award->icons);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $award->icons);
        $this->assertCount(3, $award->icons);

        $this->assertArrayHasKey('jpeg', $award->icons->toArray());
        $this->assertArrayHasKey('png', $award->icons->toArray());
        $this->assertArrayHasKey('webp', $award->icons->toArray());

        $this->assertNotEmpty($award->icons->get('jpeg'));
        $this->assertNotEmpty($award->icons->get('png'));
        $this->assertNotEmpty($award->icons->get('webp'));

        $this->assertEquals($award->icons->get('jpeg'), base64_encode(Storage::disk($disk)->get("{$award->icon_path}.jpeg")));
        $this->assertEquals($award->icons->get('png'), base64_encode(Storage::disk($disk)->get("{$award->icon_path}.png")));
        $this->assertEquals($award->icons->get('webp'), base64_encode(Storage::disk($disk)->get("{$award->icon_path}.webp")));
    }

    public function test_store_icon(){
        $disk = 'public';
        Config::set('upload.award.disk', $disk);
        Storage::fake($disk);

        Config::set('upload.award.save_as', ['png','jpeg','webp']);

        $icon = UploadedFile::fake()->image('test.png');

        $path = Award::storeIcon($icon);

        $this->assertNotNull($path);
        $this->assertNotEmpty($path);

        Storage::disk($disk)->assertExists("$path.png");
        Storage::disk($disk)->assertExists("$path.jpeg");
        Storage::disk($disk)->assertExists("$path.webp");
    }

    public function test_delete_icon_files(){
        $this->seed(PermissionsAndRolesSeeder::class);
        $this->actingAsAdmin();
        $disk = 'public';
        Config::set('upload.award.disk', $disk);
        Storage::fake($disk);
        Config::set('upload.award.save_as', ['png','jpeg','webp']);

        $this->postJson(route('award.store'), Award::factory()->raw(['name' => 'test', 'icon' => UploadedFile::fake()->image('test.png')]))->assertCreated()->assertJsonFragment(['name'=>'test']);
        $award = Award::latest()->first();

        Storage::disk($disk)->assertExists($award->icon_path.".png");
        Storage::disk($disk)->assertExists($award->icon_path.".jpeg");
        Storage::disk($disk)->assertExists($award->icon_path.".webp");

        $result = $award->deleteIconFiles();

        $this->assertTrue($result);

        Storage::disk($disk)->assertMissing($award->icon_path.".png");
        Storage::disk($disk)->assertMissing($award->icon_path.".jpeg");
        Storage::disk($disk)->assertMissing($award->icon_path.".webp");
    }

}
