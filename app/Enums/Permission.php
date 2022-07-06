<?php

namespace App\Enums;

enum Permission: string {
        //recipe
    case CreateRecipe = "create_recipe";
    case StoreRecipe = "store_recipe";
    case EditRecipe = "edit_recipe";
    case UpdateRecipe = "update_recipe";
    case DestroyRecipe = "destroy_recipe";
    case ShowRecipe = "show_recipe";
    case ApproveRecipe = "approve_recipe";
    case RejectRecipe = "reject_recipe";
        //recipe image
    case StoreRecipeImage = "store_recipe_image";
    case DestroyRecipeImage = "destroy_recipe_image";
        //recipe video
    case StoreRecipeVideo = "store_recipe_video";
    case DestroyRecipeVideo = "destroy_recipe_video";
        // ingredient
    case CreateIngredient = "create_ingredient";
    case StoreIngredient = "store_ingredient";
    case EditIngredient = "edit_ingredient";
    case UpdateIngredient = "update_ingredient";
    case DestroyIngredient = "destroy_ingredient";
    case ShowIngredient = "show_ingredient";
    case ApproveIngredient = "approve_ingredient";
    case RejectIngredient = "reject_ingredient";
        //ingredient image
    case StoreIngredientImage = "store_ingredient_image";
    case DestroyIngredientImage = "destroy_ingredient_image";
    case ApproveIngredientImage = "approve_ingredient_image";
    case RejectIngredientImage = "reject_ingredient_image";
        //ingredient video
    case StoreIngredientVideo = "store_ingredient_video";
    case DestroyIngredientVideo = "destroy_ingredient_video";
    case ApproveIngredientVideo = "approve_ingredient_video";
    case RejectIngredientVideo = "reject_ingredient_video";
        //tag
    case CreateTag = "create_tag";
    case StoreTag = "store_tag";
    case EditTag = "edit_tag";
    case UpdateTag = "update_tag";
    case DestroyTag = "destroy_tag";
    case ShowTag = "show_tag";
    case ApproveTag = "approve_tag";
    case RejectTag = "reject_tag";
        //category
    case CreateCategory = "create_category";
    case StoreCategory = "store_category";
    case EditCategory = "edit_category";
    case UpdateCategory = "update_category";
    case DestroyCategory = "destroy_category";
    case ShowCategory = "show_category";
        //course
    case CreateCourse = "create_course";
    case StoreCourse = "store_course";
    case EditCourse = "edit_course";
    case UpdateCourse = "update_course";
    case DestroyCourse = "destroy_course";
    case ShowCourse = "show_course";
        //difficulty level
    case CreateDifficultyLevel = "create_difficulty_level";
    case StoreDifficultyLevel = "store_difficulty_level";
    case EditDifficultyLevel = "edit_difficulty_level";
    case UpdateDifficultyLevel = "update_difficulty_level";
    case DestroyDifficultyLevel = "destroy_difficulty_level";
    case ShowDifficultyLevel = "show_difficulty_level";
        //repost
    case StoreRepost = "store_repost";
    case DestroyRepost = "destroy_repost";
        //comment
    case CreateComment = "create_comment";
    case StoreComment = "store_comment";
    case EditComment = "edit_comment";
    case UpdateComment = "update_comment";
    case DestroyComment = "destroy_comment";
    case BanComment = "ban_comment";
        //snack
    case CreateSnack = "create_snack";
    case StoreSnack = "store_snack";
    case EditSnack = "edit_snack";
    case UpdateSnack = "update_snack";
    case DestroySnack = "destroy_snack";
    case ShowSnack = "show_snack";
    case BanSnack = "ban_snack";
        //badge
    case CreateBadge = "create_badge";
    case StoreBadge = "store_badge";
    case EditBadge = "edit_badge";
    case UpdateBadge = "update_badge";
    case DestroyBadge = "destroy_badge";
    case AssignBadge = "assign_badge";
        //favorite
    case StoreFavorite = "store_favorite";
    case DestroyFavorite = "destroy_favorite";
        //award
    case CreateAward = "create_award";
    case StoreAward = "store_award";
    case EditAward = "edit_award";
    case UpdateAward = "update_award";
    case DestroyAward = "destroy_award";
        //execution
    case CreateExecution = "create_execution";
    case StoreExecution = "store_execution";
    case EditExecution = "edit_execution";
    case UpdateExecution = "update_execution";
    case DestroyExecution = "destroy_execution";
    case BanExecution = "ban_execution";
        //execution image
    case StoreExecutionImage = "store_execution_image";
    case DestroyExecutionImage = "destroy_execution_image";
        //execution video
    case StoreExecutionVideo = "store_execution_video";
    case DestroyExecutionVideo = "destroy_execution_video";
        //user
    case BanUser = "ban_user";
    case ForgiveUser = "forgive_user";
}
