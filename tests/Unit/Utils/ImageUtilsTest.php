<?php

namespace Tests\Unit\Utils;

use App\Utils\ImageUtils;
use Illuminate\Http\Testing\MimeType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;
use RuntimeException;

/**
 * @coversDefaultClass App\Utils\ImageUtils
 */
class ImageUtilsTest extends TestCase
{
    /**
     * @test
     *
     * @covers ::save
     */
    public function test_it_saves_image(){
        $disk = 'public';
        Storage::fake($disk);
        $image = UploadedFile::fake()->image('test.png')->mimeType(MimeType::get('png'));

        $this->assertTrue(ImageUtils::save($image, $disk, '/test_save/test123', 'jpeg'));

        Storage::disk($disk)->assertExists('/test_save/test123.jpeg');
    }

    /**
     * @test
     *
     * @covers ::save
     */
    public function test_image_saving_fails_if_width_or_height_are_invalid(){
        $disk = 'public';
        Storage::fake($disk);
        $image = UploadedFile::fake()->image('test.png')->mimeType(MimeType::get('png'));

        try{
            $this->assertTrue(ImageUtils::save($image, $disk, '/test_save/test123', 'jpeg', 0, 0));
        }catch(RuntimeException $e){
            $this->assertTrue($e->getCode() == 500);
        }

        try{
            $this->assertTrue(ImageUtils::save($image, $disk, '/test_save/test123', 'jpeg', null, 0));
        }catch(RuntimeException $e){
            $this->assertTrue($e->getCode() == 500);
        }

        try{
            $this->assertTrue(ImageUtils::save($image, $disk, '/test_save/test123', 'jpeg', 0, null));
        }catch(RuntimeException $e){
            $this->assertTrue($e->getCode() == 500);
        }

        try{
            $this->assertTrue(ImageUtils::save($image, $disk, '/test_save/test123', 'jpeg', -1, null));
        }catch(RuntimeException $e){
            $this->assertTrue($e->getCode() == 500);
        }
    }

    /**
     * @test
     *
     * @covers ::save
     */
    public function test_if_width_and_height_are_null_the_original_image_dimensions_get_used(){
        $disk = 'public';
        Storage::fake($disk);
        $image = UploadedFile::fake()->image('test.png', 123, 456)->mimeType(MimeType::get('png'));

        $this->assertTrue(ImageUtils::save($image, $disk, '/test_save/test123', 'jpeg', null, null));

        $this->assertTrue(Image::make(Storage::disk($disk)->get('/test_save/test123.jpeg'))->width() == 123);
        $this->assertTrue(Image::make(Storage::disk($disk)->get('/test_save/test123.jpeg'))->height() == 456);
    }

    /**
     * @test
     *
     * @covers ::saveWithMultipleExtensions
     */
    public function test_it_saves_image_with_multiple_extensions(){
        $extensions = ['jpeg', 'png', 'webp'];
        $disk = 'public';
        Storage::fake($disk);
        $image = UploadedFile::fake()->image('test.png')->mimeType(MimeType::get('png'));

        $this->assertTrue(ImageUtils::saveWithMultipleExtensions($image, $disk, '/test_save/test123', $extensions));

        Storage::disk($disk)->assertExists('/test_save/test123.jpeg');
        Storage::disk($disk)->assertExists('/test_save/test123.png');
        Storage::disk($disk)->assertExists('/test_save/test123.webp');
    }

    /**
     * @test
     *
     * @covers ::saveWithMultipleExtensions
     */
    public function test_it_saves_image_with_single_extensions(){
        $extensions = 'jpeg';
        $disk = 'public';
        Storage::fake($disk);
        $image = UploadedFile::fake()->image('test.png')->mimeType(MimeType::get('png'));

        $this->assertTrue(ImageUtils::saveWithMultipleExtensions($image, $disk, '/test_save/test123', $extensions));

        Storage::disk($disk)->assertExists('/test_save/test123.jpeg');
        Storage::disk($disk)->assertMissing('/test_save/test123.png');
        Storage::disk($disk)->assertMissing('/test_save/test123.webp');
    }

