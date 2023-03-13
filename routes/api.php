<?php

use App\Http\Controllers\AwardController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserOauthAccessTokenController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::delete('/user/oauth_access_tokens', [UserOauthAccessTokenController::class, 'destroy'])->name('user.access_tokens.destroy');
Route::get('/user/current', [UserController::class, 'current'])->name('user.current');

Route::apiResources([
    'award' => AwardController::class,
    'badge' => BadgeController::class,
    'category' => CategoryController::class,
], ['except' => ['index', 'show']]);

