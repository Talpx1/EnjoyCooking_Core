<?php

use App\Http\Controllers\AwardController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// #############################################################
// UNAUTHENTICATED READONLY ROUTES (only accepting known origin)
// #############################################################

Route::apiResources([
    'award' => AwardController::class,
    'badge' => BadgeController::class,
    'category' => CategoryController::class,
], ['only' => ['index', 'show']]);

//TODO: /category/id_or_slug/subcategories
