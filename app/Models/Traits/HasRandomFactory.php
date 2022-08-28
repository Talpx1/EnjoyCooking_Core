<?php

namespace App\Models\Traits;

trait HasRandomFactory{

    public static function getRandom() {
        return self::inRandomOrder()->limit(1)->first();
    }

    public static function getRandomOrCreate() {
        return self::getRandom() ?? self::factory()->create();
    }

}
