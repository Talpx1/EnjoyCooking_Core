<?php

namespace App\Models;

use App\Models\Traits\HasRandomFactory;
use App\Models\Traits\MorphCascadeOnDelete;
use App\Models\Traits\NotifyDeletionToMorphs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model{
    use HasFactory, HasRandomFactory, NotifyDeletionToMorphs, MorphCascadeOnDelete;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    private static $morphs = [Awardable::class, Like::class];
    private static $morphName = 'commentable';

    public function commentable(){
        return $this->morphTo();
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function awards(){
        return $this->morphToMany(Award::class, 'awardable');
    }

    public function likes(){
        return $this->morphMany(Like::class, 'likeable');
    }

    public function parentComment(){
        return $this->belongsTo(Comment::class, 'parent_comment_id');
    }

    public function replies(){
        return $this->hasMany(Comment::class, 'parent_comment_id');
    }

    public function getHasRepliesAttribute(){
        return $this->replies->count() > 0;
    }

    public function getIsReplyAttribute(){
        return !is_null($this->parent_comment_id);
    }
}
