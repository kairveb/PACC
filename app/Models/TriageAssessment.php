<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TriageAssessment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'triaged_at' => 'datetime',
        'symptoms' => 'array',
        'risk_factors' => 'array',
    ];

    public const PRIORITY_LEVEL1 = 'LEVEL_1';
    public const PRIORITY_LEVEL2 = 'LEVEL_2';
    public const PRIORITY_LEVEL3 = 'LEVEL_3';
    public const PRIORITY_LEVEL4 = 'LEVEL_4';
    public const PRIORITY_LEVEL5 = 'LEVEL_5';

    public const COLOR_RED = 'red';
    public const COLOR_YELLOW = 'yellow';
    public const COLOR_ORANGE = 'orange';
    public const COLOR_GREEN = 'green';

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function erVisit(): BelongsTo
    {
        return $this->belongsTo(ErVisit::class);
    }

    public function triageNurse(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triage_nurse_id');
    }

    public function vitals(): HasOne
    {
        return $this->hasOne(TriageVital::class);
    }

    public function triageVital(): HasOne
    {
        return $this->hasOne(TriageVital::class);
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ((int) ($this->priority_score ?? 0)) {
            1 => 'Emergency',
            2 => 'Urgent',
            3 => 'Prompt',
            4 => 'Non-Urgent',
            default => 'Routine',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match ((int) ($this->priority_score ?? 0)) {
            1 => self::COLOR_RED,
            2 => self::COLOR_YELLOW,
            3 => self::COLOR_ORANGE,
            default => self::COLOR_GREEN,
        };
    }
}
