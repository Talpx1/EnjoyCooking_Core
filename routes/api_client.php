<?php

use App\Http\Controllers\AwardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::apiResources([
    'award' => AwardController::class
], ['only' => ['index', 'show']]);
