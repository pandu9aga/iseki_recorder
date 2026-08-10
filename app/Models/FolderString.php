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

    public function parts(): array
    {
        return collect(preg_split('/[;|]/', $this->content ?? ''))
            ->map(fn ($part) => trim($part))
            ->filter(fn ($part) => $part !== '')
            ->values()
            ->all();
    }
}
