<?php

use App\Http\Controllers\AwardController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// #############################################################
// UNAUTHENTICATED READONLY ROUTES (only accepting known origin)
// #############################################################

Route::get('category/first-level', [CategoryController::class, 'firstLevel'])->name('category.first_level');
Route::get('category/{category}/subcategories', [CategoryController::class, 'subcategories'])->name('category.subcategories');

Route::apiResources([
    'award' => AwardController::class,
    'badge' => BadgeController::class,
    'category' => CategoryController::class,
], ['only' => ['index', 'show']]);

