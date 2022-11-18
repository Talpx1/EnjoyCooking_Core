<?php

namespace App\Models;

use App\Models\Traits\HasRandomFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
