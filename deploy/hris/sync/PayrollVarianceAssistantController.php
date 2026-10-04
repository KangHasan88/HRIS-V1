<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\PayrollRun;
use App\Models\PayrollRunAnomaly;
use App\Models\PayrollRunValidation;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PayrollVarianceAssistantController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('manage payroll settings');

        $companyId = $request->integer('company_id') ?: null;
        $period = $request->input('period');
        $severity = $request->input('severity');

        $runs = PayrollRun::with(['company', 'anomalies.employee', 'validations'])
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->when($period, fn ($query) => $query->where('period', $period))
            ->latest('cutoff_end')
            ->latest()
            ->limit(80)
            ->get();

        $anomalies = PayrollRunAnomaly::with(['payrollRun.company', 'employee.branch'])
            ->whereIn('payroll_run_id', $runs->pluck('id'))
            ->when($severity, fn ($query) => $query->where('severity', $severity))
            ->orderByRaw("CASE severity WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END")
            ->latest()
            ->limit(160)
            ->get();

        $validations = PayrollRunValidation::with('payrollRun.company')
            ->whereIn('payroll_run_id', $runs->pluck('id'))
            ->whereIn('status', ['failed', 'warning'])
            ->latest()
            ->limit(120)
            ->get();

        return view('analytics.payroll-variance-assistant', [
            'active' => 'payroll-variance-assistant',
            'companies' => Company::orderBy('code')->get(),
            'filters' => [
                'company_id' => $companyId,
                'period' => $period,
                'severity' => $severity,
            ],
            'runs' => $runs,
            'anomalies' => $anomalies,
            'validations' => $validations,
            'summary' => [
                'runs' => $runs->count(),
                'anomalies' => $anomalies->count(),
                'critical_high' => $anomalies->whereIn('severity', ['critical', 'high'])->count(),
                'open' => $anomalies->where('status', 'open')->count(),
                'failed_validation' => $validations->where('status', 'failed')->count(),
                'net_variance' => (float) $anomalies->sum(fn (PayrollRunAnomaly $anomaly) => (float) $anomaly->current_amount - (float) $anomaly->previous_amount),
            ],
            'explanations' => $this->buildExplanations($anomalies),
        ]);
    }

    public function review(Request $request, PayrollRunAnomaly $anomaly): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $request->validate([
            'status' => ['required', Rule::in(['open', 'reviewed', 'resolved', 'ignored'])],
            'review_note' => ['nullable', 'string', 'max:800'],
        ]);

        $before = $anomaly->toArray();
        $evidence = $anomaly->evidence_payload ?? [];
        $evidence['review_note'] = $data['review_note'] ?? null;
        $evidence['reviewed_by'] = auth()->user()?->name;
        $evidence['reviewed_at'] = now()->toDateTimeString();

        $anomaly->forceFill([
            'status' => $data['status'],
            'evidence_payload' => $evidence,
        ])->save();

        AuditLogger::record(
            'payroll_variance_anomaly.reviewed',
            "Anomali payroll {$anomaly->anomaly_code} direview menjadi {$anomaly->status}.",
            $anomaly,
            $before,
            $anomaly->toArray(),
            $request,
        );

        return redirect()->route('analytics.payroll-variance-assistant')->with('success', 'Status anomali payroll berhasil diperbarui.');
    }

    private function buildExplanations($anomalies)
    {
        return $anomalies->take(12)->map(function (PayrollRunAnomaly $anomaly) {
            $previous = (float) $anomaly->previous_amount;
            $current = (float) $anomaly->current_amount;
            $delta = $current - $previous;
            $absDelta = abs($delta);
            $direction = $delta >= 0 ? 'naik' : 'turun';
            $percent = (float) $anomaly->variance_percent;
            $employeeName = $anomaly->employee?->full_name ?: 'Karyawan belum terhubung';

            $likelyCause = match (true) {
                str_contains(strtolower($anomaly->anomaly_code), 'gross') => 'Kemungkinan berasal dari perubahan gaji pokok, allowance, adjustment earning, atau komponen lembur.',
                str_contains(strtolower($anomaly->anomaly_code), 'deduction') => 'Kemungkinan berasal dari perubahan potongan, BPJS, PPh 21, pinjaman, atau adjustment deduction.',
                str_contains(strtolower($anomaly->anomaly_code), 'net') => 'Kemungkinan berasal dari kombinasi perubahan gross pay dan deduction.',
                $percent >= 30 => 'Persentase varians tinggi, wajib cocokkan salary assignment, adjustment, absensi, dan pajak.',
                default => 'Perubahan masih perlu dicocokkan dengan komponen payroll periode berjalan.',
            };

            $recommendation = match ($anomaly->severity) {
                'critical' => 'Tahan publish slip/transfer untuk karyawan ini sampai payroll dan HR menyelesaikan review.',
                'high' => 'Review komponen penyebab dan minta approval payroll lead sebelum payroll run lanjut.',
                'medium' => 'Cek sample evidence dan bandingkan dengan periode sebelumnya.',
                default => 'Monitor di payroll run berikutnya.',
            };

            return [
                'id' => $anomaly->id,
                'title' => $employeeName.' '.$direction.' Rp '.number_format($absDelta, 0, ',', '.'),
                'subtitle' => $anomaly->payrollRun?->run_number.' - '.$anomaly->anomaly_name,
                'severity' => $anomaly->severity,
                'status' => $anomaly->status,
                'body' => "Nilai {$direction} dari Rp ".number_format($previous, 0, ',', '.').' menjadi Rp '.number_format($current, 0, ',', '.')." ({$percent}%). {$likelyCause}",
                'recommendation' => $recommendation,
            ];
        });
    }
}
