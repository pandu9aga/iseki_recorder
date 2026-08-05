<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Folder extends Model
{
    protected $fillable = ['nama', 'nik', 'tanggal'];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function photos(): HasMany
    {
        return $this->hasMany(FolderPhoto::class);
    }

    public function strings(): HasMany
    {
        return $this->hasMany(FolderString::class);
    }

    public function uploadDir(): string
    {
        return public_path('uploads/'.$this->id);
    }

    public function photoPath(FolderPhoto $photo): string
    {
        return $this->photoPathFromName($photo->filename);
    }

    public function photoPathFromName(string $filename): string
    {
        return $this->uploadDir().DIRECTORY_SEPARATOR.$filename;
    }

    public function photoUrl(FolderPhoto $photo): string
    {
        return asset('uploads/'.$this->id.'/'.$photo->filename);
    }
}
