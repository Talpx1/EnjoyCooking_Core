<?php

namespace App\Enums;

use App\Enums\Traits\EnumAsArray;

enum Permissions: string {

    use EnumAsArray;

        //recipe
    case CREATE_RECIPE = "create_recipe";
    case STORE_RECIPE = "store_recipe";
    case EDIT_RECIPE = "edit_recipe";
    case INDEX_RECIPE = "index_recipe";
    case UPDATE_RECIPE = "update_recipe";
    case DESTROY_RECIPE = "destroy_recipe";
    case SHOW_RECIPE = "show_recipe";
    case APPROVE_RECIPE = "approve_recipe";
    case REJECT_RECIPE = "reject_recipe";
        //recipe image
    case STORE_RECIPE_IMAGE = "store_recipe_image";
    case DESTROY_RECIPE_IMAGE = "destroy_recipe_image";
        //recipe video
    case STORE_RECIPE_VIDEO = "store_recipe_video";
    case DESTROY_RECIPE_VIDEO = "destroy_recipe_video";
        // ingredient
    case CREATE_INGREDIENT = "create_ingredient";
    case STORE_INGREDIENT = "store_ingredient";
    case EDIT_INGREDIENT = "edit_ingredient";
    case INDEX_INGREDIENT = "index_ingredient";
    case UPDATE_INGREDIENT = "update_ingredient";
    case DESTROY_INGREDIENT = "destroy_ingredient";
    case SHOW_INGREDIENT = "show_ingredient";
    case APPROVE_INGREDIENT = "approve_ingredient";
    case REJECT_INGREDIENT = "reject_ingredient";
        //ingredient image
    case STORE_INGREDIENT_IMAGE = "store_ingredient_image";
    case DESTROY_INGREDIENT_IMAGE = "destroy_ingredient_image";
    case APPROVE_INGREDIENT_IMAGE = "approve_ingredient_image";
    case REJECT_INGREDIENT_IMAGE = "reject_ingredient_image";
        //ingredient video
    case STORE_INGREDIENT_VIDEO = "store_ingredient_video";
    case DESTROY_INGREDIENT_VIDEO = "destroy_ingredient_video";
    case APPROVE_INGREDIENT_VIDEO = "approve_ingredient_video";
    case REJECT_INGREDIENT_VIDEO = "reject_ingredient_video";
        //tag
    case CREATE_TAG = "create_tag";
    case STORE_TAG = "store_tag";
    case EDIT_TAG = "edit_tag";
    case INDEX_TAG = "index_tag";
    case UPDATE_TAG = "update_tag";
    case DESTROY_TAG = "destroy_tag";
    case SHOW_TAG = "show_tag";
    case APPROVE_TAG = "approve_tag";
    case REJECT_TAG = "reject_tag";
        //category
    case CREATE_CATEGORY = "create_category";
    case STORE_CATEGORY = "store_category";
    case EDIT_CATEGORY = "edit_category";
    case INDEX_CATEGORY = "index_category";
    case UPDATE_CATEGORY = "update_category";
    case DESTROY_CATEGORY = "destroy_category";
    case SHOW_CATEGORY = "show_category";
        //course
    case CREATE_COURSE = "create_course";
    case STORE_COURSE = "store_course";
    case EDIT_COURSE = "edit_course";
    case INDEX_COURSE = "index_course";
    case UPDATE_COURSE = "update_course";
    case DESTROY_COURSE = "destroy_course";
    case SHOW_COURSE = "show_course";
        //difficulty level
    case CREATE_DIFFICULTY_LEVEL = "create_difficulty_level";
    case STORE_DIFFICULTY_LEVEL = "store_difficulty_level";
    case EDIT_DIFFICULTY_LEVEL = "edit_difficulty_level";
    case INDEX_DIFFICULTY_LEVEL = "index_difficulty_level";
    case UPDATE_DIFFICULTY_LEVEL = "update_difficulty_level";
    case DESTROY_DIFFICULTY_LEVEL = "destroy_difficulty_level";
    case SHOW_DIFFICULTY_LEVEL = "show_difficulty_level";
        //repost
    case STORE_REPOST = "store_repost";
    case DESTROY_REPOST = "destroy_repost";
        //comment
    case CREATE_COMMENT = "create_comment";
    case STORE_COMMENT = "store_comment";
    case EDIT_COMMENT = "edit_comment";
    case INDEX_COMMENT = "index_comment";
    case UPDATE_COMMENT = "update_comment";
    case DESTROY_COMMENT = "destroy_comment";
    case BAN_COMMENT = "ban_comment";
        //snack
    case CREATE_SNACK = "create_snack";
    case STORE_SNACK = "store_snack";
    case EDIT_SNACK = "edit_snack";
    case INDEX_SNACK = "index_snack";
    case UPDATE_SNACK = "update_snack";
    case DESTROY_SNACK = "destroy_snack";
    case SHOW_SNACK = "show_snack";
    case BAN_SNACK = "ban_snack";
        //badge
    case CREATE_BADGE = "create_badge";
    case STORE_BADGE = "store_badge";
    case EDIT_BADGE = "edit_badge";
    case INDEX_BADGE = "index_badge";
    case UPDATE_BADGE = "update_badge";
    case DESTROY_BADGE = "destroy_badge";
    case ASSIGN_BADGE = "assign_badge";
        //favorite
    case STORE_FAVORITE = "store_favorite";
    case DESTROY_FAVORITE = "destroy_favorite";
        //award
    case CREATE_AWARD = "create_award";
    case STORE_AWARD = "store_award";
    case EDIT_AWARD = "edit_award";
    case INDEX_AWARD = "index_award";
    case UPDATE_AWARD = "update_award";
    case DESTROY_AWARD = "destroy_award";
        //execution
    case CREATE_EXECUTION = "create_execution";
    case STORE_EXECUTION = "store_execution";
    case EDIT_EXECUTION = "edit_execution";
    case INDEX_EXECUTION = "index_execution";
    case UPDATE_EXECUTION = "update_execution";
    case DESTROY_EXECUTION = "destroy_execution";
    case BAN_EXECUTION = "ban_execution";
        //execution image
    case STORE_EXECUTION_IMAGE = "store_execution_image";
    case DESTROY_EXECUTION_IMAGE = "destroy_execution_image";
        //execution video
    case STORE_EXECUTION_VIDEO = "store_execution_video";
    case DESTROY_EXECUTION_VIDEO = "destroy_execution_video";
        //user
    case CREATE_USER = "create_user";
    case STORE_USER = "store_user";
    case SHOW_USER = "show_user";
    case EDIT_USER = "edit_user";
    case INDEX_USER = "index_user";
    case UPDATE_USER = "update_user";
    case DESTROY_USER = "destroy_user";
    case LOGIN = "login";
    case BAN_USER = "ban_user";
    case FORGIVE_USER = "forgive_user";
        //gender
    case CREATE_GENDER = "create_gender";
    case STORE_GENDER = "store_gender";
    case EDIT_GENDER = "edit_gender";
    case INDEX_GENDER = "index_gender";
    case UPDATE_GENDER = "update_gender";
    case DESTROY_GENDER = "destroy_gender";
    case SHOW_GENDER = "show_gender";
}
