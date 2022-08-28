<?php

namespace Database\Seeders;

use App\Enums\Roles;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        //super admin
        User::factory()->create(["name" => "SuperAdmin", "email" => "superadmin@enjoy.cooking", "password" => "superadmin"])
            ->assignRole(Roles::SUPER_ADMIN->value);
        //admin
        User::factory()->create(["name" => "Admin", "email" => "admin@enjoy.cooking", "password" => "admin"])
            ->assignRole(Roles::ADMIN->value);
        //moderator
        User::factory()->create(["name" => "Moderator", "email" => "moderator@enjoy.cooking", "password" => "moderator"])
            ->assignRole(Roles::MODERATOR->value);
        //user
        User::factory()->create(["name" => "User", "email" => "user@enjoy.cooking", "password" => "user"]);
    }
}
