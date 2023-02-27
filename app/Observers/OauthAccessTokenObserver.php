<?php

namespace App\Observers;

use App\Models\OauthAccessToken;

class OauthAccessTokenObserver
{
    /**
     * Handle the OauthAccessToken "created" event.
     *
     * @param  \App\Models\OauthAccessToken  $oauthAccessToken
     * @return void
     */
    public function created(OauthAccessToken $oauthAccessToken)
    {
        //
    }

    /**
     * Handle the OauthAccessToken "updated" event.
     *
     * @param  \App\Models\OauthAccessToken  $oauthAccessToken
     * @return void
     */
    public function updated(OauthAccessToken $oauthAccessToken)
    {
        //
    }

    /**
     * Handle the OauthAccessToken "deleted" event.
     *
     * @param  \App\Models\OauthAccessToken  $oauthAccessToken
     * @return void
     */
    public function deleted(OauthAccessToken $oauthAccessToken)
    {
        $oauthAccessToken->refreshTokens()->delete();
    }

    /**
     * Handle the OauthAccessToken "restored" event.
     *
     * @param  \App\Models\OauthAccessToken  $oauthAccessToken
     * @return void
     */
    public function restored(OauthAccessToken $oauthAccessToken)
    {
        //
    }

    /**
     * Handle the OauthAccessToken "force deleted" event.
     *
     * @param  \App\Models\OauthAccessToken  $oauthAccessToken
     * @return void
     */
    public function forceDeleted(OauthAccessToken $oauthAccessToken)
    {
        //
    }
}
