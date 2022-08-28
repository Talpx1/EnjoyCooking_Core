<?php

namespace App\Utils;

use App\Enums\Roles;
use Illuminate\Support\Facades\Auth;

class AuthUtils {

    public static function isAdmin(): bool {
        return Auth::user()->hasRole(Roles::ADMIN->value);
    }

    public static function isModerator(): bool {
        return Auth::user()->hasRole(Roles::MODERATOR->value);
    }

    public static function isSuperAdmin(): bool {
        return Auth::user()->hasRole(Roles::SUPER_ADMIN->value);
    }

    public static function isUser(): bool {
        return Auth::user()->hasRole(Roles::USER->value);
    }

    public static function isLoggedIn(): bool {
        return !is_null(Auth::user());
    }
}
