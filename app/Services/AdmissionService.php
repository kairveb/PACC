<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\Discharge;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdmissionService
{
    public function __construct(
        protected BedManagementService $bedManagement,
        protected AuditLogService $audit
    ) {
    }

    /**
     * Generate the next admission number.
     */
    public function generateNumber(): string
    {
        $year = now()->format('Y');
        $last = Admission::where('admission_number', 'like', "ADM-{$year}-%")
            ->orderByDesc('admission_number')
            ->lockForUpdate()
            ->first();

        if ($last) {
            $parts = explode('-', $last->admission_number);
            return "ADM-{$year}-" . str_pad((string) ((int) $parts[2]) + 1, 6, '0', STR_PAD_LEFT);
        }

        return "ADM-{$year}-000001";
    }

    /**
     * Create an admission request.
     */
    public function create(array $data): Admission
    {
        return DB::transaction(function () use ($data) {
            $admission = $this->createAdmissionRecord($data);
            $this->audit->createAdmission($admission->id);

            return $admission;
        });
    }

    protected function createAdmissionRecord(array $data): Admission
    {
        return Admission::create([
            'admission_number' => $this->generateNumber(),
            'patient_id' => $data['patient_id'],
            'er_visit_id' => $data['er_visit_id'] ?? null,
            'attending_provider_id' => $data['attending_provider_id'] ?? null,
            'status' => Admission::STATUS_REQUESTED,
            'reason' => $data['reason'] ?? null,
            'created_by' => auth()->id(),
        ]);
    }

    public function approve(Admission $admission): Admission
    {
        $admission->update(['status' => Admission::STATUS_APPROVED]);

        return $admission;
    }

    /**
     * Admit a patient and assign a bed transactionally.
     */
    public function admit(Admission $admission, int $bedId, ?int $reservationId = null): Admission
    {
        return DB::transaction(function () use ($admission, $bedId, $reservationId) {
            $this->markAdmitted($admission);
            $this->bedManagement->assignBed($admission, $bedId, $reservationId);

            return $admission;
        });
    }

    protected function markAdmitted(Admission $admission): void
    {
        $admission->update([
            'status' => Admission::STATUS_ADMITTED,
            'admitted_at' => now(),
        ]);
    }

    /**
     * Discharge a patient and release the bed transactionally.
     */
    public function discharge(Admission $admission, array $data): Discharge
    {
        return DB::transaction(function () use ($admission, $data) {
            $discharge = $this->createDischargeRecord($admission, $data);
            $this->markDischarged($admission);
            $this->releaseActiveBedIfPresent($admission);
            $this->audit->dischargePatient($admission->id);

            return $discharge;
        });
    }

    protected function createDischargeRecord(Admission $admission, array $data): Discharge
    {
        return Discharge::create([
            'admission_id' => $admission->id,
            'authorized_by' => auth()->id(),
            'discharged_at' => now(),
            'reason' => $data['reason'] ?? null,
            'disposition' => $data['disposition'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    protected function markDischarged(Admission $admission): void
    {
        $admission->update(['status' => Admission::STATUS_DISCHARGED]);
    }

    protected function releaseActiveBedIfPresent(Admission $admission): void
    {
        $activeAssignment = $admission->activeBedAssignment;
        if ($activeAssignment) {
            $this->bedManagement->releaseBed($admission, $activeAssignment->bed_id);
        }
    }
}
