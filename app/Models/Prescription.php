<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prescription extends Model
{
    protected $guarded = [];

    protected $casts = [
        'prescribed_at' => 'datetime',
    ];

    public function telehealthSession(): BelongsTo
    {
        return $this->belongsTo(TelehealthSession::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function prescribedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prescribed_by');
    }
}
