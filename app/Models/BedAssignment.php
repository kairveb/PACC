<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BedAssignment extends Model
{
    protected $guarded = [];

    protected $casts = ['assigned_at' => 'datetime', 'released_at' => 'datetime'];

    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_RELEASED = 'RELEASED';

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
