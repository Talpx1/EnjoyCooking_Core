<?php

namespace App\Models\Traits;
use Illuminate\Database\Eloquent\Model;

trait MorphCascadeOnDelete{

    public static function performMorphCleaning(Model $morphed): void{
        self::where([[self::$morphName.'_id', '=', $morphed->id],[self::$morphName.'_type', '=', $morphed::class]])->delete();
    }

}
