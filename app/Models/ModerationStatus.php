<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasRandomFactory;

class ModerationStatus extends Model
{
    use HasFactory, HasRandomFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

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

    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function snacks()
    {
        return $this->hasMany(Snack::class);
    }

    public function executions()
    {
        return $this->hasMany(Execution::class);
    }
}
