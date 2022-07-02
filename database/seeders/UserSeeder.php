<?php

namespace Database\Seeders;

use App\Enums\Role;
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
        User::factory()->create(["name" => "SuperAdmin", "email" => "superadmin@enjoy.cooking", "password" => Hash::make("superadmin")])
            ->assignRole(Role::SuperAdmin->value);
        //admin
        User::factory()->create(["name" => "Admin", "email" => "admin@enjoy.cooking", "password" => Hash::make("admin")])
            ->assignRole(Role::Admin->value);
        //moderator
        User::factory()->create(["name" => "Moderator", "email" => "moderator@enjoy.cooking", "password" => Hash::make("moderator")])
            ->assignRole(Role::Moderator->value);
        //user
        User::factory()->create(["name" => "User", "email" => "user@enjoy.cooking", "password" => Hash::make("user")]);
    }
}
