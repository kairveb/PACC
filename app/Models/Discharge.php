<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Discharge extends Model
{
    protected $guarded = [];

    protected $casts = ['discharged_at' => 'datetime'];

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function authorizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }
}
