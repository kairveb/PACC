<?php

namespace Database\Seeders;

use App\Models\Admission;
use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\Department;
use App\Models\EmergencyContact;
use App\Models\ErQueue;
use App\Models\ErVisit;
use App\Models\Patient;
use App\Models\PatientAddress;
use App\Models\Permission;
use App\Models\Provider;
use App\Models\ProviderSchedule;
use App\Models\Role;
use App\Models\Room;
use App\Models\Specialty;
use App\Models\TriageAssessment;
use App\Models\TriageVital;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HimsSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedRolesAndPermissions();
        $this->seedUsers();
        $this->seedDepartmentsAndProviders();
        $this->seedPatients();
        $this->seedWardsRoomsBeds();
        $this->seedWorkflow();
    }

    protected function seedRolesAndPermissions(): void
    {
        $roles = [
            'super-admin' => 'Super Admin',
            'hospital-admin' => 'Hospital Admin',
            'registration' => 'Registration / Front Desk',
            'doctor' => 'Doctor',
            'nurse' => 'Nurse',
            'patient' => 'Patient',
        ];

        foreach ($roles as $name => $label) {
            Role::firstOrCreate(['name' => $name], ['label' => $label]);
        }

        $permissions = [
            'manage-users', 'manage-roles',
'view-patients', 'create-patients', 'update-patients', 'delete-patients', 'verify-patients',
            'view-appointments', 'create-appointments', 'update-appointments', 'cancel-appointments', 'delete-appointments', 'view-own-appointments',
            'view-encounters', 'create-encounters', 'update-encounters',
            'view-triage', 'create-triage', 'update-triage',
            'view-er', 'create-er-visits', 'triage-patients',
            'view-beds', 'manage-beds', 'view-admissions', 'manage-admissions',
            'view-reports', 'view-audit-logs',
            'view-telehealth', 'start-telehealth', 'join-telehealth',
            'view-billing', 'manage-billing',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name], ['label' => ucwords(str_replace('-', ' ', $name))]);
        }

