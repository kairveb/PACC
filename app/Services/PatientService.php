<?php

namespace App\Services;

use App\Models\EmergencyContact;
use App\Models\Patient;
use App\Models\PatientAddress;
use App\Models\PatientIdentifier;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PatientService
{
    /**
     * Generate the next unique MRN.
     */
    public function generateMpn(): string
    {
        $year = now()->format('Y');
        $last = Patient::withTrashed()
            ->where('mrn', 'like', "MRN-{$year}-%")
            ->orderByDesc('mrn')
            ->lockForUpdate()
            ->first();

        $sequence = 1;
        if ($last) {
            $parts = explode('-', $last->mrn);
            $sequence = ((int) end($parts)) + 1;
        }

        return sprintf('MRN-%s-%06d', $year, $sequence);
    }

    /**
     * Register a new patient with address, emergency contact, and identifiers.
     */
    public function register(array $data, ?int $userId = null): Patient
    {
return DB::transaction(function () use ($data, $userId) {
            $data['mrn'] = $data['mrn'] ?? $this->generateMpn();
            $data['verified'] = $data['verified'] ?? false;

            // Strip relational keys that belong in separate tables
            $patientData = Arr::except($data, ['address', 'emergency_contact', 'identifiers']);
            $patient = Patient::create($patientData);

            if (!empty($data['address'])) {
                PatientAddress::create([
                    'patient_id' => $patient->id,
                    'line1' => $data['address']['line1'] ?? '',
                    'line2' => $data['address']['line2'] ?? null,
                    'barangay' => $data['address']['barangay'] ?? null,
                    'city' => $data['address']['city'] ?? null,
                    'province' => $data['address']['province'] ?? null,
                    'postal_code' => $data['address']['postal_code'] ?? null,
                    'primary' => true,
                ]);
            }

            if (!empty($data['emergency_contact'])) {
                EmergencyContact::create([
                    'patient_id' => $patient->id,
                    'name' => $data['emergency_contact']['name'] ?? '',
                    'relationship' => $data['emergency_contact']['relationship'] ?? '',
                    'phone' => $data['emergency_contact']['phone'] ?? '',
                ]);
            }

            if (!empty($data['identifiers'])) {
                foreach ($data['identifiers'] as $identifier) {
                    if (!empty($identifier['type']) && !empty($identifier['value'])) {
                        PatientIdentifier::create([
                            'patient_id' => $patient->id,
                            'type' => $identifier['type'],
                            'value' => $identifier['value'],
                        ]);
                    }
                }
            }

            app(AuditLogService::class)->createPatient($patient->id, ['mrn' => $patient->mrn]);

            return $patient;
        });
    }

    /**
     * Find potential duplicate patients based on name, DOB, and contact.
     */
    public function findDuplicates(array $data): array
    {
        $query = Patient::query();

        $firstName = Str::lower(trim($data['first_name'] ?? ''));
        $lastName = Str::lower(trim($data['last_name'] ?? ''));
        $dob = $data['date_of_birth'] ?? null;
        $phone = $data['phone'] ?? null;
        $email = $data['email'] ?? null;

        if ($firstName && $lastName) {
            $query->where(function ($q) use ($firstName, $lastName) {
                $q->whereRaw('LOWER(first_name) = ?', [$firstName])
                    ->whereRaw('LOWER(last_name) = ?', [$lastName]);
            });
        }

        if ($dob) {
            $query->where(function ($q) use ($dob) {
                $q->where('date_of_birth', $dob);
            });
        }

        if ($phone) {
            $query->orWhere('phone', $phone);
        }

        if ($email) {
            $query->orWhere('email', $email);
        }

        return $query->limit(10)->get()->toArray();
    }

    /**
     * Search patients by any supported criteria.
     */
    public function search(?string $term = null, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Patient::query()
            ->with(['addresses', 'emergencyContacts'])
            ->orderBy('created_at', 'desc');

        if ($term) {
            $like = '%' . $term . '%';
            $query->where(function ($q) use ($term, $like) {
                $q->where('mrn', 'like', $like)
                    ->orWhere('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('middle_name', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhere('email', 'like', $like);
            });
        }

        if (!empty($filters['date_of_birth'])) {
            $query->where('date_of_birth', $filters['date_of_birth']);
        }

        if (!empty($filters['sex'])) {
            $query->where('sex', $filters['sex']);
        }

        return $query->paginate(15);
    }

    /**
     * Update permitted patient demographics plus related records.
     */
    public function update(Patient $patient, array $data): Patient
    {
        return DB::transaction(function () use ($patient, $data) {
            $patient->update(Arr::except($data, ['address', 'emergency_contact', 'identifiers']));

            if (!empty($data['address'])) {
                $this->upsertPrimaryAddress($patient, $data['address']);
            }

            if (!empty($data['emergency_contact'])) {
                $this->upsertEmergencyContact($patient, $data['emergency_contact']);
            }

            app(AuditLogService::class)->updatePatient($patient->id);

            return $patient;
        });
    }

    protected function upsertPrimaryAddress(Patient $patient, array $addressData): void
    {
        $address = $patient->addresses()->where('primary', true)->first();

        if ($address) {
            $address->fill($addressData)->save();
            return;
        }

        PatientAddress::create(array_merge(['patient_id' => $patient->id, 'primary' => true], $addressData));
    }

    protected function upsertEmergencyContact(Patient $patient, array $contactData): void
    {
        $contact = $patient->emergencyContacts()->first();

        if ($contact) {
            $contact->fill($contactData)->save();
            return;
        }

        EmergencyContact::create(array_merge(['patient_id' => $patient->id], $contactData));
    }

    public function verify(Patient $patient, ?int $userId = null): Patient
    {
        $patient->update(['verified' => true]);
        app(AuditLogService::class)->log('VERIFY_PATIENT', 'patient', $patient->id, 'SUCCESS', null, $userId);
        return $patient;
    }
}
