<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Bed;
use App\Models\ErVisit;
use App\Models\Patient;
use App\Models\TelehealthSession;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $followUpDue = \App\Models\Encounter::with('patient')
            ->whereNotNull('follow_up_date')
            ->whereDate('follow_up_date', '<=', now()->addDays(7)->toDateString())
            ->orderBy('follow_up_date', 'asc')
            ->limit(5)
            ->get();

        $data = [
            'todayPatients' => Patient::whereDate('created_at', today())->count(),
            'todayAppointments' => Appointment::whereDate('starts_at', today())->count(),
            'erPatients' => ErVisit::whereDate('arrived_at', today())->count(),
            'availableBeds' => Bed::where('status', Bed::STATUS_AVAILABLE)->count(),
            'occupiedBeds' => Bed::where('status', Bed::STATUS_OCCUPIED)->count(),
            'telehealthAppointments' => TelehealthSession::whereDate('start_time', today())->count(),
            'recentAppointments' => Appointment::with(['patient', 'provider'])
                ->orderBy('starts_at', 'desc')
                ->limit(10)
                ->get(),
            'followUpDue' => $followUpDue,
            'erQueue' => \App\Models\ErQueue::with(['erVisit.patient'])
                ->orderByRaw("FIELD(priority, 'Level 1', 'Level 2', 'Level 3', 'Level 4', 'Level 5')")
                ->orderBy('queued_at')
                ->limit(10)
                ->get(),
            'recentAuditLogs' => AuditLog::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
            'bedOccupancy' => Bed::selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
        ];

        // Role-specific data
        if ($user->hasRole('doctor')) {
            $provider = $user->provider;
            $data['myAppointments'] = $provider
                ? Appointment::with('patient')->where('provider_id', $provider->id)->whereDate('starts_at', today())->get()
                : collect();
            $data['myEncounters'] = $provider
                ? \App\Models\Encounter::with('patient')->where('provider_id', $provider->id)->orderBy('started_at', 'desc')->limit(10)->get()
                : collect();
            $data['myPatientCount'] = $provider
                ? \App\Models\Encounter::where('provider_id', $provider->id)->whereDate('started_at', today())->count()
                : 0;
        }

        if ($user->hasRole('patient')) {
            $patient = $user->patient;
            $data['myAppointments'] = $patient
                ? Appointment::with('provider')->where('patient_id', $patient->id)->orderBy('starts_at', 'desc')->limit(10)->get()
                : collect();
            $data['myNotifications'] = $user->notifications->take(10);
            $data['myFollowUps'] = $patient
                ? \App\Models\Encounter::with('provider')->where('patient_id', $patient->id)->whereNotNull('follow_up_date')->orderBy('follow_up_date', 'asc')->limit(5)->get()
                : collect();
        }

        if ($user->hasRole('nurse')) {
            $data['triageQueue'] = \App\Models\ErQueue::with(['erVisit.patient'])
                ->where('status', \App\Models\ErQueue::STATUS_WAITING)
                ->orderByRaw("FIELD(priority, 'Level 1', 'Level 2', 'Level 3', 'Level 4', 'Level 5')")
                ->orderBy('queued_at')
                ->limit(10)
                ->get();
            $data['pendingAdmissions'] = Admission::with('patient')
                ->whereIn('status', [Admission::STATUS_REQUESTED, Admission::STATUS_APPROVED])
                ->limit(10)
                ->get();
        }

        if ($user->hasRole('registration')) {
            $data['registrationDeskQueue'] = Appointment::with(['patient', 'provider'])
                ->whereDate('starts_at', today())
                ->orderBy('starts_at', 'asc')
                ->limit(10)
                ->get();
            $data['checkedInToday'] = Appointment::whereDate('starts_at', today())
                ->where('status', 'CHECKED_IN')
                ->count();
        }

        if ($user->hasAnyRole(['super-admin', 'hospital-admin'])) {
            $data['systemAlerts'] = [
                'Acuity board active',
                'Bed assignments updated',
                'Daily reporting available',
            ];
        }

        if ($user->hasAnyRole(['hospital-admin', 'nurse'])) {
            $data['pendingAdmissions'] = Admission::with('patient')
                ->whereIn('status', [Admission::STATUS_REQUESTED, Admission::STATUS_APPROVED])
                ->limit(10)
                ->get();
        }

        $data['currentRole'] = $user->roles()->first()?->name ?? 'guest';

        return view('dashboard', $data);
    }
}