    /**
     * @test
     *
     * @covers ::getMultipleExtensionsCollection
     */
    public function test_it_gets_collection_of_image_saved_with_multiple_extensions(){
        $extensions = ['jpeg', 'png', 'webp'];
        $disk = 'public';
        $save_path = '/test_save/test123';
        Storage::fake($disk);

        $getMultipleExtensionsCollection_method = (new ReflectionClass(ImageUtils::class))->getMethod('getMultipleExtensionsCollection');
        $getMultipleExtensionsCollection_method->setAccessible(true);

        $image = UploadedFile::fake()->image('test.png')->mimeType(MimeType::get('png'));

        $this->assertTrue(ImageUtils::saveWithMultipleExtensions($image, $disk, $save_path, $extensions));

        Storage::disk($disk)->assertExists("{$save_path}.jpeg");
        Storage::disk($disk)->assertExists("{$save_path}.png");
        Storage::disk($disk)->assertExists("{$save_path}.webp");

        $images_paths = $getMultipleExtensionsCollection_method->invoke(null, $extensions, $save_path, "path", $disk);
        $images_urls = $getMultipleExtensionsCollection_method->invoke(null, $extensions, $save_path, "url", $disk);
        $images_base64_encoded = $getMultipleExtensionsCollection_method->invoke(null, $extensions, $save_path, "get", $disk);

        $this->assertInstanceOf(Collection::class, $images_paths);
        $this->assertInstanceOf(Collection::class, $images_urls);
        $this->assertInstanceOf(Collection::class, $images_base64_encoded);

        $this->assertTrue($images_paths->has($extensions));
        $this->assertTrue($images_urls->has($extensions));
        $this->assertTrue($images_base64_encoded->has($extensions));

        $this->assertNotEmpty($images_paths->get("png"));
        $this->assertNotEmpty($images_paths->get("jpeg"));
        $this->assertNotEmpty($images_paths->get("webp"));
        $this->assertNotEmpty($images_urls->get("png"));
        $this->assertNotEmpty($images_urls->get("jpeg"));
        $this->assertNotEmpty($images_urls->get("webp"));
        $this->assertNotEmpty($images_base64_encoded->get("png"));
        $this->assertNotEmpty($images_base64_encoded->get("jpeg"));
        $this->assertNotEmpty($images_base64_encoded->get("webp"));
    }

    /**
     * @test
     *
     * @covers ::getMultipleExtensionsCollection
     */
    public function test_it_gets_collection_of_image_saved_with_single_extensions(){
        $extensions = 'jpeg';
        $disk = 'public';
        $save_path = '/test_save/test123';
        Storage::fake($disk);

        $getMultipleExtensionsCollection_method = (new ReflectionClass(ImageUtils::class))->getMethod('getMultipleExtensionsCollection');
        $getMultipleExtensionsCollection_method->setAccessible(true);

        $image = UploadedFile::fake()->image('test.png')->mimeType(MimeType::get('png'));

        $this->assertTrue(ImageUtils::saveWithMultipleExtensions($image, $disk, $save_path, $extensions));

        Storage::disk($disk)->assertExists("{$save_path}.jpeg");
        Storage::disk($disk)->assertMissing("{$save_path}.png");
        Storage::disk($disk)->assertMissing("{$save_path}.webp");

        $images_paths = $getMultipleExtensionsCollection_method->invoke(null, $extensions, $save_path, "path", $disk);
        $images_urls = $getMultipleExtensionsCollection_method->invoke(null, $extensions, $save_path, "url", $disk);
        $images_base64_encoded = $getMultipleExtensionsCollection_method->invoke(null, $extensions, $save_path, "get", $disk);

        $this->assertInstanceOf(Collection::class, $images_paths);
        $this->assertInstanceOf(Collection::class, $images_urls);
        $this->assertInstanceOf(Collection::class, $images_base64_encoded);

        $this->assertTrue($images_paths->has($extensions));
        $this->assertTrue($images_urls->has($extensions));
        $this->assertTrue($images_base64_encoded->has($extensions));

        $this->assertFalse($images_paths->has(['png', 'webp']));
        $this->assertFalse($images_urls->has(['png', 'webp']));
        $this->assertFalse($images_base64_encoded->has(['png', 'webp']));

        $this->assertNotEmpty($images_paths->get("jpeg"));
        $this->assertEmpty($images_paths->get("png"));
        $this->assertEmpty($images_paths->get("webp"));

        $this->assertNotEmpty($images_urls->get("jpeg"));
        $this->assertEmpty($images_urls->get("png"));
        $this->assertEmpty($images_urls->get("webp"));

        $this->assertNotEmpty($images_base64_encoded->get("jpeg"));
        $this->assertEmpty($images_base64_encoded->get("png"));
        $this->assertEmpty($images_base64_encoded->get("webp"));
    }

