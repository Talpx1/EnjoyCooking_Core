<?php

namespace App\Models;

use App\Models\Traits\HasRandomFactory;
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

    public function getIconPathsAttribute(){ //TODO: TEST
        $icons = [];
        foreach(explode(',', config('upload.award.save_as')) as $format) $icons[$format] = Storage::disk('public')->path($this->icon_path . ".{$format}");
        return collect($icons);
    }

    public function getIconUrlsAttribute(){ //TODO: TEST
        $icons = [];
        foreach(explode(',', config('upload.award.save_as')) as $format) $icons[$format] = Storage::disk('public')->url($this->icon_path . ".{$format}");
        return collect($icons);
    }

    public function getIconsAttribute(){ //TODO: TEST
        $icons = [];
        foreach(explode(',', config('upload.award.save_as')) as $format) $icons[$format] = base64_encode(Storage::disk('public')->get($this->icon_path . ".{$format}"));
        return collect($icons);
    }
}
