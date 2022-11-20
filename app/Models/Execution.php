<?php

namespace App\Models;

use App\Models\Traits\HasRandomFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Execution extends Model
{
    use HasFactory, HasRandomFactory;

    public function recipe(){
        return $this->belongsTo(Recipe::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
