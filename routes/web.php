<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorQueueController;
use App\Http\Controllers\EmergencyController;
use App\Http\Controllers\EncounterController;
use App\Http\Controllers\InpatientController;
use App\Http\Controllers\MfaController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientPortalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TelehealthController;
use App\Http\Controllers\TriageAssessmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::middleware(['can:portal-dashboard', 'role:patient'])->group(function () {
        Route::get('patient-portal', [PatientPortalController::class, 'dashboard'])->name('patients.portal');
        Route::get('patient-portal/appointments', [PatientPortalController::class, 'appointments'])->name('patients.portal.appointments');
        Route::get('patient-portal/history', [PatientPortalController::class, 'history'])->name('patients.portal.history');
        Route::get('patient-portal/telehealth', [PatientPortalController::class, 'telehealth'])->name('patients.portal.telehealth');
    });

    Route::middleware(['role:patient'])->group(function () {
        Route::get('patients/profile', [PatientController::class, 'profile'])->name('patients.profile');
        Route::post('patients/profile', [PatientController::class, 'saveProfile'])->name('patients.profile.save');
    });
    Route::middleware(['role:registration,super-admin,hospital-admin'])->group(function () {
        Route::get('patients/lookup', [PatientController::class, 'lookup'])->name('patients.lookup');
    });

    // Patients
    Route::middleware('can:view-patients')->group(function () {
        Route::resource('patients', PatientController::class)->except(['edit', 'update', 'destroy']);
        Route::get('patients/vitals/{patient}', [PatientController::class, 'showVitals'])->name('patients.vitals');
    });
    Route::middleware('can:create-patients')->group(function () {
        Route::post('patients/{patient}/verify', [PatientController::class, 'verify'])->name('patients.verify');
    });

    // Appointments
    Route::middleware('can:view-appointments')->group(function () {
        Route::resource('appointments', AppointmentController::class)->except(['edit', 'update', 'destroy']);
        Route::get('appointments/slots/json', [AppointmentController::class, 'slots'])->name('appointments.slots');
    });
    Route::middleware('can:create-appointments')->group(function () {
        Route::post('appointments/{appointment}/check-in', [AppointmentController::class, 'checkIn'])->name('appointments.check-in');
    });