    /**
     * @test
     *
     * @covers ::getMultipleExtensionsPathsCollection
     */
    public function test_it_gets_collection_of_paths_of_image_saved_with_multiple_extensions(){
        $extensions = ['jpeg', 'png', 'webp'];
        $disk = 'public';
        $save_path = '/test_save/test123';
        Storage::fake($disk);

        $image = UploadedFile::fake()->image('test.png')->mimeType(MimeType::get('png'));

        $this->assertTrue(ImageUtils::saveWithMultipleExtensions($image, $disk, $save_path, $extensions));

        Storage::disk($disk)->assertExists("{$save_path}.jpeg");
        Storage::disk($disk)->assertExists("{$save_path}.png");
        Storage::disk($disk)->assertExists("{$save_path}.webp");

        $images_paths = ImageUtils::getMultipleExtensionsPathsCollection($extensions, $save_path, $disk);


        $this->assertInstanceOf(Collection::class, $images_paths);

        $this->assertTrue($images_paths->has($extensions));

        $this->assertNotEmpty($images_paths->get("png"));
        $this->assertNotEmpty($images_paths->get("jpeg"));
        $this->assertNotEmpty($images_paths->get("webp"));

        $this->assertEquals($images_paths->get("png"), Storage::disk($disk)->path("{$save_path}.png"));
        $this->assertEquals($images_paths->get("jpeg"), Storage::disk($disk)->path("{$save_path}.jpeg"));
        $this->assertEquals($images_paths->get("webp"), Storage::disk($disk)->path("{$save_path}.webp"));
    }

    /**
     * @test
     *
     * @covers ::getMultipleExtensionsUrlsCollection
     */
    public function test_it_gets_collection_of_urls_of_image_saved_with_multiple_extensions(){
        $extensions = ['jpeg', 'png', 'webp'];
        $disk = 'public';
        $save_path = '/test_save/test123';
        Storage::fake($disk);

        $image = UploadedFile::fake()->image('test.png')->mimeType(MimeType::get('png'));

        $this->assertTrue(ImageUtils::saveWithMultipleExtensions($image, $disk, $save_path, $extensions));

        Storage::disk($disk)->assertExists("{$save_path}.jpeg");
        Storage::disk($disk)->assertExists("{$save_path}.png");
        Storage::disk($disk)->assertExists("{$save_path}.webp");

        $images_urls = ImageUtils::getMultipleExtensionsUrlsCollection($extensions, $save_path, $disk);


        $this->assertInstanceOf(Collection::class, $images_urls);

        $this->assertTrue($images_urls->has($extensions));

        $this->assertNotEmpty($images_urls->get("png"));
        $this->assertNotEmpty($images_urls->get("jpeg"));
        $this->assertNotEmpty($images_urls->get("webp"));

        $this->assertEquals($images_urls->get("png"), Storage::disk($disk)->url("{$save_path}.png"));
        $this->assertEquals($images_urls->get("jpeg"), Storage::disk($disk)->url("{$save_path}.jpeg"));
        $this->assertEquals($images_urls->get("webp"), Storage::disk($disk)->url("{$save_path}.webp"));
    }

    /**
     * @test
     *
     * @covers ::getMultipleExtensionsBase64EncodedCollection
     */
    public function test_it_gets_collection_of_base64_encodings_of_image_saved_with_multiple_extensions(){
        $extensions = ['jpeg', 'png', 'webp'];
        $disk = 'public';
        $save_path = '/test_save/test123';
        Storage::fake($disk);

        $image = UploadedFile::fake()->image('test.png')->mimeType(MimeType::get('png'));

        $this->assertTrue(ImageUtils::saveWithMultipleExtensions($image, $disk, $save_path, $extensions));

        Storage::disk($disk)->assertExists("{$save_path}.jpeg");
        Storage::disk($disk)->assertExists("{$save_path}.png");
        Storage::disk($disk)->assertExists("{$save_path}.webp");

        $images_base64 = ImageUtils::getMultipleExtensionsBase64EncodedCollection($extensions, $save_path, $disk);


        $this->assertInstanceOf(Collection::class, $images_base64);

        $this->assertTrue($images_base64->has($extensions));

        $this->assertNotEmpty($images_base64->get("png"));
        $this->assertNotEmpty($images_base64->get("jpeg"));
        $this->assertNotEmpty($images_base64->get("webp"));

        $this->assertEquals($images_base64->get("png"), base64_encode(Storage::disk($disk)->get("{$save_path}.png")));
        $this->assertEquals($images_base64->get("jpeg"), base64_encode(Storage::disk($disk)->get("{$save_path}.jpeg")));
        $this->assertEquals($images_base64->get("webp"), base64_encode(Storage::disk($disk)->get("{$save_path}.webp")));
    }
}
