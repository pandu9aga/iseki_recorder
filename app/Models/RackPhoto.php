<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RackPhoto extends Model
{
    protected $fillable = [
        'rack_qr_folder_id',
        'photo_path',
        'filename',
    ];

    public function qrFolder(): BelongsTo
    {
        return $this->belongsTo(RackQrFolder::class, 'rack_qr_folder_id');
    }

    public function fullPath(): string
    {
        return public_path($this->photo_path);
    }

    public function url(): string
    {
        return asset($this->photo_path);
    }
}
