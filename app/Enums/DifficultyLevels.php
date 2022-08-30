<?php

namespace App\Enums;

use App\Enums\Traits\EnumAsArray;
use App\Enums\Traits\NormalizeNames;

enum DifficultyLevels: int {

    use EnumAsArray, NormalizeNames;

    case VERY_EASY = 1;
    case EASY = 2;
    case MEDIUM = 3;
    case DIFFICULT = 4;
    case VERY_DIFFICULT = 5;
}
