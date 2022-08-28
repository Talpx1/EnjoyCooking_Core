<?php

namespace App\Enums;

use App\Enums\Traits\EnumAsArray;

enum ModerationStatuses: string {

    use EnumAsArray;

    case PENDING_MODERATION = "pending_moderation";
    case UNDER_MODERATION = "under_moderation";
    case APPROVED = "approved";
    case REJECTED = "rejected";
}
