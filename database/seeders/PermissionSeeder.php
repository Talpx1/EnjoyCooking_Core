<?php

namespace Database\Seeders;

use App\Enums\Permissions;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission as ModelsPermission;

class PermissionSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        foreach (Permissions::cases() as $permission) ModelsPermission::create(["name" => $permission->value]);
    }
}
