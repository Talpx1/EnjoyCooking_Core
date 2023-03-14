<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasRandomFactory;
use Cviebrock\EloquentSluggable\Sluggable;

class Category extends Model
{
    use HasFactory, HasRandomFactory, Sluggable;

    protected $guarded = ['id', 'created_at', 'updated_at', 'slug'];
    protected $with = ['parent'];

    public function sluggable(): array{
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    public function parent(){
        return $this->belongsTo(Category::class, 'parent_category_id');
    }

    public function subcategories(){
        return $this->hasMany(Category::class, 'parent_category_id');
    }

    public function getIsParentCategoryAttribute(){
        return is_null($this->parent_category_id);
    }

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }

    public function scopeWhereNameLike(Builder $query, string|null $name){
        if(empty($name)) return $query;

        $query->where('name', 'like', "%{$name}%");
    }
}
