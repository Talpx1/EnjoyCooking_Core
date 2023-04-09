<?php

use App\Http\Controllers\AwardController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DifficultyLevelController;
use App\Http\Controllers\GenderController;
use Illuminate\Support\Facades\Route;

// #############################################################
// UNAUTHENTICATED READONLY ROUTES (only accepting known origin)
// #############################################################

//category
Route::get('category/first-level', [CategoryController::class, 'firstLevel'])->name('category.first_level');
Route::get('category/{category}/subcategories', [CategoryController::class, 'subcategories'])->name('category.subcategories');

//gender
Route::get('gender', [GenderController::class, 'index'])->name('gender.index');

//all
Route::apiResources([
    'award' => AwardController::class,
    'badge' => BadgeController::class,
    'category' => CategoryController::class,
    'course' => CourseController::class,
    'difficulty_level' => DifficultyLevelController::class,
], ['only' => ['index', 'show']]);

