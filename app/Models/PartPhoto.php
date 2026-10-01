<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartPhoto extends Model
{
    protected $fillable = [
        'name',
        'photo_path',
    ];
}
