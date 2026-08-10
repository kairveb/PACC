<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        if ($request->get('action')) {
            $query->where('action', $request->get('action'));
        }

        $logs = $query->paginate(30);
        $actions = AuditLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('audit.index', compact('logs', 'actions'));
    }
}
