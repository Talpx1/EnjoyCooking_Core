<?php

namespace App\Models;

use App\Models\Traits\HasRandomFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Passport\Token;

class OauthAccessToken extends Token
{
    use HasFactory, HasRandomFactory;

    public function refreshTokens()
    {
        return $this->hasMany(OauthRefreshToken::class, 'access_token_id');
    }

    public function oauthClient()
    {
        return $this->belongsTo(OauthClient::class, 'client_id');
    }
}
