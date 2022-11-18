<?php

namespace App\Models;

use App\Models\Traits\HasRandomFactory;
use App\Models\Traits\MorphCleaningOnDelete;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory, HasRandomFactory, Sluggable, MorphCleaningOnDelete;

    protected $guarded = ['id', 'created_at', 'updated_at', 'slug'];

    private static $morphs = [Taggable::class];

    public function sluggable(): array{
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function videos(){
        return $this->hasMany(IngredientVideo::class);
    }

    public function images(){
        return $this->hasMany(IngredientImage::class);
    }

    public function recipes(){
        return $this->belongsToMany(Recipe::class);
    }

    public function tags(){
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
