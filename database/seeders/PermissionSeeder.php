<?php

namespace Database\Seeders;

use App\Enums\Permissions;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        foreach (Permissions::cases() as $permission) Permission::create(["name" => $permission->value]);
    }
}
