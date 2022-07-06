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

        $everyonePermissions = [
            Permission::ShowRecipe->value,
            Permission::ShowIngredient->value,
            Permission::ShowTag->value,
            Permission::ShowCategory->value,
            Permission::ShowCourse->value,
            Permission::ShowDifficultyLevel->value,
            Permission::ShowSnack->value,
        ];

        $userPermissions = array_merge($everyonePermissions, [
            Permission::CreateRecipe->value,
            Permission::StoreRecipe->value,
            Permission::EditRecipe->value,
            Permission::UpdateRecipe->value,
            Permission::DestroyRecipe->value,
            Permission::StoreRecipeImage->value,
            Permission::DestroyRecipeImage->value,
            Permission::StoreRecipeVideo->value,
            Permission::DestroyRecipeVideo->value,
            Permission::CreateIngredient->value,
            Permission::StoreIngredient->value,
            Permission::EditIngredient->value,
            Permission::UpdateIngredient->value,
            Permission::DestroyIngredient->value,
            Permission::StoreIngredientImage->value,
            Permission::DestroyIngredientVideo->value,
            Permission::CreateTag->value,
            Permission::StoreTag->value,
            Permission::StoreRepost->value,
            Permission::DestroyRepost->value,
            Permission::CreateComment->value,
            Permission::StoreComment->value,
            Permission::EditComment->value,
            Permission::UpdateComment->value,
            Permission::DestroyComment->value,
            Permission::CreateSnack->value,
            Permission::StoreSnack->value,
            Permission::EditSnack->value,
            Permission::UpdateSnack->value,
            Permission::DestroySnack->value,
            Permission::StoreFavorite->value,
            Permission::DestroyFavorite->value,
            Permission::CreateExecution->value,
            Permission::StoreExecution->value,
            Permission::EditExecution->value,
            Permission::UpdateExecution->value,
            Permission::DestroyExecution->value,
            Permission::StoreExecutionImage->value,
            Permission::DestroyExecutionImage->value,
            Permission::StoreExecutionVideo->value,
            Permission::DestroyExecutionVideo->value,
        ]);

        $moderatorPermissions = array_merge($everyonePermissions, [
            Permission::ApproveRecipe->value,
            Permission::RejectRecipe->value,
            Permission::ApproveIngredient->value,
            Permission::RejectIngredient->value,
            Permission::ApproveIngredientImage->value,
            Permission::RejectIngredientVideo->value,
            Permission::DestroyIngredientImage->value,
            Permission::DestroyIngredientVideo->value,
            Permission::ApproveTag->value,
            Permission::RejectTag->value,
            Permission::BanComment->value,
            Permission::BanSnack->value,
            Permission::BanExecution->value,
            Permission::DestroyExecutionImage->value,
            Permission::DestroyExecutionVideo->value,
            Permission::BanUser->value,
            Permission::ForgiveUser->value,
        ]);

        $adminPermissions = array_merge($everyonePermissions, $moderatorPermissions, [
            Permission::EditTag->value,
            Permission::UpdateTag->value,
            Permission::DestroyTag->value,
            Permission::CreateCategory->value,
            Permission::StoreCategory->value,
            Permission::EditCategory->value,
            Permission::UpdateCategory->value,
            Permission::DestroyCategory->value,
            Permission::CreateCourse->value,
            Permission::StoreCourse->value,
            Permission::EditCourse->value,
            Permission::UpdateCourse->value,
            Permission::DestroyCourse->value,
            Permission::CreateDifficultyLevel->value,
            Permission::StoreDifficultyLevel->value,
            Permission::EditDifficultyLevel->value,
            Permission::UpdateDifficultyLevel->value,
            Permission::DestroyDifficultyLevel->value,
            Permission::CreateBadge->value,
            Permission::StoreBadge->value,
            Permission::EditBadge->value,
            Permission::UpdateBadge->value,
            Permission::DestroyBadge->value,
            Permission::CreateAward->value,
            Permission::StoreAward->value,
            Permission::EditAward->value,
            Permission::UpdateAward->value,
            Permission::DestroyAward->value,
            Permission::AssignBadge->value,
        ]);

        Role::findByName(EnumsRole::Admin->value)->givePermissionTo($adminPermissions);
        Role::findByName(EnumsRole::Moderator->value)->givePermissionTo($moderatorPermissions);
        Role::findByName(EnumsRole::User->value)->givePermissionTo($userPermissions);
    }
}
