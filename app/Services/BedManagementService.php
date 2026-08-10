<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\BedReservation;
use App\Models\PatientTransfer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BedManagementService
{
    public function __construct(protected AuditLogService $audit)
    {
    }

    /**
     * Get available beds, optionally filtered by ward.
     */
    public function availableBeds(?int $wardId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = Bed::where('status', Bed::STATUS_AVAILABLE)
            ->with(['room.ward']);

        if ($wardId) {
            $query->whereHas('room', fn ($q) => $q->where('ward_id', $wardId));
        }

        return $query->orderBy('room_id')->get();
    }

    /**
     * Reserve a bed for an admission. Transactional with locking.
     */
    public function reserveBed(Admission $admission, int $bedId, ?int $expiresInMinutes = null): BedReservation
    {
        return DB::transaction(function () use ($admission, $bedId, $expiresInMinutes) {
            $bed = Bed::where('id', $bedId)->lockForUpdate()->first();

            if (!$bed || $bed->status !== Bed::STATUS_AVAILABLE) {
                throw ValidationException::withMessages([
                    'bed_id' => 'This bed is no longer available.',
                ]);
            }

            // Cancel any existing active reservations for this admission
            BedReservation::where('admission_id', $admission->id)
                ->where('status', BedReservation::STATUS_ACTIVE)
                ->update(['status' => BedReservation::STATUS_CANCELLED]);

            $reservation = BedReservation::create([
                'bed_id' => $bed->id,
                'admission_id' => $admission->id,
                'reserved_by' => auth()->id(),
                'expires_at' => $expiresInMinutes ? now()->addMinutes($expiresInMinutes) : null,
                'status' => BedReservation::STATUS_ACTIVE,
            ]);

            $bed->update(['status' => Bed::STATUS_RESERVED, 'status_updated_at' => now()]);

            $this->audit->log('RESERVE_BED', 'bed', $bed->id, 'SUCCESS', ['admission_id' => $admission->id]);

            return $reservation;
        });
    }

    /**
     * Assign a bed to an admission. Transactional with row locking to prevent concurrency.
     */
    public function assignBed(Admission $admission, int $bedId, ?int $reservationId = null): BedAssignment
    {
        return DB::transaction(function () use ($admission, $bedId, $reservationId) {
            $bed = $this->lockBed($bedId);
            $this->ensureBedAssignable($bed);
            $this->convertReservationIfProvided($admission, $bed, $reservationId);
            $this->releaseExistingAssignments($admission);

            $assignment = BedAssignment::create([
                'admission_id' => $admission->id,
                'bed_id' => $bed->id,
                'assigned_by' => auth()->id(),
                'assigned_at' => now(),
                'status' => BedAssignment::STATUS_ACTIVE,
            ]);

            $bed->update(['status' => Bed::STATUS_OCCUPIED, 'status_updated_at' => now()]);

            $this->audit->assignBed($admission->id, $bed->id);

            return $assignment;
        });
    }

    protected function lockBed(int $bedId): Bed
    {
        $bed = Bed::where('id', $bedId)->lockForUpdate()->first();

        if (!$bed) {
            throw ValidationException::withMessages(['bed_id' => 'Bed not found.']);
        }

        return $bed;
    }

    protected function ensureBedAssignable(Bed $bed): void
    {
        if (!in_array($bed->status, [Bed::STATUS_AVAILABLE, Bed::STATUS_RESERVED])) {
            throw ValidationException::withMessages([
                'bed_id' => "This bed is no longer available (current status: {$bed->status}).",
            ]);
        }
    }

    protected function convertReservationIfProvided(Admission $admission, Bed $bed, ?int $reservationId): void
    {
        if (!$reservationId) {
            return;
        }

        $reservation = BedReservation::where('id', $reservationId)
            ->where('admission_id', $admission->id)
            ->where('bed_id', $bed->id)
            ->where('status', BedReservation::STATUS_ACTIVE)
            ->lockForUpdate()
            ->first();

        if (!$reservation) {
            throw ValidationException::withMessages(['bed_id' => 'The reservation is invalid or expired.']);
        }

        $reservation->update(['status' => BedReservation::STATUS_CONVERTED]);
    }

    protected function releaseExistingAssignments(Admission $admission): void
    {
        BedAssignment::where('admission_id', $admission->id)
            ->where('status', BedAssignment::STATUS_ACTIVE)
            ->update(['status' => BedAssignment::STATUS_RELEASED, 'released_at' => now()]);
    }

    /**
     * Transfer a patient to a new bed. Transactional.
     */
    public function transfer(Admission $admission, int $toBedId, ?string $reason = null): PatientTransfer
    {
        return DB::transaction(function () use ($admission, $toBedId, $reason) {
            $activeAssignment = $admission->activeBedAssignment()->lockForUpdate()->first();

            if (!$activeAssignment) {
                throw ValidationException::withMessages(['admission_id' => 'Patient has no active bed assignment.']);
            }

            $toBed = Bed::where('id', $toBedId)->lockForUpdate()->first();

            if (!$toBed || !in_array($toBed->status, [Bed::STATUS_AVAILABLE, Bed::STATUS_RESERVED])) {
                throw ValidationException::withMessages([
                    'to_bed_id' => "Destination bed is not available (status: {$toBed->status}).",
                ]);
            }

            $fromBedId = $activeAssignment->bed_id;

            // Release the previous bed
            $activeAssignment->update(['status' => BedAssignment::STATUS_RELEASED, 'released_at' => now()]);
            $oldBed = Bed::find($fromBedId);
            if ($oldBed) {
                $oldBed->update(['status' => Bed::STATUS_CLEANING, 'status_updated_at' => now()]);
            }

            // Create new assignment
            $newAssignment = BedAssignment::create([
                'admission_id' => $admission->id,
                'bed_id' => $toBed->id,
                'assigned_by' => auth()->id(),
                'assigned_at' => now(),
                'status' => BedAssignment::STATUS_ACTIVE,
            ]);

            $toBed->update(['status' => Bed::STATUS_OCCUPIED, 'status_updated_at' => now()]);

            $transfer = PatientTransfer::create([
                'admission_id' => $admission->id,
                'from_bed_id' => $fromBedId,
                'to_bed_id' => $toBed->id,
                'transferred_by' => auth()->id(),
                'transferred_at' => now(),
                'reason' => $reason,
            ]);

            $admission->update(['status' => Admission::STATUS_TRANSFERRED]);

            $this->audit->transferPatient($admission->id, $toBed->id);

            return $transfer;
        });
    }

    /**
     * Release a bed and mark it for cleaning. Transactional.
     */
    public function releaseBed(Admission $admission, int $bedId): Bed
    {
        return DB::transaction(function () use ($admission, $bedId) {
            $bed = Bed::where('id', $bedId)->lockForUpdate()->first();

            if (!$bed) {
                throw ValidationException::withMessages(['bed_id' => 'Bed not found.']);
            }

            BedAssignment::where('admission_id', $admission->id)
                ->where('bed_id', $bed->id)
                ->where('status', BedAssignment::STATUS_ACTIVE)
                ->update(['status' => BedAssignment::STATUS_RELEASED, 'released_at' => now()]);

            // Cancel any active reservations
            BedReservation::where('bed_id', $bed->id)
                ->where('status', BedReservation::STATUS_ACTIVE)
                ->update(['status' => BedReservation::STATUS_CANCELLED]);

            $bed->update(['status' => Bed::STATUS_CLEANING, 'status_updated_at' => now()]);

            $this->audit->log('RELEASE_BED', 'bed', $bed->id, 'SUCCESS', ['admission_id' => $admission->id]);

            return $bed;
        });
    }

    /**
     * Mark a cleaning bed as available, or set maintenance/blocked status.
     */
    public function setStatus(Bed $bed, string $status): Bed
    {
        $allowed = [Bed::STATUS_AVAILABLE, Bed::STATUS_MAINTENANCE, Bed::STATUS_BLOCKED, Bed::STATUS_CLEANING];

        if (!in_array($status, $allowed)) {
            throw ValidationException::withMessages(['status' => "Cannot set bed status to {$status} directly."]);
        }

        $bed->update(['status' => $status, 'status_updated_at' => now()]);
        $this->audit->log('SET_BED_STATUS', 'bed', $bed->id, 'SUCCESS', ['status' => $status]);

        return $bed;
    }
}
