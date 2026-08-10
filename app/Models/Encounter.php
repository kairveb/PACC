<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Encounter extends Model
{
    protected $guarded = [];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'follow_up_date' => 'date',
    ];

    public const TYPE_OUTPATIENT = 'OUTPATIENT';
    public const TYPE_TELEHEALTH = 'TELEHEALTH';
    public const TYPE_EMERGENCY = 'EMERGENCY';

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(EncounterNote::class);
    }

    public function vitals(): HasMany
    {
        return $this->hasMany(Vital::class);
    }
}

