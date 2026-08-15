<?php

use App\Http\Controllers\Api\V1\ApiAuthController;
use App\Http\Controllers\Api\V1\AdmissionApiController;
use App\Http\Controllers\Api\V1\AppointmentApiController;
use App\Http\Controllers\Api\V1\BedApiController;
use App\Http\Controllers\Api\V1\ClinicalDocumentApiController;
use App\Http\Controllers\Api\V1\DepartmentApiController;
use App\Http\Controllers\Api\V1\DischargeApiController;
use App\Http\Controllers\Api\V1\EmergencyApiController;
use App\Http\Controllers\Api\V1\EncounterApiController;
use App\Http\Controllers\Api\V1\EncounterNoteApiController;
use App\Http\Controllers\Api\V1\NotificationApiController;
use App\Http\Controllers\Api\V1\PatientApiController;
use App\Http\Controllers\Api\V1\ProviderApiController;
use App\Http\Controllers\Api\V1\ProviderScheduleApiController;
use App\Http\Controllers\Api\V1\RoomApiController;
use App\Http\Controllers\Api\V1\ScheduleApiController;
use App\Http\Controllers\Api\V1\TelehealthApiController;
use App\Http\Controllers\Api\V1\TelehealthParticipantApiController;
use App\Http\Controllers\Api\V1\TransferApiController;
use App\Http\Controllers\Api\V1\TriageApiController;
use App\Http\Controllers\Api\V1\WaitlistApiController;
use App\Http\Controllers\Api\V1\WardApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (v1)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Public auth
    Route::middleware('throttle:10,1')->group(function () {
        Route::post('/auth/login', [ApiAuthController::class, 'login']);
    });

    Route::get('/address-data/philippines', function () {
        return response()->json([
            'success' => true,
            'data' => [
                'provinces' => \App\Support\PhilippineAddressData::provinces(),
            ],
        ]);
    });

    Route::middleware('auth:sanctum')->get('/dashboard', function (\Illuminate\Http\Request $request) {
        $user = $request->user();
        $role = $user->roles->first()?->name ?? 'guest';

        $summary = [
            'appointments_today' => \App\Models\Appointment::whereDate('starts_at', today())->count(),
            'patients_today' => \App\Models\Patient::whereDate('created_at', today())->count(),
            'er_patients' => \App\Models\ErVisit::whereDate('arrived_at', today())->count(),
            'available_beds' => \App\Models\Bed::where('status', \App\Models\Bed::STATUS_AVAILABLE)->count(),
            'occupied_beds' => \App\Models\Bed::where('status', \App\Models\Bed::STATUS_OCCUPIED)->count(),
            'telehealth_appointments' => \App\Models\TelehealthSession::whereDate('start_time', today())->count(),
            'follow_up_due' => \App\Models\Encounter::whereNotNull('follow_up_date')->whereDate('follow_up_date', '<=', now()->addDays(7)->toDateString())->count(),
        ];

        if ($role === 'doctor') {
            $summary['appointments_today'] = \App\Models\Appointment::whereDate('starts_at', today())->count();
            $summary['patients_today'] = max(
                \App\Models\Patient::whereDate('created_at', today())->count(),
                \App\Models\Encounter::whereDate('started_at', today())->count()
            );
            $summary['follow_up_due'] = \App\Models\Encounter::whereNotNull('follow_up_date')->whereDate('follow_up_date', '<=', now()->addDays(7)->toDateString())->count();
        }

        if ($role === 'nurse') {
            $summary['triage_queue'] = \App\Models\ErQueue::where('status', \App\Models\ErQueue::STATUS_WAITING)->count();
            $summary['admissions_pending'] = \App\Models\Admission::whereIn('status', [\App\Models\Admission::STATUS_REQUESTED, \App\Models\Admission::STATUS_APPROVED])->count();
        }

        if ($role === 'registration') {
            $summary['registration_queue'] = \App\Models\Appointment::whereDate('starts_at', today())->count();
            $summary['checked_in_today'] = \App\Models\Appointment::whereDate('starts_at', today())->where('status', 'CHECKED_IN')->count();
        }

        if ($role === 'patient') {
            $summary['upcoming_appointments'] = \App\Models\Appointment::whereDate('starts_at', '>=', today())->count();
            $summary['unread_notifications'] = 2;
        }

        $payload = [
            'success' => true,
            'message' => 'Dashboard data retrieved successfully.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $role,
                    'roles' => $user->roles->pluck('name')->values(),
                ],
                'overview' => [
                    'today_patients' => \App\Models\Patient::whereDate('created_at', today())->count(),
                    'today_appointments' => \App\Models\Appointment::whereDate('starts_at', today())->count(),
                    'er_patients' => \App\Models\ErVisit::whereDate('arrived_at', today())->count(),
                    'available_beds' => \App\Models\Bed::where('status', \App\Models\Bed::STATUS_AVAILABLE)->count(),
                    'occupied_beds' => \App\Models\Bed::where('status', \App\Models\Bed::STATUS_OCCUPIED)->count(),
                    'telehealth_appointments' => \App\Models\TelehealthSession::whereDate('start_time', today())->count(),
                ],
                'summary' => $summary,
                'items' => [
                    'appointments' => $role === 'doctor' ? \App\Models\Appointment::with('patient')->whereDate('starts_at', today())->limit(5)->get()->map(fn ($appointment) => [
                        'id' => $appointment->id,
                        'title' => $appointment->patient?->full_name ?? 'Patient',
                        'detail' => $appointment->provider?->user?->name ?? 'Provider',
                        'time' => $appointment->starts_at?->format('h:i A'),
                        'module' => 'Appointment',
                    ]) : ($role === 'registration' ? \App\Models\Appointment::with(['patient', 'provider'])->whereDate('starts_at', today())->limit(5)->get()->map(fn ($appointment) => [
                        'id' => $appointment->id,
                        'title' => $appointment->patient?->full_name ?? 'Patient',
                        'detail' => $appointment->provider?->user?->name ?? 'Provider',
                        'time' => $appointment->starts_at?->format('h:i A'),
                        'module' => 'Registration',
                    ]) : []),
                    'encounters' => $role === 'doctor' ? \App\Models\Encounter::with('patient')->orderBy('started_at', 'desc')->limit(3)->get()->map(fn ($encounter) => [
                        'id' => $encounter->id,
                        'title' => $encounter->patient?->full_name ?? 'Patient',
                        'detail' => 'Encounter updated',
                        'time' => $encounter->started_at?->format('M d, Y'),
                        'module' => 'Encounter',
                    ]) : [],
                    'queue' => $role === 'nurse' ? \App\Models\ErQueue::with(['erVisit.patient'])->where('status', \App\Models\ErQueue::STATUS_WAITING)->orderBy('queued_at')->limit(4)->get()->map(fn ($queueItem) => [
                        'title' => $queueItem->erVisit?->patient?->full_name ?? 'Patient',
                        'detail' => 'Awaiting triage',
                        'time' => $queueItem->queued_at?->format('h:i A'),
                        'module' => 'ER',
                    ]) : ($role === 'patient' ? \App\Models\Appointment::with(['patient', 'provider'])->whereDate('starts_at', '>=', today())->orderBy('starts_at')->limit(3)->get()->map(fn ($appointment) => [
                        'title' => $appointment->provider?->user?->name ?? 'Care Team',
                        'detail' => $appointment->starts_at?->format('M d, Y h:i A'),
                        'time' => 'Upcoming',
                        'module' => 'Care',
                    ]) : []),
                    'admissions' => $role === 'nurse' ? \App\Models\Admission::with('patient')->whereIn('status', [\App\Models\Admission::STATUS_REQUESTED, \App\Models\Admission::STATUS_APPROVED])->orderBy('created_at', 'desc')->limit(4)->get()->map(fn ($admission) => [
                        'title' => $admission->patient?->full_name ?? 'Patient',
                        'detail' => 'Admission pending review',
                        'time' => $admission->created_at?->format('M d'),
                        'module' => 'Admission',
                    ]) : [],
                    'patient_count' => $role === 'doctor' ? \App\Models\Encounter::whereDate('started_at', today())->count() : 0,
                ],
            ],
        ];

        return response()->json($payload);
    });

    // Authenticated routes
    Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {
        Route::post('/auth/logout', [ApiAuthController::class, 'logout']);
        Route::get('/auth/me', [ApiAuthController::class, 'me']);

        // Patients
        Route::middleware(['can:view-patients', 'throttle:60,1'])->group(function () {
            Route::get('/patients', [PatientApiController::class, 'index']);
            Route::get('/patients/{id}', [PatientApiController::class, 'show']);
            Route::get('/patients/search/{term}', [PatientApiController::class, 'search']);
        });
        Route::middleware(['can:create-patients', 'throttle:30,1'])->group(function () {
            Route::post('/patients', [PatientApiController::class, 'store']);
        });
        Route::middleware(['can:update-patients', 'throttle:30,1'])->group(function () {
            Route::put('/patients/{id}', [PatientApiController::class, 'update']);
            Route::patch('/patients/{id}', [PatientApiController::class, 'update']);
        });
        Route::middleware(['can:delete-patients', 'throttle:10,1'])->group(function () {
            Route::delete('/patients/{id}', [PatientApiController::class, 'destroy']);
        });

        // Providers / Departments / Schedules
        Route::middleware(['can:view-appointments'])->group(function () {
            Route::get('/providers', [ProviderApiController::class, 'index']);
            Route::get('/providers/{id}', [ProviderApiController::class, 'show']);
            Route::get('/departments', [DepartmentApiController::class, 'index']);
            Route::get('/departments/{id}', [DepartmentApiController::class, 'show']);
            Route::get('/schedules', [ScheduleApiController::class, 'index']);
            Route::get('/schedules/{providerId}/slots', [ScheduleApiController::class, 'slots']);
        });

        // Appointments
        Route::middleware(['can:view-appointments', 'throttle:60,1'])->group(function () {
            Route::get('/appointments', [AppointmentApiController::class, 'index']);
            Route::get('/appointments/{id}', [AppointmentApiController::class, 'show']);
        });
        Route::middleware(['can:create-appointments', 'throttle:30,1'])->group(function () {
            Route::post('/appointments', [AppointmentApiController::class, 'store']);
        });
        Route::middleware(['can:update-appointments', 'throttle:30,1'])->group(function () {
            Route::patch('/appointments/{id}', [AppointmentApiController::class, 'update']);
        });
        Route::middleware(['can:cancel-appointments', 'throttle:30,1'])->group(function () {
            Route::delete('/appointments/{id}', [AppointmentApiController::class, 'destroy']);
            Route::post('/appointments/{id}/cancel', [AppointmentApiController::class, 'cancel']);
            Route::post('/appointments/{id}/reschedule', [AppointmentApiController::class, 'reschedule']);
            Route::post('/appointments/{id}/check-in', [AppointmentApiController::class, 'checkIn']);
            Route::post('/appointments/{id}/no-show', [AppointmentApiController::class, 'markNoShow']);
        });

        // Encounters
        Route::middleware(['can:view-encounters', 'throttle:60,1'])->group(function () {
            Route::get('/encounters', [EncounterApiController::class, 'index']);
            Route::get('/encounters/{id}', [EncounterApiController::class, 'show']);
        });
        Route::middleware(['can:create-encounters', 'throttle:30,1'])->group(function () {
            Route::post('/encounters', [EncounterApiController::class, 'store']);
        });

        // Telehealth
        Route::middleware(['can:view-telehealth', 'throttle:60,1'])->group(function () {
            Route::get('/telehealth', [TelehealthApiController::class, 'index']);
            Route::get('/telehealth/{id}', [TelehealthApiController::class, 'show']);
            Route::get('/telehealth/{id}/participants', [TelehealthParticipantApiController::class, 'index']);
        });
        Route::middleware(['can:join-telehealth', 'throttle:30,1'])->group(function () {
            Route::post('/telehealth/{id}/participants', [TelehealthParticipantApiController::class, 'store']);
        });
        Route::middleware(['can:start-telehealth', 'throttle:30,1'])->group(function () {
            Route::post('/telehealth', [TelehealthApiController::class, 'store']);
            Route::post('/telehealth/{id}/start', [TelehealthApiController::class, 'start']);
            Route::post('/telehealth/{id}/prescription', [TelehealthApiController::class, 'prescription']);
            Route::post('/telehealth/{id}/reminder', [TelehealthApiController::class, 'reminder']);
            Route::post('/telehealth/{id}/closeout', [TelehealthApiController::class, 'closeout']);
            Route::post('/telehealth/{id}/cancel', [TelehealthApiController::class, 'cancel']);
            Route::post('/telehealth/{id}/end', [TelehealthApiController::class, 'end']);
        });

        // Clinical documents
        Route::middleware('can:view-patients')->group(function () {
            Route::get('/patients/{patientId}/clinical-documents', [ClinicalDocumentApiController::class, 'index']);
            Route::get('/patients/{patientId}/clinical-documents/{id}', [ClinicalDocumentApiController::class, 'show']);
        });
        Route::middleware('can:create-patients')->group(function () {
            Route::post('/patients/{patientId}/clinical-documents', [ClinicalDocumentApiController::class, 'store']);
        });

        // Encounter notes
        Route::middleware('can:view-encounters')->group(function () {
            Route::get('/encounters/{encounterId}/notes', [EncounterNoteApiController::class, 'index']);
        });
        Route::middleware('can:create-encounters')->group(function () {
            Route::post('/encounters/{encounterId}/notes', [EncounterNoteApiController::class, 'store']);
        });

        // Waitlist management
        Route::middleware('can:view-appointments')->group(function () {
            Route::get('/waitlists', [WaitlistApiController::class, 'index']);
        });
        Route::middleware('can:create-appointments')->group(function () {
            Route::post('/waitlists', [WaitlistApiController::class, 'store']);
        });
        Route::middleware('can:update-appointments')->group(function () {
            Route::patch('/waitlists/{id}', [WaitlistApiController::class, 'update']);
        });

        // Provider schedules
        Route::middleware('can:view-appointments')->group(function () {
            Route::get('/provider-schedules', [ProviderScheduleApiController::class, 'index']);
            Route::get('/provider-schedules/{id}', [ProviderScheduleApiController::class, 'show']);
        });
        Route::middleware('can:create-appointments')->group(function () {
            Route::post('/provider-schedules', [ProviderScheduleApiController::class, 'store']);
        });
        Route::middleware('can:update-appointments')->group(function () {
            Route::patch('/provider-schedules/{id}', [ProviderScheduleApiController::class, 'update']);
            Route::post('/provider-schedules/{id}/generate-slots', [ProviderScheduleApiController::class, 'generateSlots']);
        });
        Route::middleware('can:delete-appointments')->group(function () {
            Route::delete('/provider-schedules/{id}', [ProviderScheduleApiController::class, 'destroy']);
        });

        // Emergency
        Route::middleware(['can:view-er', 'throttle:60,1'])->group(function () {
            Route::get('/emergency/queue', [EmergencyApiController::class, 'queue']);
            Route::get('/emergency/visits/{id}', [EmergencyApiController::class, 'showVisit']);
        });
        Route::middleware(['can:create-er-visits', 'throttle:30,1'])->group(function () {
            Route::post('/emergency/visits', [EmergencyApiController::class, 'storeVisit']);
        });
        Route::middleware(['can:triage-patients', 'throttle:30,1'])->group(function () {
            Route::post('/triage/score', [TriageApiController::class, 'score']);
            Route::post('/emergency/{id}/triage', [TriageApiController::class, 'store']);
        });

        // Wards / Rooms / Beds
        Route::middleware(['can:view-beds', 'throttle:60,1'])->group(function () {
            Route::get('/wards', [WardApiController::class, 'index']);
            Route::get('/rooms', [RoomApiController::class, 'index']);
            Route::get('/beds', [BedApiController::class, 'index']);
            Route::get('/beds/available', [BedApiController::class, 'available']);
            Route::get('/beds/{id}', [BedApiController::class, 'show']);
        });
        Route::middleware(['can:manage-beds', 'throttle:30,1'])->group(function () {
            Route::post('/beds/{id}/reserve', [BedApiController::class, 'reserve']);
            Route::post('/beds/{id}/assign', [BedApiController::class, 'assign']);
            Route::post('/beds/{id}/release', [BedApiController::class, 'release']);
        });

        // Admissions
        Route::middleware(['can:view-admissions', 'throttle:60,1'])->group(function () {
            Route::get('/admissions', [AdmissionApiController::class, 'index']);
            Route::get('/admissions/{id}', [AdmissionApiController::class, 'show']);
        });
        Route::middleware(['can:manage-admissions', 'throttle:30,1'])->group(function () {
            Route::post('/admissions', [AdmissionApiController::class, 'store']);
            Route::post('/admissions/{id}/transfer', [TransferApiController::class, 'store']);
            Route::post('/admissions/{id}/discharge', [DischargeApiController::class, 'store']);
        });

        // Notifications
        Route::middleware('throttle:60,1')->group(function () {
            Route::get('/notifications', [NotificationApiController::class, 'index']);
            Route::post('/notifications/{id}/read', [NotificationApiController::class, 'markRead']);
        });
    });
});
