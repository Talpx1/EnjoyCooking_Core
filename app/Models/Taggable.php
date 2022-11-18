<?php

namespace App\Models;

use App\Models\Interfaces\MorphCleaning;
use App\Models\Traits\HasRandomFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Taggable extends Model implements MorphCleaning
{
    use HasFactory, HasRandomFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public static function performMorphCleaning(Model $morphed): void{
        self::where([['taggable_id', '=', $morphed->id],['taggable_type', '=', $morphed::class]])->delete();
    }
}