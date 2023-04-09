<?php

namespace App\Models;

use App\Models\Traits\HasRandomFactory;
use App\Models\Traits\NotifyDeletionToMorphs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Execution extends Model
{
    use HasFactory, HasRandomFactory, NotifyDeletionToMorphs;

    private static $morphs = [Awardable::class, Like::class, Comment::class, Favorite::class, Rating::class, Repost::class];

    public function recipe(){
        return $this->belongsTo(Recipe::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function ratings(){
        return $this->morphMany(Rating::class, 'rateable');
    }

    public function awards(){
        return $this->morphToMany(Award::class, 'awardable');
    }

    public function reposts(){
        return $this->morphMany(Repost::class, 'repostable');
    }

    public function likes(){
        return $this->morphMany(Like::class, 'likeable');
    }

    public function favorites(){
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    public function comments(){
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function images(){
        return $this->hasMany(ExecutionImage::class);
    }

    public function videos(){
        return $this->hasMany(ExecutionVideo::class);
    }

    public function moderationStatus(){
        return $this->belongsTo(ModerationStatus::class);
    }
}