Route::middleware('can:cancel-appointments')->group(function () {
        Route::post('appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
        Route::post('appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule'])->name('appointments.reschedule');
        Route::post('appointments/{appointment}/no-show', [AppointmentController::class, 'markNoShow'])->name('appointments.no-show');
    });

    // Outpatient / Encounters
    Route::middleware('can:view-encounters')->group(function () {
        Route::get('outpatient', [EncounterController::class, 'index'])->name('outpatient.index');
        Route::get('encounters', [EncounterController::class, 'index'])->name('encounters.index');
        Route::get('encounters/create', [EncounterController::class, 'create'])->name('encounters.create');
        Route::post('encounters', [EncounterController::class, 'store'])->name('encounters.store');
        Route::get('encounters/{encounter}', [EncounterController::class, 'show'])->name('encounters.show');
        Route::post('encounters/{encounter}/complete', [EncounterController::class, 'complete'])->name('encounters.complete');
    });

    // Emergency / ER
    Route::middleware(['can:view-er', 'role:nurse,doctor,super-admin,hospital-admin,registration'])->group(function () {
        Route::get('emergency', [EmergencyController::class, 'index'])->name('emergency.index');
        Route::get('emergency/create', [EmergencyController::class, 'create'])->name('emergency.create');
        Route::post('emergency', [EmergencyController::class, 'store'])->name('emergency.store');
        Route::get('emergency/{visit}', [EmergencyController::class, 'show'])->name('emergency.show');
        Route::post('emergency/{visit}/triage', [EmergencyController::class, 'triage'])->name('emergency.triage');
        Route::post('emergency/queue/{queue}/status', [EmergencyController::class, 'queueStatus'])->name('emergency.queue-status');
        Route::get('emergency/check-in/{token}', [\App\Http\Controllers\ArrivalCheckInController::class, 'show'])->name('emergency.checkin.show');
        Route::post('emergency/check-in', [\App\Http\Controllers\ArrivalCheckInController::class, 'store'])->name('emergency.checkin.store');
    });

    Route::middleware(['can:triage-patients', 'role:nurse,doctor,super-admin,hospital-admin'])->group(function () {
        Route::get('triage', [TriageAssessmentController::class, 'create'])->name('triage.dashboard');
        Route::get('triage/create', [TriageAssessmentController::class, 'create'])->name('triage.create');
        Route::post('triage', [TriageAssessmentController::class, 'store'])->name('triage.store');
        Route::get('triage/{triageAssessment}/er-intake', [EmergencyController::class, 'createFromTriage'])->name('triage.er-intake');
    });

    Route::middleware(['can:view-encounters', 'role:doctor,super-admin,hospital-admin'])->group(function () {
        Route::get('doctors/queue', [DoctorQueueController::class, 'index'])->name('doctors.queue');
        Route::get('doctors/queue/{triageAssessment}', [DoctorQueueController::class, 'show'])->name('doctors.queue.show');
        Route::post('doctors/queue/{triageAssessment}/status', [DoctorQueueController::class, 'updateStatus'])->name('doctors.queue.status');
    });

    // Inpatient / Beds / Admissions
    Route::middleware(['can:view-beds', 'role:nurse,super-admin,hospital-admin'])->group(function () {
        Route::get('inpatient', [InpatientController::class, 'wards'])->name('inpatient.index');
        Route::get('beds', [InpatientController::class, 'wards'])->name('beds.index');
        Route::get('admissions', [InpatientController::class, 'admissions'])->name('admissions.index');
        Route::get('admissions/create', [InpatientController::class, 'createAdmission'])->name('admissions.create');
        Route::post('admissions', [InpatientController::class, 'storeAdmission'])->name('admissions.store');
        Route::get('admissions/{admission}', [InpatientController::class, 'showAdmission'])->name('admissions.show');
    });
    Route::middleware(['can:manage-beds', 'role:nurse,super-admin,hospital-admin'])->group(function () {
        Route::post('beds/{bed}/status', [InpatientController::class, 'setBedStatus'])->name('beds.status');
        Route::post('admissions/{admission}/approve', [InpatientController::class, 'approveAdmission'])->name('admissions.approve');
        Route::post('admissions/{admission}/admit', [InpatientController::class, 'admit'])->name('admissions.admit');
        Route::post('admissions/{admission}/reserve', [InpatientController::class, 'reserveBed'])->name('admissions.reserve');
        Route::post('admissions/{admission}/transfer', [InpatientController::class, 'transfer'])->name('admissions.transfer');
        Route::post('admissions/{admission}/discharge', [InpatientController::class, 'discharge'])->name('admissions.discharge');
    });

    // Telehealth
    Route::middleware(['can:view-telehealth', 'role:doctor,nurse,super-admin,hospital-admin'])->group(function () {
        Route::get('telehealth', [TelehealthController::class, 'index'])->name('telehealth.index');
        Route::get('telehealth/{session}', [TelehealthController::class, 'show'])->name('telehealth.show');
    });
    Route::middleware('can:join-telehealth')->group(function () {
        Route::get('telehealth/{session}/join', [TelehealthController::class, 'join'])->name('telehealth.join');
    });
    Route::middleware('can:start-telehealth')->group(function () {
        Route::post('telehealth/{session}/create-meeting', [TelehealthController::class, 'createMeeting'])->name('telehealth.create-meeting');
    });

    // Reports
    Route::middleware(['can:view-reports', 'role:doctor,super-admin,hospital-admin'])->group(function () {
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/patients', [ReportController::class, 'patients'])->name('reports.patients');
        Route::get('reports/appointments', [ReportController::class, 'appointments'])->name('reports.appointments');
        Route::get('reports/encounters', [ReportController::class, 'encounters'])->name('reports.encounters');
        Route::get('reports/er', [ReportController::class, 'erVolume'])->name('reports.er');
        Route::get('reports/beds', [ReportController::class, 'bedOccupancy'])->name('reports.beds');
        Route::get('reports/telehealth', [ReportController::class, 'telehealth'])->name('reports.telehealth');
    });

    // Audit logs
    Route::middleware(['can:view-audit-logs', 'role:super-admin,hospital-admin'])->group(function () {
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit.index');
    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Two-factor authentication (MFA) management
    Route::get('profile/mfa', [MfaController::class, 'show'])->name('mfa.setup');
    Route::post('profile/mfa/enable', [MfaController::class, 'enable'])->name('mfa.enable');
    Route::post('profile/mfa/disable', [MfaController::class, 'disable'])->name('mfa.disable');
});

require __DIR__.'/auth.php';
require __DIR__.'/portal.php';
