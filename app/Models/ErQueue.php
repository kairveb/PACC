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

    public function erVisit(): BelongsTo
    {
        return $this->belongsTo(ErVisit::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
