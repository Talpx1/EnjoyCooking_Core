<?php

namespace App\Enums;

use App\Enums\Traits\EnumAsArray;
use App\Enums\Traits\NormalizeNames;

enum ModerationStatuses: int {

    use EnumAsArray, NormalizeNames;

    case PENDING_MODERATION = 1;
    case UNDER_MODERATION = 2;
    case APPROVED = 3;
    case REJECTED = 4;
}
