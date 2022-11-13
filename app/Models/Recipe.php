<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasRandomFactory;
use Cviebrock\EloquentSluggable\Sluggable;

class Recipe extends Model
{
    use HasFactory, HasRandomFactory, Sluggable;

    protected $guarded = ['id', 'created_at', 'updated_at', 'slug'];

    public function sluggable(): array{
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public function videos(){
        return $this->hasMany(RecipeVideo::class);
    }

    public function difficultyLevel(){
        return $this->belongsTo(DifficultyLevel::class);
    }

    public function parentRecipe(){
        return $this->belongsTo(Recipe::class, 'parent_recipe_id');
    }

    public function childRecipes(){
        return $this->hasMany(Recipe::class, 'parent_recipe_id');
    }

    public function course(){
        return $this->belongsTo(Course::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function getHasChildrenAttribute(){
        return $this->childRecipes->count() > 0;
    }

    public function getIsChildAttribute(){
        return !is_null($this->parent_recipe_id);
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function moderationStatus(){
        return $this->belongsTo(ModerationStatus::class);
    }

    public function visibilityStatus(){
        return $this->belongsTo(VisibilityStatus::class);
    }

    public function images(){
        return $this->hasMany(RecipeImage::class);
    }

    public function steps(){
        return $this->hasMany(RecipeStep::class);
    }

    public function ingredients(){
        return $this->belongsToMany(Ingredient::class);
    }

    public function tags(){
        return $this->morphToMany(Tag::class, 'taggable');
    }

    protected static function booted(){
        static::deleting(function ($recipe) {
            Taggable::where([['taggable_id', '=', $recipe->id],['taggable_type', '=', Recipe::class]])->delete();
        });
    }
}
