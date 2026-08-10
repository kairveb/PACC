<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BedReservation extends Model
{
    protected $guarded = [];

    protected $casts = ['expires_at' => 'datetime'];

    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_CONVERTED = 'CONVERTED';
    public const STATUS_EXPIRED = 'EXPIRED';
    public const STATUS_CANCELLED = 'CANCELLED';

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function reservedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reserved_by');
    }
}
