<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreArrivalProfile extends Model
{
    protected $guarded = [];

    protected $casts = [
        'arrived_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function isEligibleForCheckIn(): bool
    {
        return $this->status === 'pending' && blank($this->arrived_at) && ! blank($this->token) && $this->patient()->exists();
    }
}
