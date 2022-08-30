<?php

namespace App\Enums;

use App\Enums\Traits\EnumAsArray;
use App\Enums\Traits\NormalizeNames;

enum Genders: int {

    use EnumAsArray, NormalizeNames;

    case MALE = 1;
    case FEMALE = 2;
    case NON_BINARY = 3;
}
