<?php

namespace App\Models;

use App\Models\Traits\HasRandomFactory;
use App\Models\Traits\NotifyDeletionToMorphs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Snack extends Model
{
    use HasFactory, HasRandomFactory, NotifyDeletionToMorphs;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    private static $morphs = [Taggable::class, Comment::class];

    public function recipe(){
        return $this->belongsTo(Recipe::class);
    }

    public function user(){
        return $this->belongsTo(Recipe::class);
    }

    public function tags(){
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function comments(){
        return $this->morphMany(Comment::class, 'commentable');
    }
}
