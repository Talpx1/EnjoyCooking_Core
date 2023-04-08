<?php

namespace App\Models;

use App\Models\Traits\HasRandomFactory;
use App\Utils\ImageUtils;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Award extends Model
{
    use HasFactory, HasRandomFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $appends = ['icons'];
    protected $casts = ['price'=>'float'];

    public function recipes(){
        return $this->morphedByMany(Recipe::class, 'awardable');
    }

    public function comments(){
        return $this->morphedByMany(Comment::class, 'awardable');
    }

    public function getIconPathsAttribute(){
        return ImageUtils::getMultipleExtensionsPathsCollection(config('upload.award.save_as'), $this->icon_path, config('upload.award.disk'));
    }

    public function getIconUrlsAttribute(){
        return ImageUtils::getMultipleExtensionsUrlsCollection(config('upload.award.save_as'), $this->icon_path, config('upload.award.disk'));
    }

    public function getIconsAttribute(){
        return ImageUtils::getMultipleExtensionsBase64EncodedCollection(config('upload.award.save_as'), $this->icon_path, config('upload.award.disk'));
    }

    public static function storeIcon($icon_source): string{
        $path = config('upload.award.save_path') . uniqid(time().'_');
        ImageUtils::saveWithMultipleExtensions($icon_source, config('upload.award.disk'), $path, config('upload.award.save_as'), config('upload.award.save_width'), config('upload.award.save_height'));
        return $path;
    }

    public function deleteIconFiles(): bool{
        $disk = config('upload.award.disk');
        $icons = glob(Storage::disk($disk)->path($this->icon_path.'.*'));
        array_walk($icons, fn(&$path) => $path = str_replace(Storage::disk($disk)->path(''), '', $path));
        return Storage::disk($disk)->delete($icons);
    }
}
