<?php

namespace App\Enums;

use App\Enums\Traits\EnumAsArray;

enum Roles: string {

    use EnumAsArray;

    case SUPER_ADMIN = "super_admin";
    case ADMIN = "admin";
    case MODERATOR = "moderator";
    case USER = "user";
}
