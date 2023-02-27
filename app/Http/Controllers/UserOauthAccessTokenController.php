<?php

namespace App\Http\Controllers;

class UserOauthAccessTokenController extends Controller
{

    public function destroy(){
        \Auth::user()?->oauthRefreshTokens()->delete();
        \Auth::user()?->oauthAccessTokens()->delete();
        return response()->json(status:200);
    }
}
