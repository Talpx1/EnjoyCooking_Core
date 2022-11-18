<?php

namespace App\Models;

use App\Models\Traits\HasRandomFactory;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory, HasRandomFactory, Sluggable;

    protected $guarded = ['id', 'created_at', 'updated_at', 'slug'];

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

}
