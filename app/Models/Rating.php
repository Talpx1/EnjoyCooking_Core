<?php

namespace App\Models;

use App\Models\Interfaces\MorphCleaning;
use App\Models\Traits\HasRandomFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model implements MorphCleaning
{
    use HasFactory, HasRandomFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function rateable(){
        return $this->morphTo();
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public static function performMorphCleaning(Model $morphed): void{
        self::where([['rateable_id', '=', $morphed->id],['rateable_type', '=', $morphed::class]])->delete();
    }
}
