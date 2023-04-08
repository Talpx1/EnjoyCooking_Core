<?php

namespace Database\Seeders;

use App\Enums\Permissions;
use App\Enums\Roles;
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
        $everyonePermissions = [
            Permissions::INDEX_RECIPE->value,
            Permissions::INDEX_INGREDIENT->value,
            Permissions::INDEX_TAG->value,
            Permissions::INDEX_CATEGORY->value,
            Permissions::INDEX_COURSE->value,
            Permissions::INDEX_DIFFICULTY_LEVEL->value,
            Permissions::INDEX_COMMENT->value,
            Permissions::INDEX_SNACK->value,
            Permissions::INDEX_BADGE->value,
            Permissions::INDEX_AWARD->value,
            Permissions::INDEX_EXECUTION->value,
            Permissions::SHOW_RECIPE->value,
            Permissions::SHOW_INGREDIENT->value,
            Permissions::SHOW_TAG->value,
            Permissions::SHOW_CATEGORY->value,
            Permissions::SHOW_COURSE->value,
            Permissions::SHOW_DIFFICULTY_LEVEL->value,
            Permissions::SHOW_SNACK->value,
            Permissions::CREATE_USER->value,
            Permissions::STORE_USER->value,
        ];

        $userPermissions = array_merge($everyonePermissions, [
            Permissions::CREATE_RECIPE->value,
            Permissions::STORE_RECIPE->value,
            Permissions::EDIT_RECIPE->value,
            Permissions::UPDATE_RECIPE->value,
            Permissions::DESTROY_RECIPE->value,
            Permissions::STORE_RECIPE_IMAGE->value,
            Permissions::DESTROY_RECIPE_IMAGE->value,
            Permissions::STORE_RECIPE_VIDEO->value,
            Permissions::DESTROY_RECIPE_VIDEO->value,
            Permissions::CREATE_INGREDIENT->value,
            Permissions::STORE_INGREDIENT->value,
            Permissions::EDIT_INGREDIENT->value,
            Permissions::UPDATE_INGREDIENT->value,
            Permissions::DESTROY_INGREDIENT->value,
            Permissions::STORE_INGREDIENT_IMAGE->value,
            Permissions::DESTROY_INGREDIENT_VIDEO->value,
            Permissions::CREATE_TAG->value,
            Permissions::STORE_TAG->value,
            Permissions::STORE_REPOST->value,
            Permissions::DESTROY_REPOST->value,
            Permissions::CREATE_COMMENT->value,
            Permissions::STORE_COMMENT->value,
            Permissions::EDIT_COMMENT->value,
            Permissions::UPDATE_COMMENT->value,
            Permissions::DESTROY_COMMENT->value,
            Permissions::CREATE_SNACK->value,
            Permissions::STORE_SNACK->value,
            Permissions::EDIT_SNACK->value,
            Permissions::UPDATE_SNACK->value,
            Permissions::DESTROY_SNACK->value,
            Permissions::STORE_FAVORITE->value,
            Permissions::DESTROY_FAVORITE->value,
            Permissions::CREATE_EXECUTION->value,
            Permissions::STORE_EXECUTION->value,
            Permissions::EDIT_EXECUTION->value,
            Permissions::UPDATE_EXECUTION->value,
            Permissions::DESTROY_EXECUTION->value,
            Permissions::STORE_EXECUTION_IMAGE->value,
            Permissions::DESTROY_EXECUTION_IMAGE->value,
            Permissions::STORE_EXECUTION_VIDEO->value,
            Permissions::DESTROY_EXECUTION_VIDEO->value,
            Permissions::SHOW_USER->value,
            Permissions::EDIT_USER->value,
            Permissions::UPDATE_USER->value,
            Permissions::DESTROY_USER->value,
            Permissions::LOGIN->value
        ]);

        $moderatorPermissions = array_merge($everyonePermissions, [
            Permissions::APPROVE_RECIPE->value,
            Permissions::REJECT_RECIPE->value,
            Permissions::APPROVE_INGREDIENT->value,
            Permissions::REJECT_INGREDIENT->value,
            Permissions::APPROVE_INGREDIENT_IMAGE->value,
            Permissions::REJECT_INGREDIENT_VIDEO->value,
            Permissions::DESTROY_INGREDIENT_IMAGE->value,
            Permissions::DESTROY_INGREDIENT_VIDEO->value,
            Permissions::APPROVE_TAG->value,
            Permissions::REJECT_TAG->value,
            Permissions::BAN_COMMENT->value,
            Permissions::BAN_SNACK->value,
            Permissions::BAN_EXECUTION->value,
            Permissions::DESTROY_EXECUTION_IMAGE->value,
            Permissions::DESTROY_EXECUTION_VIDEO->value,
            Permissions::BAN_USER->value,
            Permissions::FORGIVE_USER->value,
        ]);

        $adminPermissions = array_merge($everyonePermissions, $moderatorPermissions, [
            Permissions::EDIT_TAG->value,
            Permissions::UPDATE_TAG->value,
            Permissions::DESTROY_TAG->value,
            Permissions::CREATE_CATEGORY->value,
            Permissions::STORE_CATEGORY->value,
            Permissions::EDIT_CATEGORY->value,
            Permissions::UPDATE_CATEGORY->value,
            Permissions::DESTROY_CATEGORY->value,
            Permissions::CREATE_COURSE->value,
            Permissions::STORE_COURSE->value,
            Permissions::EDIT_COURSE->value,
            Permissions::UPDATE_COURSE->value,
            Permissions::DESTROY_COURSE->value,
            Permissions::CREATE_DIFFICULTY_LEVEL->value,
            Permissions::STORE_DIFFICULTY_LEVEL->value,
            Permissions::EDIT_DIFFICULTY_LEVEL->value,
            Permissions::UPDATE_DIFFICULTY_LEVEL->value,
            Permissions::DESTROY_DIFFICULTY_LEVEL->value,
            Permissions::CREATE_BADGE->value,
            Permissions::STORE_BADGE->value,
            Permissions::EDIT_BADGE->value,
            Permissions::UPDATE_BADGE->value,
            Permissions::DESTROY_BADGE->value,
            Permissions::CREATE_AWARD->value,
            Permissions::STORE_AWARD->value,
            Permissions::EDIT_AWARD->value,
            Permissions::UPDATE_AWARD->value,
            Permissions::DESTROY_AWARD->value,
            Permissions::ASSIGN_BADGE->value,
            Permissions::INDEX_USER->value,
        ]);

        Role::findByName(Roles::ADMIN->value)->givePermissionTo($adminPermissions);
        Role::findByName(Roles::MODERATOR->value)->givePermissionTo($moderatorPermissions);
        Role::findByName(Roles::USER->value)->givePermissionTo($userPermissions);
    }
}
