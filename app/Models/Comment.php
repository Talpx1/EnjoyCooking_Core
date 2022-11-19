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

    private static $morphs = [Awardable::class];
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
}
