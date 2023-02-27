<?php

namespace App\Models;

use App\Models\Traits\HasRandomFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\RefreshToken;

class OauthRefreshToken extends RefreshToken
{
    use HasFactory, HasRandomFactory;
}
