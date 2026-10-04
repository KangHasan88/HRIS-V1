<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\TaxProfile;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaxProfileController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage payroll settings');

        $profiles = TaxProfile::with(['company', 'branch'])
            ->latest()
            ->limit(100)
            ->get();

        return view('payroll.tax-profiles', [
            'active' => 'tax-profiles',
            'profiles' => $profiles,
            'companies' => Company::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']),
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(['id', 'company_id', 'code', 'name']),
            'summary' => [
                'total' => TaxProfile::count(),
                'verified_npwp' => TaxProfile::where('is_npwp_verified', true)->count(),
                'without_npwp' => TaxProfile::whereNull('npwp')->orWhere('npwp', '')->count(),
            ],
            'ptkpOptions' => ['TK/0', 'TK/1', 'TK/2', 'TK/3', 'K/0', 'K/1', 'K/2', 'K/3'],
            'taxMethods' => ['gross' => 'Gross', 'gross_up' => 'Gross Up', 'net' => 'Net'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'employee_number' => ['nullable', 'string', 'max:50'],
            'employee_name' => ['required', 'string', 'max:255'],
            'nik' => [
                'required',
                'string',
                'max:32',
                Rule::unique('tax_profiles', 'nik')->where('company_id', $request->integer('company_id')),
            ],
            'npwp' => ['nullable', 'string', 'max:32'],
            'ptkp_status' => ['required', 'in:TK/0,TK/1,TK/2,TK/3,K/0,K/1,K/2,K/3'],
            'tax_method' => ['required', 'in:gross,gross_up,net'],
            'klu' => ['nullable', 'string', 'max:20'],
            'kpp_code' => ['nullable', 'string', 'max:30'],
            'kpp_name' => ['nullable', 'string', 'max:255'],
            'is_npwp_verified' => ['nullable', 'boolean'],
        ]);

        $data['is_npwp_verified'] = $request->boolean('is_npwp_verified');
        $data['is_active'] = true;

        $profile = TaxProfile::create($data);

        AuditLogger::record(
            'tax_profile.created',
            "Profil PPh 21 '{$profile->employee_name}' dibuat.",
            $profile,
            [],
            $profile->only(['company_id', 'branch_id', 'employee_name', 'nik', 'npwp', 'ptkp_status', 'tax_method', 'klu', 'kpp_code']),
            $request
        );

        return redirect()->route('payroll.tax-profiles')->with('success', 'Profil PPh 21 berhasil ditambahkan.');
    }
}