$rolePerms = [
            'super-admin' => $permissions,
            'hospital-admin' => [
                'manage-users', 'manage-roles', 'view-patients', 'create-patients', 'update-patients', 'verify-patients',
                'view-appointments', 'create-appointments', 'update-appointments', 'cancel-appointments', 'delete-appointments',
                'view-encounters', 'create-encounters', 'update-encounters', 'view-triage', 'create-triage', 'update-triage',
                'view-er', 'create-er-visits', 'triage-patients', 'view-beds', 'manage-beds', 'view-admissions', 'manage-admissions',
                'view-reports', 'view-audit-logs', 'view-telehealth', 'start-telehealth', 'join-telehealth', 'view-billing', 'manage-billing'
            ],
            'registration' => [
                'view-patients', 'create-patients', 'update-patients', 'verify-patients',
                'view-appointments', 'create-appointments', 'update-appointments', 'cancel-appointments',
                'view-triage', 'create-triage', 'update-triage', 'view-er', 'view-billing', 'view-reports'
            ],
            'doctor' => [
                'view-patients', 'update-patients', 'view-appointments', 'update-appointments',
                'view-encounters', 'create-encounters', 'update-encounters', 'view-triage', 'create-triage', 'update-triage',
                'view-er', 'triage-patients', 'view-telehealth', 'start-telehealth', 'join-telehealth', 'view-billing', 'view-reports'
            ],
            'nurse' => [
                'view-patients', 'update-patients', 'view-appointments', 'view-encounters', 'create-encounters',
                'view-triage', 'create-triage', 'update-triage', 'view-er', 'create-er-visits', 'triage-patients',
                'view-beds', 'manage-beds', 'view-admissions', 'manage-admissions', 'view-telehealth', 'join-telehealth', 'view-billing'
            ],
            'patient' => [
                'view-own-appointments', 'view-telehealth', 'join-telehealth', 'view-billing'
            ],
        ];

        foreach ($rolePerms as $role => $perms) {
            $roleModel = Role::where('name', $role)->first();
            $roleModel->permissions()->sync(Permission::whereIn('name', $perms)->pluck('id'));
        }
    }

    protected function seedUsers(): void
    {
        $users = [
            'super-admin' => ['name' => 'Super Admin', 'email' => 'super-admin@coor.test'],
            'hospital-admin' => ['name' => 'Hospital Admin', 'email' => 'hospital-admin@coor.test'],
            'registration' => ['name' => 'Registration Staff', 'email' => 'registration@coor.test'],
            'doctor' => ['name' => 'Dr. Elena Santos', 'email' => 'doctor@coor.test'],
            'nurse' => ['name' => 'Nurse Ana Reyes', 'email' => 'nurse@coor.test'],
            'patient' => ['name' => 'Maria Santos', 'email' => 'patient@coor.test'],
        ];

        foreach ($users as $role => $userData) {
            $emailCandidates = array_unique([
                $userData['email'],
                $role . '@coor.test',
                str_replace('-', '.', $role) . '@coor.test',
            ]);

            $user = null;
            foreach ($emailCandidates as $candidate) {
                $user = User::where('email', $candidate)->first();
                if ($user) {
                    break;
                }
            }

            if (! $user) {
                $user = new User();
                $user->email = $userData['email'];
            }

            $user->fill([
                'name' => $userData['name'],
                'password' => Hash::make('Password123!'),
                'email_verified_at' => now(),
            ]);
            $user->save();

            if ($role === 'super-admin' && ! User::where('email', 'super.admin@coor.test')->exists()) {
                $extraUser = new User();
                $extraUser->name = 'Super Admin';
                $extraUser->email = 'super.admin@coor.test';
                $extraUser->password = Hash::make('Password123!');
                $extraUser->email_verified_at = now();
                $extraUser->save();
                $extraUser->roles()->syncWithoutDetaching([Role::where('name', 'super-admin')->value('id')]);
            }

            if ($role === 'hospital-admin' && ! User::where('email', 'hospital.admin@coor.test')->exists()) {
                $extraUser = new User();
                $extraUser->name = 'Hospital Admin';
                $extraUser->email = 'hospital.admin@coor.test';
                $extraUser->password = Hash::make('Password123!');
                $extraUser->email_verified_at = now();
                $extraUser->save();
                $extraUser->roles()->syncWithoutDetaching([Role::where('name', 'hospital-admin')->value('id')]);
            }

            $user->roles()->syncWithoutDetaching([Role::where('name', $role)->value('id')]);
        }
    }

    protected function seedDepartmentsAndProviders(): void
    {
        $depts = [
            ['code' => 'CAR', 'name' => 'Cardiology', 'phone' => '1234'],
            ['code' => 'PED', 'name' => 'Pediatrics', 'phone' => '2345'],
            ['code' => 'ORT', 'name' => 'Orthopedics', 'phone' => '3456'],
            ['code' => 'GEN', 'name' => 'General Medicine', 'phone' => '4567'],
            ['code' => 'EMG', 'name' => 'Emergency Medicine', 'phone' => '5678'],
        ];
        foreach ($depts as $d) {
            Department::firstOrCreate(['code' => $d['code']], ['name' => $d['name'], 'phone' => $d['phone']]);
        }

        $specs = ['Cardiology', 'Pediatrics', 'Orthopedics', 'Internal Medicine', 'Emergency Medicine'];
        foreach ($specs as $s) {
            Specialty::firstOrCreate(['name' => $s]);
        }

        $doctorUser = User::where('email', 'doctor@coor.test')->first();
        $provider = Provider::firstOrCreate(
            ['user_id' => $doctorUser->id],
            ['department_id' => Department::where('code', 'CAR')->value('id'), 'license_number' => 'LIC-001', 'display_name' => 'Dr. Elena Santos', 'active' => true]
        );
        $provider->specialties()->syncWithoutDetaching([Specialty::where('name', 'Cardiology')->value('id')]);

        ProviderSchedule::firstOrCreate(
            ['provider_id' => $provider->id, 'day_of_week' => 1],
            ['start_time' => '08:00:00', 'end_time' => '17:00:00', 'slot_duration' => 30]
        );
        ProviderSchedule::firstOrCreate(
            ['provider_id' => $provider->id, 'day_of_week' => 2],
            ['start_time' => '08:00:00', 'end_time' => '17:00:00', 'slot_duration' => 30]
        );
        ProviderSchedule::firstOrCreate(
            ['provider_id' => $provider->id, 'day_of_week' => 3],
            ['start_time' => '08:00:00', 'end_time' => '17:00:00', 'slot_duration' => 30]
        );

        AppointmentType::firstOrCreate(['name' => 'Outpatient'], ['default_duration' => 30, 'telehealth' => false]);
        AppointmentType::firstOrCreate(['name' => 'Telehealth'], ['default_duration' => 30, 'telehealth' => true]);
    }

    protected function seedPatients(): void
    {
        $patients = [
            ['mrn' => 'MRN-2026-000001', 'first_name' => 'Maria', 'last_name' => 'Santos', 'date_of_birth' => '1987-04-18', 'sex' => 'Female', 'phone' => '09171234567', 'email' => 'maria.santos@example.test', 'allergies' => 'Penicillin'],
            ['mrn' => 'MRN-2026-000002', 'first_name' => 'Juan', 'last_name' => 'Dela Cruz', 'date_of_birth' => '1975-11-02', 'sex' => 'Male', 'phone' => '09173456789', 'email' => 'juan.delacruz@example.test', 'allergies' => null],
            ['mrn' => 'MRN-2026-000003', 'first_name' => 'Liza', 'last_name' => 'Reyes', 'date_of_birth' => '1992-06-15', 'sex' => 'Female', 'phone' => '09179876543', 'email' => 'liza.reyes@example.test', 'allergies' => null],
            ['mrn' => 'MRN-2026-000004', 'first_name' => 'Pedro', 'last_name' => 'Garcia', 'date_of_birth' => '1968-02-28', 'sex' => 'Male', 'phone' => '09171239876', 'email' => 'pedro.garcia@example.test', 'allergies' => 'Latex'],
        ];

        $patientUser = User::where('email', 'patient@coor.test')->first();

        foreach ($patients as $i => $p) {
            $patient = Patient::firstOrCreate(
                ['mrn' => $p['mrn']],
                [
                    'user_id' => $i === 0 ? $patientUser->id : null,
                    'first_name' => $p['first_name'],
                    'last_name' => $p['last_name'],
                    'middle_name' => null,
                    'date_of_birth' => $p['date_of_birth'],
                    'sex' => $p['sex'],
                    'civil_status' => 'Single',
                    'nationality' => 'Filipino',
                    'phone' => $p['phone'],
                    'email' => $p['email'],
                    'allergies' => $p['allergies'],
                    'verified' => true,
                ]
            );

            PatientAddress::firstOrCreate(
                ['patient_id' => $patient->id, 'line1' => '123 Mabini St.'],
                ['city' => 'Manila', 'province' => 'Metro Manila', 'postal_code' => '1000', 'primary' => true]
            );

            EmergencyContact::firstOrCreate(
                ['patient_id' => $patient->id, 'name' => 'Emergency Contact'],
                ['relationship' => 'Spouse', 'phone' => '09170000000']
            );
        }
    }

    protected function seedWardsRoomsBeds(): void
    {
        $wards = [
            ['code' => 'MED', 'name' => 'Medical Ward', 'type' => 'General'],
            ['code' => 'PED', 'name' => 'Pediatric Ward', 'type' => 'Pediatric'],
            ['code' => 'ICU', 'name' => 'Intensive Care Unit', 'type' => 'ICU'],
        ];

        foreach ($wards as $w) {
            $ward = Ward::firstOrCreate(['code' => $w['code']], ['name' => $w['name'], 'type' => $w['type']]);

            for ($roomNum = 201; $roomNum <= 202; $roomNum++) {
                $room = Room::firstOrCreate(['ward_id' => $ward->id, 'number' => (string) $roomNum], ['type' => 'Standard']);

                foreach (['A', 'B', 'C'] as $bedLetter) {
                    Bed::firstOrCreate(['room_id' => $room->id, 'number' => $bedLetter], ['status' => 'AVAILABLE']);
                }
            }
        }
    }

    protected function seedWorkflow(): void
    {
        $patient = Patient::where('mrn', 'MRN-2026-000001')->first();
        $provider = Provider::first();
        $apptType = AppointmentType::where('name', 'Outpatient')->first();
        $dept = Department::where('code', 'CAR')->first();
        $nurse = User::where('email', 'nurse@coor.test')->first();
        $admission = User::where('email', 'admission@coor.test')->first()
            ?? User::where('email', 'hospital-admin@coor.test')->first()
            ?? $nurse;

        if (!$patient || !$provider || !$nurse) {
            return;
        }

        $createdBy = $nurse->id;
        if ($admission) {
            $createdBy = $admission->id;
        }

        Appointment::firstOrCreate(
            ['appointment_number' => 'APT-2026-000001'],
            [
                'patient_id' => $patient->id,
                'provider_id' => $provider->id,
                'department_id' => $dept?->id,
                'appointment_type_id' => $apptType?->id,
                'starts_at' => now()->addHours(2),
                'ends_at' => now()->addHours(2)->addMinutes(30),
                'status' => 'CONFIRMED',
                'reason' => 'Follow-up checkup',
                'created_by' => $createdBy,
            ]
        );

        $erPatient = Patient::where('mrn', 'MRN-2026-000002')->first();
        if ($erPatient) {
            $erVisit = ErVisit::firstOrCreate(
                ['visit_number' => 'ER-2026-000001'],
                [
                    'patient_id' => $erPatient->id,
                    'arrived_at' => now()->subHour(),
                    'arrival_method' => 'Ambulance',
                    'chief_complaint' => 'Chest pain',
                    'status' => 'TRIAGED',
                    'created_by' => $nurse->id,
                ]
            );

            $triage = TriageAssessment::firstOrCreate(
                ['er_visit_id' => $erVisit->id],
                [
                    'triage_nurse_id' => $nurse->id,
                    'triaged_at' => now()->subMinutes(50),
                    'chief_complaint' => 'Chest pain',
                    'pain_score' => 7,
                    'priority' => 'Level 2',
                    'notes' => 'Stable, monitoring',
                    'status' => 'COMPLETE',
                ]
            );

            TriageVital::firstOrCreate(
                ['triage_assessment_id' => $triage->id],
                ['blood_pressure' => '140/90', 'heart_rate' => 100, 'respiratory_rate' => 20, 'temperature' => 37.5, 'spo2' => 97]
            );

            ErQueue::firstOrCreate(
                ['er_visit_id' => $erVisit->id],
                ['priority' => 'Level 2', 'status' => 'WAITING', 'treatment_area' => 'Resuscitation', 'queued_at' => now()->subMinutes(50)]
            );
        }

        $admitPatient = Patient::where('mrn', 'MRN-2026-000003')->first();
        if ($admitPatient) {
            $admissionRec = Admission::firstOrCreate(
                ['admission_number' => 'ADM-2026-000001'],
                [
                    'patient_id' => $admitPatient->id,
                    'attending_provider_id' => $provider->id,
                    'status' => 'ADMITTED',
                    'reason' => 'Observation',
                    'admitted_at' => now()->subHours(3),
                    'created_by' => $admission->id,
                ]
            );

            $bed = Bed::where('status', 'AVAILABLE')->first();
            if ($bed) {
                BedAssignment::firstOrCreate(
                    ['admission_id' => $admissionRec->id, 'bed_id' => $bed->id],
                    ['assigned_by' => $admission->id, 'assigned_at' => now()->subHours(3), 'status' => 'ACTIVE']
                );
                $bed->update(['status' => 'OCCUPIED', 'status_updated_at' => now()]);
            }
        }
    }
}
