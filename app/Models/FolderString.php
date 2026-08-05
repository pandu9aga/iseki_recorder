<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FolderString extends Model
{
    protected $fillable = ['folder_id', 'content'];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }
}
