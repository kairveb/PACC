<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\Discharge;
use App\Models\Patient;
use App\Models\Provider;
use App\Models\Ward;
use App\Services\AdmissionService;
use App\Services\BedManagementService;
use Illuminate\Http\Request;

class InpatientController extends Controller
{
    public function __construct(
        protected AdmissionService $admissions,
        protected BedManagementService $beds
    ) {
    }

public function wards()
    {
        $wards = Ward::with(['rooms.beds.activeAssignment.admission.patient'])->get();
        $bedStats = Bed::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        return view('inpatient.beds', compact('wards', 'bedStats'));
    }

    public function setBedStatus(Request $request, Bed $bed)
    {
        $request->validate(['status' => ['required', 'in:AVAILABLE,MAINTENANCE,BLOCKED,CLEANING']]);
        $this->beds->setStatus($bed, $request->status);
        return back()->with('success', 'Bed status updated.');
    }

    public function admissions(Request $request)
    {
        $query = Admission::with(['patient', 'bedAssignments.bed.room.ward', 'discharge'])->orderBy('created_at', 'desc');

        if ($request->get('status')) {
            $query->where('status', $request->get('status'));
        }

        $admissions = $query->paginate(15);
        $statuses = [Admission::STATUS_REQUESTED, Admission::STATUS_APPROVED, Admission::STATUS_ADMITTED, Admission::STATUS_TRANSFERRED, Admission::STATUS_DISCHARGED];

        return view('inpatient.admissions', compact('admissions', 'statuses'));
    }

    public function createAdmission()
    {
        $patients = Patient::orderBy('last_name')->get();
        $providers = Provider::where('active', true)->get();
        return view('inpatient.create-admission', compact('patients', 'providers'));
    }

    public function storeAdmission(Request $request)
    {
        $data = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'attending_provider_id' => ['nullable', 'exists:providers,id'],
            'reason' => ['nullable', 'string'],
        ]);

        $admission = $this->admissions->create($data);

return redirect()->route('admissions.show', $admission)->with('success', 'Admission request created.');
    }

    public function showAdmission(Admission $admission)
    {
        $admission->load(['patient', 'bedAssignments.bed.room.ward', 'transfers.fromBed.room.ward', 'transfers.toBed.room.ward', 'discharge', 'attendingProvider']);
        $availableBeds = $this->beds->availableBeds();
        $wards = Ward::with(['rooms.beds'])->get();
        return view('inpatient.show-admission', compact('admission', 'availableBeds', 'wards'));
    }

    public function approveAdmission(Admission $admission)
    {
        $this->admissions->approve($admission);
        return back()->with('success', 'Admission approved.');
    }

    public function admit(Request $request, Admission $admission)
    {
        $data = $request->validate([
            'bed_id' => ['required', 'exists:beds,id'],
            'reservation_id' => ['nullable', 'exists:bed_reservations,id'],
        ]);

        try {
            $this->admissions->admit($admission, $data['bed_id'], $data['reservation_id'] ?? null);
            return back()->with('success', 'Patient admitted and bed assigned.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }

    public function reserveBed(Request $request, Admission $admission)
    {
        $data = $request->validate(['bed_id' => ['required', 'exists:beds,id']]);
        try {
            $this->beds->reserveBed($admission, $data['bed_id']);
            return back()->with('success', 'Bed reserved.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }

    public function transfer(Request $request, Admission $admission)
    {
        $data = $request->validate([
            'to_bed_id' => ['required', 'exists:beds,id', 'different:bed_id'],
            'reason' => ['nullable', 'string'],
        ]);

        try {
            $this->beds->transfer($admission, $data['to_bed_id'], $data['reason'] ?? null);
            return back()->with('success', 'Patient transferred.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }

    public function discharge(Request $request, Admission $admission)
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string'],
            'disposition' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->admissions->discharge($admission, $data);

        return back()->with('success', 'Patient discharged and bed released for cleaning.');
    }
}
