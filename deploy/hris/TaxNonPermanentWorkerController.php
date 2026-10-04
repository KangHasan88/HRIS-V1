<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\TaxNonPermanentCalculation;
use App\Models\TaxNonPermanentWorker;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TaxNonPermanentWorkerController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage payroll settings');

        return view('payroll.tax-non-permanent-workers', [
            'active' => 'tax-non-permanent',
            'companies' => Company::where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
            'branches' => Branch::where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
            'workers' => TaxNonPermanentWorker::latest()->limit(100)->get(),
            'calculations' => TaxNonPermanentCalculation::with('worker')->latest()->limit(100)->get(),
            'workerTypes' => [
                'daily_worker' => 'Pekerja Harian',
                'freelancer' => 'Freelancer',
                'consultant' => 'Konsultan',
                'non_employee' => 'Bukan Pegawai',
            ],
            'summary' => [
                'workers' => TaxNonPermanentWorker::count(),
                'calculations' => TaxNonPermanentCalculation::count(),
                'final' => TaxNonPermanentCalculation::where('calculation_status', 'final')->count(),
                'total_tax' => TaxNonPermanentCalculation::sum('pph21_amount'),
            ],
        ]);
    }

    public function storeWorker(Request $request): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'worker_name' => ['required', 'string', 'max:255'],
            'worker_number' => ['nullable', 'string', 'max:50'],
            'nik' => ['nullable', 'string', 'max:32'],
            'npwp' => ['nullable', 'string', 'max:32'],
            'worker_type' => ['required', 'in:daily_worker,freelancer,consultant,non_employee'],
            'ptkp_status' => ['required', 'in:TK/0,TK/1,TK/2,TK/3,K/0,K/1,K/2,K/3'],
            'is_npwp_verified' => ['nullable', 'boolean'],
        ]);

        $worker = TaxNonPermanentWorker::create([
            ...$data,
            'is_npwp_verified' => $request->boolean('is_npwp_verified'),
            'is_active' => true,
        ]);

        AuditLogger::record(
            'tax_non_permanent.worker_created',
            "Worker pajak non permanen {$worker->worker_name} dibuat.",
            $worker,
            [],
            $worker->toArray(),
            $request
        );

        return redirect()->route('payroll.tax-non-permanent')->with('success', 'Worker non permanen berhasil ditambahkan.');
    }

    public function calculate(Request $request): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $request->validate([
            'tax_non_permanent_worker_id' => ['required', 'exists:tax_non_permanent_workers,id'],
            'period' => ['required', 'date_format:Y-m'],
            'work_days' => ['required', 'integer', 'min:1', 'max:31'],
            'daily_rate' => ['required', 'numeric', 'min:0'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:1'],
            'calculation_status' => ['required', 'in:draft,final'],
        ]);

        $worker = TaxNonPermanentWorker::findOrFail($data['tax_non_permanent_worker_id']);
        $gross = (float) $data['daily_rate'] * (int) $data['work_days'];
        $taxableBase = match ($worker->worker_type) {
            'daily_worker' => max(0, $gross - 450000),
            'freelancer', 'consultant', 'non_employee' => $gross * 0.5,
            default => $gross,
        };
        $tax = round($taxableBase * (float) $data['tax_rate'], 2);

        $calculation = TaxNonPermanentCalculation::create([
            'tax_non_permanent_worker_id' => $worker->id,
            'company_id' => $worker->company_id,
            'branch_id' => $worker->branch_id,
            'period' => $data['period'],
            'work_days' => $data['work_days'],
            'daily_rate' => $data['daily_rate'],
            'gross_income' => $gross,
            'taxable_base' => $taxableBase,
            'tax_rate' => $data['tax_rate'],
            'pph21_amount' => $tax,
            'calculation_status' => $data['calculation_status'],
            'calculation_steps' => [
                ['label' => 'Bruto', 'formula' => 'Jumlah hari kerja x rate harian', 'amount' => $gross],
                ['label' => 'Dasar pajak', 'formula' => $worker->worker_type === 'daily_worker' ? 'Bruto - batas harian non kena pajak' : '50% dari bruto untuk bukan pegawai', 'amount' => $taxableBase],
                ['label' => 'PPh 21', 'formula' => 'Dasar pajak x tarif input', 'amount' => $tax],
            ],
            'source_snapshot' => [
                'worker_name' => $worker->worker_name,
                'worker_type' => $worker->worker_type,
                'ptkp_status' => $worker->ptkp_status,
                'npwp' => $worker->npwp,
            ],
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'tax_non_permanent.calculated',
            "PPh 21 worker non permanen {$worker->worker_name} periode {$calculation->period} dihitung.",
            $calculation,
            [],
            $calculation->only(['tax_non_permanent_worker_id', 'period', 'gross_income', 'taxable_base', 'pph21_amount']),
            $request
        );

        return redirect()->route('payroll.tax-non-permanent')->with('success', 'PPh 21 worker non permanen berhasil dihitung.');
    }
}
