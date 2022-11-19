<?php

namespace App\Models;


use App\Models\Traits\MorphCascadeOnDelete;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasRandomFactory;

class Repost extends Model{
    use HasFactory, HasRandomFactory, MorphCascadeOnDelete;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    private static $morphName = 'repostable';

    public function repostable(){
        return $this->morphTo();
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
