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

    public static function generateUniqueReferenceCode(): string
    {
        $prefixes = ['PAC', 'REF', 'HIM'];

        do {
            $prefix = $prefixes[array_rand($prefixes)];
            $suffix = random_int(1000, 9999);
            $code = sprintf('%s-%04d', $prefix, $suffix);
        } while (self::query()->where('reference_code', $code)->exists());

        return strtoupper($code);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function isEligibleForCheckIn(): bool
    {
        return $this->status === 'pending' && blank($this->arrived_at) && ! blank($this->token) && $this->patient()->exists();
    }
}
