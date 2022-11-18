<?php

namespace App\Models;

use App\Models\Interfaces\MorphCleaning;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasRandomFactory;

class Repost extends Model implements MorphCleaning{
    use HasFactory, HasRandomFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function repostable(){
        return $this->morphTo();
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public static function performMorphCleaning(Model $morphed):void {
        self::where([['repostable_id', '=', $morphed->id],['repostable_type', '=', $morphed::class]])->delete();
    }
}
