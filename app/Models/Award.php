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

    public function recipes(){
        return $this->morphedByMany(Recipe::class, 'awardable');
    }

    public function comments(){
        return $this->morphedByMany(Comment::class, 'awardable');
    }

    public function getIconPathsAttribute(){
        $icons = [];
        foreach(explode(',', config('upload.award.save_as')) as $format) $icons[$format] = Storage::disk('public')->path($this->icon_path . ".{$format}");
        return collect($icons);
    }

    public function getIconUrlsAttribute(){
        $icons = [];
        foreach(explode(',', config('upload.award.save_as')) as $format) $icons[$format] = Storage::disk('public')->url($this->icon_path . ".{$format}");
        return collect($icons);
    }

    public function getIconsAttribute(){
        $icons = [];
        foreach(explode(',', config('upload.award.save_as')) as $format) $icons[$format] = base64_encode(Storage::disk('public')->get($this->icon_path . ".{$format}"));
        return collect($icons);
    }

    public static function storeIcon($icon_source): string{
        $path = config('upload.award.save_path') . uniqid(time().'_');
        $extensions = explode(',', config('upload.award.save_as'));
        ImageUtils::saveWithMultipleExtensions($icon_source, 'public', $path, $extensions, config('upload.award.save_width'), config('upload.award.save_height'));

        return $path;
    }

    public function deleteIconFiles(): bool{
        $icons = glob(Storage::disk('public')->path($this->icon_path.'.*'));
        array_walk($icons, fn(&$path) => $path = str_replace(Storage::disk('public')->path(''), '', $path));
        return Storage::disk('public')->delete($icons);
    }
}
