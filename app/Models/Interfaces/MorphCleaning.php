<?php

namespace App\Models\Interfaces;
use Illuminate\Database\Eloquent\Model;

interface MorphCleaning{

    public static function performMorphCleaning(Model $morphed):void;

}
