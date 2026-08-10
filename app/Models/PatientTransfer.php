<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientTransfer extends Model
{
    protected $guarded = [];

    protected $casts = ['transferred_at' => 'datetime'];

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function fromBed(): BelongsTo
    {
        return $this->belongsTo(Bed::class, 'from_bed_id');
    }

    public function toBed(): BelongsTo
    {
        return $this->belongsTo(Bed::class, 'to_bed_id');
    }

    public function transferredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }
}
