<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderSchedule extends Model
{
    protected $guarded = [];

    protected $casts = ['day_of_week' => 'integer', 'slot_duration' => 'integer', 'unavailable_date' => 'date'];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
