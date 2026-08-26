<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrTimer extends Model
{
    protected $fillable = [
        'nik',
        'member_name',
        'qr_code',
        'start_time',
        'end_time',
        'duration_seconds',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration_seconds' => 'integer',
    ];

    /**
     * Format duration into readable string.
     */
    public function getFormattedDurationAttribute(): string
    {
        if ($this->duration_seconds === null) {
            return '-';
        }

        $seconds = $this->duration_seconds;
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;

        $parts = [];
        if ($hours > 0) {
            $parts[] = "{$hours} jam";
        }
        if ($minutes > 0 || $hours > 0) {
            $parts[] = "{$minutes} mnt";
        }
        $parts[] = "{$remainingSeconds} dtk";

        return implode(' ', $parts);
    }

    /**
     * Format duration as HH:MM:SS
     */
    public function getDurationClockAttribute(): string
    {
        if ($this->duration_seconds === null) {
            return '--:--:--';
        }

        $hours = str_pad((string) floor($this->duration_seconds / 3600), 2, '0', STR_PAD_LEFT);
        $minutes = str_pad((string) floor(($this->duration_seconds % 3600) / 60), 2, '0', STR_PAD_LEFT);
        $seconds = str_pad((string) ($this->duration_seconds % 60), 2, '0', STR_PAD_LEFT);

        return "{$hours}:{$minutes}:{$seconds}";
    }
}
