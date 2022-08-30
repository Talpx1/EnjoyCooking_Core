<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasRandomFactory;
use Cviebrock\EloquentSluggable\Sluggable;

class DifficultyLevel extends Model
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
}
