<?php

namespace App\Models;

use App\Models\Traits\HasRandomFactory;
use App\Models\Traits\MorphCascadeOnDelete;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory, HasRandomFactory, MorphCascadeOnDelete;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    private static $morphName = 'favoritable';

    public function favoritable(){
        return $this->morphTo();
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
