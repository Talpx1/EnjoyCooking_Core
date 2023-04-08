<?php

namespace App\Utils;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Constraint;
use Intervention\Image\Facades\Image;
use RuntimeException;

class ImageUtils {

    public static function save($image_source, string $disk, string $save_path, string $extension, ?int $save_width = null, ?int $save_height = null): bool {

        if( !(is_null($save_width) && is_null($save_height)) && ($save_width <= 0 ||  $save_height <= 0) ) throw new RuntimeException(__('Invalid width and/or height while saving image. Those values must be positive ints or null(s).'), 500);

        $image = Image::make($image_source);

        if(is_null($save_width) && is_null($save_height)){
            $save_width = $image->width();
            $save_width = $image->height();
        }

        $image->resize($save_width, $save_height , function (Constraint $constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        })->encode($extension);

        return Storage::disk($disk)->put($save_path.".{$extension}", $image);
    }

    public static function saveWithMultipleExtensions($image_source, string $disk, string $save_path, array|string $extensions, ?int $save_width = null, ?int $save_height = null): bool {
        if(!is_array($extensions)) {
            self::save($image_source, $disk, $save_path, $extensions, $save_width, $save_height) ?: throw new RuntimeException(__('An error occurred while saving an image'), 500);
            return true;
        };

        foreach($extensions as $extension) {
            self::save($image_source, $disk, $save_path, $extension, $save_width, $save_height) ?: throw new RuntimeException(__('An error occurred while saving an image'), 500);
        }

        return true;
    }

    private static function getMultipleExtensionsCollection(array|string $extensions, string $images_path, string $storage_method, string $disk){
        if(!is_array($extensions)) {
            $image = Storage::disk($disk)->{$storage_method}("{$images_path}.{$extensions}");
            return collect([$extensions => $storage_method === "get" ? base64_encode($image) : $image]);
        }

        $images = [];
        foreach($extensions as $extension) {
            $image = Storage::disk($disk)->{$storage_method}("{$images_path}.{$extension}");
            $images[$extension] = $storage_method === "get" ? base64_encode($image) : $image;
        }
        return collect($images);
    }

    public static function getMultipleExtensionsPathsCollection(array|string $extensions, string $images_path, string $disk): \Illuminate\Support\Collection{
        return self::getMultipleExtensionsCollection($extensions, $images_path, "path", $disk);
    }

    public static function getMultipleExtensionsUrlsCollection(array|string $extensions, string $images_path, string $disk): \Illuminate\Support\Collection{
        return self::getMultipleExtensionsCollection($extensions, $images_path, "url", $disk);
    }

    public static function getMultipleExtensionsBase64EncodedCollection(array|string $extensions, string $images_path, string $disk): \Illuminate\Support\Collection{
        return self::getMultipleExtensionsCollection($extensions, $images_path, "get", $disk);
    }
}
