<?php

use App\Http\Controllers\AwardController;
use App\Http\Controllers\BadgeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// #############################################################
// UNAUTHENTICATED READONLY ROUTES (only accepting known origin)
// #############################################################

Route::apiResources([
    'award' => AwardController::class,
    'badge' => BadgeController::class
], ['only' => ['index', 'show']]);
