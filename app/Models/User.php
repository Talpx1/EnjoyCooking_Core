<?php

namespace App\Models;

use App\Models\Traits\NotifyDeletionToMorphs;
use App\Utils\ImageUtils;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\HasApiTokens;
use Laravel\Passport\Passport;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Traits\HasRandomFactory;

class User extends Authenticatable {
    use HasApiTokens, HasFactory, Notifiable, HasRoles, HasRandomFactory, NotifyDeletionToMorphs;


    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'email_verified_at',
        'remember_token'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $with = ['roles'];
    protected $appends = ['permissions_list'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'banned' => 'boolean',
    ];

    private static $morphs = [Follow::class];

    public function reposts()
    {
        return $this->hasMany(Repost::class);
    }

    public function type()
    {
        return $this->belongsTo(UserType::class, 'user_type_id');
    }

    public function gender()
    {
        return $this->belongsTo(Gender::class);
    }

    public function professionGroup()
    {
        return $this->belongsTo(ProfessionGroup::class);
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class);
    }

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }

    public function ingredients()
    {
        return $this->hasMany(Ingredient::class);
    }

    public function ingredientImages()
    {
        return $this->hasMany(IngredientImage::class);
    }

    public function ingredientVideos()
    {
        return $this->hasMany(IngredientVideo::class);
    }

    public function snacks()
    {
        return $this->hasMany(Snack::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function tags(){
        return $this->morphToMany(Award::class, 'awardable');
    }

    public function followers(){
        return $this->morphMany(Follow::class, 'followable');
    }

    public function follows(){
        return $this->hasMany(Follow::class);
    }

    public function favorites(){
        return $this->hasMany(Favorite::class);
    }

    public function executions(){
        return $this->hasMany(Execution::class);
    }

    public function oauthAccessTokens(){
        return $this->hasMany(OauthAccessToken::class);
    }

    public function oauthRefreshTokens()
    {
        return $this->through('oauthAccessTokens')->has('refreshTokens');
    }

    public function getPermissionsListAttribute(){
        return $this->getAllPermissions()->pluck('name');
    }

    public function getImagePathsAttribute(){
        return ImageUtils::getMultipleExtensionsPathsCollection(config('upload.user.save_as'), $this->image_path, config('upload.user.disk'));
    }

    public function getImageUrlsAttribute(){
        return ImageUtils::getMultipleExtensionsUrlsCollection(config('upload.user.save_as'), $this->image_path, config('upload.user.disk'));
    }

    public function getImagesAttribute(){
        return ImageUtils::getMultipleExtensionsBase64EncodedCollection(config('upload.user.save_as'), $this->image_path, config('upload.user.disk'));
    }
}
