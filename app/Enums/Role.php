<?php

namespace App\Enums;

enum Role: string {
    case SuperAdmin = "super_admin";
    case Admin = "admin";
    case Moderator = "moderator";
    case User = "user";
}
