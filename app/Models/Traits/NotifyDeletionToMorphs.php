<?php

namespace App\Models\Traits;

trait NotifyDeletionToMorphs{

    protected static function booted(){
        static::deleting(function (self $model) {
            foreach(self::$morphs as $morph) $morph::performMorphCleaning($model);
        });
    }

}
