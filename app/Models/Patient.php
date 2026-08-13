<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Patient extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = ['date_of_birth' => 'date', 'verified' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function admissions(): HasMany
    {
        return $this->hasMany(Admission::class);
    }

    public function erVisits(): HasMany
    {
        return $this->hasMany(ErVisit::class);
    }

    public function encounters(): HasMany
    {
        return $this->hasMany(Encounter::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(PatientAddress::class);
    }

    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(EmergencyContact::class);
    }

    public function identifiers(): HasMany
    {
        return $this->hasMany(PatientIdentifier::class);
    }

    public function consents(): HasMany
    {
        return $this->hasMany(PatientConsent::class);
    }

    public function vitals(): HasMany
    {
        return $this->hasMany(Vital::class);
    }

    public function triageAssessments(): HasMany
    {
        return $this->hasMany(TriageAssessment::class);
    }

    public function clinicalDocuments(): HasMany
    {
        return $this->hasMany(ClinicalDocument::class);
    }

    public function waitlists(): HasMany
    {
        return $this->hasMany(Waitlist::class);
    }

    public function primaryAddress()
    {
        return $this->addresses()->where('primary', true)->first();
    }

    public function primaryEmergencyContact()
    {
        return $this->emergencyContacts()->first();
    }

    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->suffix,
        ])));
    }

    public function ensureLookupCode(): string
    {
        if (! $this->lookup_code) {
            $this->lookup_code = strtoupper(Str::random(8));
        }

        return $this->lookup_code;
    }

    public function markPendingArrival(): void
    {
        $this->forceFill([
            'lookup_code' => $this->ensureLookupCode(),
            'pre_registration_status' => 'pending_arrival',
        ])->save();
    }

    public function getAgeAttribute(): int
    {
        return $this->date_of_birth ? $this->date_of_birth->age : 0;
    }
}
