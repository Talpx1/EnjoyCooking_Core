<?php

namespace App\Models;

use App\Models\Traits\HasRandomFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Passport\Client;

class OauthClient extends Client
{
    use HasRandomFactory;

    /**
     * Determine if the client should skip the authorization prompt.
     *
     * @return bool
     */
    public function skipsAuthorization()
    {
        return $this->firstParty() || in_array($this->id, config('passport.first_party_clients'));
    }
}
