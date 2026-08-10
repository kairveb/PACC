<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TelehealthSession extends Model
{
    protected $guarded = [];

    protected $casts = [
        'start_time' => 'datetime',
    ];

    public const STATUS_NOT_CONFIGURED = 'NOT_CONFIGURED';
    public const STATUS_SCHEDULED = 'SCHEDULED';
    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_CANCELLED = 'CANCELLED';

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(TelehealthParticipant::class);
    }
}
