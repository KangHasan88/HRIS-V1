<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\HolidayCalendar;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class HolidayCalendarController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('view holiday calendar');

        $year = (int) $request->query('year', now()->year);
        $companyId = $request->integer('company_id') ?: null;

        $query = HolidayCalendar::with(['company', 'branch'])
            ->whereYear('holiday_date', $year)
            ->when($companyId, fn ($holidayQuery) => $holidayQuery->where('company_id', $companyId))
            ->orderBy('holiday_date')
            ->orderBy('name');

        $holidays = $query->get();

        $summary = [
            'total' => $holidays->count(),
            'attendance' => $holidays->where('affects_attendance', true)->count(),
            'leave' => $holidays->where('affects_leave', true)->count(),
            'payroll' => $holidays->where('affects_payroll', true)->count(),
        ];

        return view('attendance.holidays', [
            'active' => 'holidays',
            'holidays' => $holidays,
            'companies' => Company::where('is_active', true)->orderBy('code')->get(),
            'branches' => Branch::where('is_active', true)->orderBy('code')->get(),
            'summary' => $summary,
            'year' => $year,
            'selectedCompanyId' => $companyId,
            'scopeOptions' => [
                'national' => 'Nasional',
                'company' => 'Perusahaan',
                'branch' => 'Cabang / Site',
            ],
            'categoryOptions' => [
                'national' => 'Libur nasional',
                'joint_leave' => 'Cuti bersama',
                'religious' => 'Hari besar agama',
                'regional' => 'Libur wilayah',
                'company' => 'Libur perusahaan',
                'custom' => 'Khusus operasional',
            ],
            'religionOptions' => ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage holiday calendar');

        $validated = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:160'],
            'holiday_date' => ['required', 'date'],
            'scope' => ['required', 'in:national,company,branch'],
            'category' => ['required', 'in:national,joint_leave,religious,regional,company,custom'],
            'religion' => ['nullable', 'string', 'max:40'],
            'region' => ['nullable', 'string', 'max:80'],
            'affects_attendance' => ['nullable', 'boolean'],
            'affects_leave' => ['nullable', 'boolean'],
            'affects_payroll' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validated['scope'] === 'national') {
            $validated['company_id'] = null;
            $validated['branch_id'] = null;
        }

        if ($validated['scope'] === 'company') {
            $validated['branch_id'] = null;
        }

        $validated['affects_attendance'] = $request->boolean('affects_attendance', true);
        $validated['affects_leave'] = $request->boolean('affects_leave', true);
        $validated['affects_payroll'] = $request->boolean('affects_payroll');
        $validated['is_active'] = true;

        $holiday = HolidayCalendar::create($validated);

        AuditLogger::record(
            'holiday_calendar.created',
            "Kalender libur {$holiday->name} pada {$holiday->holiday_date->format('d/m/Y')} dibuat.",
            $holiday,
            [],
            $holiday->toArray(),
            $request,
        );

        return redirect()->route('attendance.holidays', ['year' => $holiday->holiday_date->year])
            ->with('success', 'Kalender libur berhasil ditambahkan.');
    }
}
