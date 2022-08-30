<?php

namespace App\Enums;

use App\Enums\Traits\EnumAsArray;
use App\Enums\Traits\NormalizeNames;

enum VisibilityStatuses: int {

    use EnumAsArray, NormalizeNames;

    case PUBLIC = 1;
    case DRAFT = 2;
}
