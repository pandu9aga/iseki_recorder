<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RackParentFolder extends Model
{
    protected $fillable = [
        'name',
        'nik',
        'member_name',
    ];

    public function qrFolders(): HasMany
    {
        return $this->hasMany(RackQrFolder::class, 'rack_parent_folder_id');
    }

    public function uploadDir(): string
    {
        return public_path('uploads/rack/' . $this->name);
    }
}
