<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RackQrFolder extends Model
{
    protected $fillable = [
        'rack_parent_folder_id',
        'qr_code',
    ];

    public function parentFolder(): BelongsTo
    {
        return $this->belongsTo(RackParentFolder::class, 'rack_parent_folder_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(RackPhoto::class, 'rack_qr_folder_id');
    }

    public function uploadDir(): string
    {
        $sanitizedQr = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $this->qr_code);
        return $this->parentFolder->uploadDir() . '/' . $sanitizedQr;
    }
}
