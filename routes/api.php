<?php

use App\Http\Controllers\AwardController;
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

Route::apiResources([
    'award' => AwardController::class
], ['except' => ['index', 'show']]);

Route::delete('/user/oauth_access_tokens', [UserOauthAccessTokenController::class, 'destroy'])->name('user.access_tokens.destroy');
