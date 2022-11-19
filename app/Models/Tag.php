<?php

namespace App\Models;

use App\Models\Traits\HasRandomFactory;
use App\Models\Traits\NotifyDeletionToMorphs;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory, HasRandomFactory, Sluggable, NotifyDeletionToMorphs;

    protected $guarded = ['id', 'created_at', 'updated_at', 'slug'];

    private static $morphs = [Follow::class];

    public function sluggable(): array{
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    public function ingredients(){
        return $this->morphedByMany(Ingredient::class, 'taggable');
    }

    public function recipes(){
        return $this->morphedByMany(Recipe::class, 'taggable');
    }

    public function snacks(){
        return $this->morphedByMany(Snack::class, 'taggable');
    }

    public function followers(){
        return $this->morphMany(Follow::class, 'followable');
    }

}
