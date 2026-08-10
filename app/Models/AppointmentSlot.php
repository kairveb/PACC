<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentSlot extends Model
{
    protected $guarded = [];

    protected $casts = ['starts_at' => 'datetime', 'ends_at' => 'datetime'];

    public const STATUS_AVAILABLE = 'AVAILABLE';
    public const STATUS_BOOKED = 'BOOKED';
    public const STATUS_BLOCKED = 'BLOCKED';

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function appointmentType(): BelongsTo
    {
        return $this->belongsTo(AppointmentType::class);
    }
}
