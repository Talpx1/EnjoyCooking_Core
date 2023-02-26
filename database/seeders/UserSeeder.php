<?php

namespace Database\Seeders;

use App\Enums\Roles;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Enums\UserTypes;

class UserSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        //super admin
        User::factory()->create([
            "first_name" => "SuperAdmin",
            "last_name" => "SuperAdmin",
            'username' => 'super_admin',
            "email" => "superadmin@enjoy.cooking",
            "password" => Hash::make("superadmin"),
            'user_type_id'=>UserTypes::STANDARD,
            'date_of_birth' => '2022-01-01',
            'gender_id'=>null,
            'company_name'=> 'Enjoy Cooking',
            'profession_group_id' => null
        ])->assignRole(Roles::SUPER_ADMIN->value);
        //admin
        User::factory()->create([
            "first_name" => "Admin",
            "last_name" => "Admin",
            'username' => 'admin',
            "email" => "admin@enjoy.cooking",
            "password" => Hash::make("admin"),
            'user_type_id'=>UserTypes::STANDARD,
            'date_of_birth' => '2022-01-01',
            'gender_id'=>null,
            'company_name'=> 'Enjoy Cooking',
            'profession_group_id' => null
        ])->assignRole(Roles::ADMIN->value);
        //moderator
        User::factory()->create([
            "first_name" => "Moderator",
            "last_name" => "Moderator",
            'username' => 'moderator',
            "email" => "moderator@enjoy.cooking",
            "password" => Hash::make("moderator"),
            'user_type_id'=>UserTypes::STANDARD,
            'date_of_birth' => '2022-01-01',
            'gender_id'=>null,
            'company_name'=> 'Enjoy Cooking',
            'profession_group_id' => null
        ])->assignRole(Roles::MODERATOR->value);
        //user
        User::factory()->create([
            "first_name" => "User",
            "last_name" => "User",
            'username' => 'user',
            "email" => "user@enjoy.cooking",
            "password" => Hash::make("user"),
            'user_type_id'=>UserTypes::STANDARD,
            'date_of_birth' => '2022-01-01',
            'gender_id'=>null,
            'company_name'=> 'Enjoy Cooking',
            'profession_group_id' => null
        ])->assignRole(Roles::USER->value);
    }
}
