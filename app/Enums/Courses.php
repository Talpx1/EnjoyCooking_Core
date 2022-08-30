<?php

namespace App\Enums;

use App\Enums\Traits\EnumAsArray;
use App\Enums\Traits\NormalizeNames;

enum Courses: int {

    use EnumAsArray, NormalizeNames;

    case STARTER = 1;
    case MAIN_COURSE = 2;
    case SECOND_COURSE = 3;
    case SIDE = 4;
    case DESSERT = 5;
    case DRINK = 6;
    case APPETIZER = 7;
}
