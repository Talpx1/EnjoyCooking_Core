<?php

namespace App\Models;

use App\Models\Interfaces\MorphCleaning;
use App\Models\Traits\HasRandomFactory;
use App\Models\Traits\MorphCleaningOnDelete;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model implements MorphCleaning{
    use HasFactory, HasRandomFactory, MorphCleaningOnDelete;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    private static $morphs = [Awardable::class];

    public function commentable(){
        return $this->morphTo();
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function awards(){
        return $this->morphToMany(Award::class, 'awardable');
    }

    public static function performMorphCleaning(Model $morphed): void{
        self::where([['commentable_id', '=', $morphed->id],['commentable_type', '=', $morphed::class]])->delete();
    }
}
