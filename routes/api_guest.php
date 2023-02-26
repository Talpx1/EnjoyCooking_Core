<?php

use App\Http\Controllers\AwardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// #############################################################
// UNAUTHENTICATED READONLY ROUTES (only accepting known origin)
// #############################################################

Route::apiResources([
    'award' => AwardController::class
], ['only' => ['index', 'show']]);
