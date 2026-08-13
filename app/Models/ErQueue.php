<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ErQueue extends Model
{
    protected $table = 'er_queue';

    protected $guarded = [];

    protected $casts = ['queued_at' => 'datetime'];

    public const STATUS_WAITING = 'WAITING';
    public const STATUS_IN_TREATMENT = 'IN_TREATMENT';
    public const STATUS_DONE = 'DONE';

    public static function priorityRank(string $priority): int
    {
        return match (strtolower(trim($priority))) {
            'level 1', 'emergency', 'red' => 1,
            'level 2', 'urgent', 'yellow' => 2,
            'level 3', 'prompt', 'orange' => 3,
            'level 4', 'non-urgent', 'green' => 4,
            'level 5', 'routine' => 5,
            default => 99,
        };
    }

    public function getPriorityRankAttribute(): int
    {
        return self::priorityRank((string) ($this->priority ?? ''));
    }

    public function erVisit(): BelongsTo
    {
        return $this->belongsTo(ErVisit::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
