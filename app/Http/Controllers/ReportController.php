<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Encounter;
use App\Models\ErVisit;
use App\Models\Patient;
use App\Models\TelehealthSession;
use App\Models\TriageAssessment;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function patients(Request $request)
    {
        $start = $request->get('start') ?: today()->subDays(30)->toDateString();
        $end = $request->get('end') ?: today()->toDateString();

        $data = Patient::whereBetween('created_at', [$start, $end])->get();

        $byDay = $data->groupBy(fn ($p) => $p->created_at->toDateString())->map->count();

        return view('reports.patients', compact('data', 'byDay', 'start', 'end'));
    }

    public function appointments(Request $request)
    {
        $start = $request->get('start') ?: today()->subDays(30)->toDateString();
        $end = $request->get('end') ?: today()->toDateString();

        $appointments = Appointment::whereBetween('starts_at', [$start, $end])->get();

        $total = $appointments->count();
        $cancelled = $appointments->where('status', Appointment::STATUS_CANCELLED)->count();
        $noShow = $appointments->where('status', Appointment::STATUS_NO_SHOW)->count();
        $byStatus = $appointments->groupBy('status')->map->count();

        return view('reports.appointments', compact('appointments', 'total', 'cancelled', 'noShow', 'byStatus', 'start', 'end'));
    }

    public function encounters(Request $request)
    {
        $start = $request->get('start') ?: today()->subDays(30)->toDateString();
        $end = $request->get('end') ?: today()->toDateString();

        $encounters = Encounter::whereBetween('started_at', [$start, $end])->get();
        $byType = $encounters->groupBy('type')->map->count();

        return view('reports.encounters', compact('encounters', 'byType', 'start', 'end'));
    }

    public function erVolume(Request $request)
    {
        $start = $request->get('start') ?: today()->subDays(30)->toDateString();
        $end = $request->get('end') ?: today()->toDateString();

        $visits = ErVisit::whereBetween('arrived_at', [$start, $end])->get();
        $triage = TriageAssessment::whereBetween('triaged_at', [$start, $end])->get();
        $byPriority = $triage->groupBy('priority')->map->count();

        return view('reports.er', compact('visits', 'byPriority', 'start', 'end'));
    }

    public function bedOccupancy()
    {
        $beds = Bed::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $admissions = Admission::with('patient')->where('status', Admission::STATUS_ADMITTED)->get();

        return view('reports.beds', ['beds' => $beds, 'admissions' => $admissions]);
    }

    public function telehealth()
    {
        $sessions = TelehealthSession::with('appointment.patient')->orderBy('start_time', 'desc')->limit(100)->get();
        $byStatus = $sessions->groupBy('status')->map->count();

        return view('reports.telehealth', compact('sessions', 'byStatus'));
    }
}
