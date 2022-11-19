<?php

namespace App\Models;

use App\Models\Traits\MorphCascadeOnDelete;
use App\Models\Traits\NotifyDeletionToMorphs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasRandomFactory;

class Repost extends Model{
    use HasFactory, HasRandomFactory, MorphCascadeOnDelete, NotifyDeletionToMorphs;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    private static $morphName = 'repostable';
    private static $morphs = [Like::class];

    public function repostable(){
        return $this->morphTo();
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function likes(){
        return $this->morphMany(Like::class, 'likeable');
    }
}
