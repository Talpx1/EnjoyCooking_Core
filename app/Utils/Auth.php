<?php

namespace App\Utils;

use App\Enums\Role;
use Illuminate\Support\Facades\Auth as FacadesAuth;

class Auth {

    public static function isAdmin(): bool {
        return FacadesAuth::user()->hasRole(Role::Admin->value);
    }
}
