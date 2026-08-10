<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Record an audit log entry.
     */
    public function log(
        string $action,
        string $resourceType,
        ?int $resourceId = null,
        ?string $result = 'SUCCESS',
        ?array $metadata = null,
        ?int $userId = null
    ): AuditLog {
        $userId = $userId ?? Auth::id();

        return AuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'result' => $result,
            'ip_address' => Request::ip(),
            'metadata' => $metadata,
        ]);
    }

    // Common actions
    public function createPatient(int $id, ?array $meta = null): AuditLog
    {
        return $this->log('CREATE_PATIENT', 'patient', $id, 'SUCCESS', $meta);
    }

    public function updatePatient(int $id, ?array $meta = null): AuditLog
    {
        return $this->log('UPDATE_PATIENT', 'patient', $id, 'SUCCESS', $meta);
    }

    public function viewPatient(int $id): AuditLog
    {
        return $this->log('VIEW_PATIENT', 'patient', $id);
    }

    public function createAppointment(int $id, ?array $meta = null): AuditLog
    {
        return $this->log('CREATE_APPOINTMENT', 'appointment', $id, 'SUCCESS', $meta);
    }

    public function cancelAppointment(int $id, ?array $meta = null): AuditLog
    {
        return $this->log('CANCEL_APPOINTMENT', 'appointment', $id, 'SUCCESS', $meta);
    }

    public function rescheduleAppointment(int $id, ?array $meta = null): AuditLog
    {
        return $this->log('RESCHEDULE_APPOINTMENT', 'appointment', $id, 'SUCCESS', $meta);
    }

    public function checkInAppointment(int $id): AuditLog
    {
        return $this->log('CHECK_IN_APPOINTMENT', 'appointment', $id);
    }

    public function createEncounter(int $id): AuditLog
    {
        return $this->log('CREATE_ENCOUNTER', 'encounter', $id);
    }

    public function createErVisit(int $id): AuditLog
    {
        return $this->log('CREATE_ER_VISIT', 'er_visit', $id);
    }

    public function createTriage(int $id): AuditLog
    {
        return $this->log('CREATE_TRIAGE', 'triage_assessment', $id);
    }

    public function assignBed(int $admissionId, int $bedId): AuditLog
    {
        return $this->log('ASSIGN_BED', 'bed_assignment', $bedId, 'SUCCESS', ['admission_id' => $admissionId]);
    }

    public function transferPatient(int $admissionId, int $toBedId): AuditLog
    {
        return $this->log('TRANSFER_PATIENT', 'patient_transfer', $admissionId, 'SUCCESS', ['to_bed_id' => $toBedId]);
    }

    public function dischargePatient(int $admissionId): AuditLog
    {
        return $this->log('DISCHARGE_PATIENT', 'discharge', $admissionId);
    }

    public function createAdmission(int $id): AuditLog
    {
        return $this->log('CREATE_ADMISSION', 'admission', $id);
    }
}
