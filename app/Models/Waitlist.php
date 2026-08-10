<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Waitlist extends Model
{
    protected $guarded = [];

    protected $casts = ['preferred_date' => 'date'];

    public const STATUS_WAITING = 'WAITING';
    public const STATUS_OFFERED = 'OFFERED';
    public const STATUS_BOOKED = 'BOOKED';
    public const STATUS_REMOVED = 'REMOVED';

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function appointmentType(): BelongsTo
    {
        return $this->belongsTo(AppointmentType::class);
    }
}
