<?php

namespace App\Http\Controllers;

class UserOauthAccessTokenController extends Controller
{

    public function destroy(){
        if(!\Auth::check()) abort(401);

        \Auth::user()->oauthRefreshTokens()->delete();
        \Auth::user()->oauthAccessTokens()->delete();

        return response()->json(status:200);
    }
}
