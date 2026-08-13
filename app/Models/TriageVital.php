<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TriageVital extends Model
{
    protected $guarded = [];

    protected $casts = [
        'temperature' => 'decimal:1',
        'spo2' => 'decimal:2',
        'weight' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function triageAssessment(): BelongsTo
    {
        return $this->belongsTo(TriageAssessment::class);
    }
}
