<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FolderPhoto extends Model
{
    protected $fillable = ['folder_id', 'filename'];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }
}
