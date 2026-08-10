<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientAddress extends Model
{
    protected $guarded = [];

    protected $casts = ['primary' => 'boolean'];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
