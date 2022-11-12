<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Traits\HasRandomFactory;

class User extends Authenticatable {
    use HasApiTokens, HasFactory, Notifiable, HasRoles, HasRandomFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
    ];

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
}
