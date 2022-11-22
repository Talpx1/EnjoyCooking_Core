<?php

namespace Tests\Unit;

use App\Utils\ImageUtils;
use Illuminate\Http\Testing\MimeType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
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

        $this->assertTrue(Image::make(Storage::disk('public')->get('/test_save/test123.jpeg'))->width() == 123);
        $this->assertTrue(Image::make(Storage::disk('public')->get('/test_save/test123.jpeg'))->height() == 456);
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
}
