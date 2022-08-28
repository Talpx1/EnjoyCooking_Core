<?php

namespace Database\Seeders;

use App\Enums\Roles;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        foreach (Roles::cases() as $role) Role::create(["name" => $role->value]);
    }
}
