<?php

namespace Database\Seeders;

use App\Enums\Permission;
use App\Enums\Role as EnumsRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * Assign permissions to roles in the seeding phase.
 * In order for this seeder to correctly work, both PermissionSeeder and RoleSeeder need to be executed first
 */
class PermissionRoleSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        $everyonePermissions = [Permission::ShowRecipe->value];
        $userPermissions = array_merge($everyonePermissions, [Permission::CreateRecipe->value, Permission::StoreRecipe->value, Permission::EditRecipe->value, Permission::UpdateRecipe->value, Permission::DestroyRecipe->value]);
        $moderatorPermissions = array_merge($everyonePermissions, [Permission::ApproveRecipe->value, Permission::RejectRecipe->value]);
        $adminPermissions = array_merge($everyonePermissions, $moderatorPermissions, []);

        Role::findByName(EnumsRole::Admin->value)->givePermissionTo($adminPermissions);
        Role::findByName(EnumsRole::Moderator->value)->givePermissionTo($moderatorPermissions);
        Role::findByName(EnumsRole::User->value)->givePermissionTo($userPermissions);
    }
}
