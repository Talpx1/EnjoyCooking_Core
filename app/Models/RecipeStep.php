<?php

namespace App\Models;

use App\Models\Traits\HasRandomFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecipeStep extends Model
{
    use HasFactory, HasRandomFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }
}
