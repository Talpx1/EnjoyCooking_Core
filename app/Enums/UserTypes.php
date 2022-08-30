<?php

namespace App\Enums;

use App\Enums\Traits\EnumAsArray;
use App\Enums\Traits\NormalizeNames;

enum UserTypes: int {

    use EnumAsArray, NormalizeNames;

    case STANDARD = 1;
    case COMPANY = 2;
}
