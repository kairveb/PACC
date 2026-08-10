<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelehealthParticipant extends Model
{
    protected $guarded = [];

    protected $casts = ['joined_at' => 'datetime'];

    public function telehealthSession(): BelongsTo
    {
        return $this->belongsTo(TelehealthSession::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
