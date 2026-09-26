<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreArrivalProfile extends Model
{
    protected $guarded = [];

    protected $casts = [
        'arrived_at' => 'datetime',
        'date_of_birth' => 'date',
        'visit_reason' => 'encrypted',
        'initial_notes' => 'encrypted',
        'medical_history' => 'encrypted',
        'current_medications' => 'encrypted',
        'allergies' => 'encrypted',
        'first_name' => 'encrypted',
        'middle_name' => 'encrypted',
        'last_name' => 'encrypted',
        'suffix' => 'encrypted',
        'sex' => 'encrypted',
        'civil_status' => 'encrypted',
        'nationality' => 'encrypted',
        'phone' => 'encrypted',
        'email' => 'encrypted',
        'emergency_name' => 'encrypted',
        'emergency_phone' => 'encrypted',
        'emergency_relationship' => 'encrypted',
        'address_line1' => 'encrypted',
        'address_barangay' => 'encrypted',
        'address_city' => 'encrypted',
        'address_province' => 'encrypted',
        'address_postal' => 'encrypted',
    ];

    public static function generateUniqueReferenceCode(): string
    {
        do {
            $suffix = random_int(1000, 9999);
            $code = sprintf('PAC-%04d', $suffix);
        } while (self::query()->where('reference_code', $code)->exists());

        return $code;
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
