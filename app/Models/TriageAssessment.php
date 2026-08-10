<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TriageAssessment extends Model
{
    protected $guarded = [];

    protected $casts = ['triaged_at' => 'datetime'];

    public const PRIORITY_LEVEL1 = 'LEVEL_1';
    public const PRIORITY_LEVEL2 = 'LEVEL_2';
    public const PRIORITY_LEVEL3 = 'LEVEL_3';
    public const PRIORITY_LEVEL4 = 'LEVEL_4';
    public const PRIORITY_LEVEL5 = 'LEVEL_5';

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
}
