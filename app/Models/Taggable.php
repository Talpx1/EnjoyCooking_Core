<?php

namespace App\Models;

use App\Models\Traits\HasRandomFactory;
use App\Models\Traits\MorphCascadeOnDelete;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Taggable extends Model{
    use HasFactory, HasRandomFactory, MorphCascadeOnDelete;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    private static $morphName = 'taggable';
}
