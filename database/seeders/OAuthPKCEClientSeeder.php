<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Laravel\Passport\Passport;

class OAuthPKCEClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Artisan::call('passport:install');
        (Passport::clientModel())::create([
            'user_id' => null,
            'secret' => null,
            'provider' => null,
            'name' => 'Enjoy_Cooking_Core PKCE Grant Client',
            'redirect' => config('passport.ec_frontend_auth_callback_url'),
            'personal_access_client' => 0,
            'password_client' => 0,
            'revoked' => 0,
        ]);
    }
}
