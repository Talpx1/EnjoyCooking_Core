<?php

namespace Tests\Unit;

use App\Utils\ImageUtils;
use Illuminate\Http\Testing\MimeType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

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

    //TODO: test width and hight validations and exception throws

    /**
     * @test
     *
     * @covers ::saveWithMultipleExtensions
     */
    public function test_it_saves_image_with_multiple_extensions(){
        //TODO
    }
}
