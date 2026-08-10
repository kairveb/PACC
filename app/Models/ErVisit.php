<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ErVisit extends Model
{
    protected $guarded = [];

    protected $casts = ['arrived_at' => 'datetime'];

    public const STATUS_ARRIVED = 'ARRIVED';
    public const STATUS_TRIAGED = 'TRIAGED';
    public const STATUS_IN_TREATMENT = 'IN_TREATMENT';
    public const STATUS_ADMITTED = 'ADMITTED';
    public const STATUS_DISCHARGED = 'DISCHARGED';

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

public function triage(): HasOne
    {
        return $this->hasOne(TriageAssessment::class);
    }

    public function triageAssessments(): HasMany
    {
        return $this->hasMany(TriageAssessment::class);
    }

    public function queue(): HasOne
    {
        return $this->hasOne(ErQueue::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
