<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AuditTrailController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage settings');

        $logs = AuditLog::with('actor')
            ->latest()
            ->limit(100)
            ->get();

        return view('settings.audit-trail', [
            'active' => 'audit-trail',
            'logs' => $logs,
            'summary' => [
                'total' => AuditLog::count(),
                'today' => AuditLog::whereDate('created_at', now('Asia/Jakarta')->toDateString())->count(),
                'events' => AuditLog::distinct('event')->count('event'),
            ],
        ]);
    }
}
