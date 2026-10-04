<?php

namespace App\Http\Controllers;

use App\Models\PayrollBpjsProfile;
use App\Models\TaxProfile;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class IntegrationExportTemplateController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage payroll settings');

        $taxRows = $this->taxRows();
        $bpjsRows = $this->bpjsRows();

        return view('integration-export-templates', [
            'active' => 'integration-export-templates',
            'taxRows' => $taxRows->take(20),
            'bpjsRows' => $bpjsRows->take(20),
            'summary' => [
                'tax_rows' => $taxRows->count(),
                'tax_valid' => $taxRows->where('valid', true)->count(),
                'bpjs_rows' => $bpjsRows->count(),
                'bpjs_valid' => $bpjsRows->where('valid', true)->count(),
                'issues' => $taxRows->where('valid', false)->count() + $bpjsRows->where('valid', false)->count(),
            ],
        ]);
    }

    public function download(string $type): Response
    {
        Gate::authorize('manage payroll settings');

        abort_unless(in_array($type, ['tax', 'bpjs'], true), 404);

        $rows = $type === 'tax' ? $this->taxRows() : $this->bpjsRows();
        $headers = $type === 'tax'
            ? ['employee_number', 'employee_name', 'nik', 'npwp', 'ptkp_status', 'tax_method', 'klu', 'kpp_code', 'validation_status', 'validation_notes']
            : ['employee_number', 'employee_name', 'bpjs_kesehatan', 'bpjs_ketenagakerjaan', 'bpjs_class', 'effective_date', 'health_base', 'employment_base', 'health_employee_rate', 'health_employer_rate', 'jht_employee_rate', 'jht_employer_rate', 'jp_employee_rate', 'jp_employer_rate', 'validation_status', 'validation_notes'];

        $csv = collect([$headers])
            ->merge($rows->map(fn (array $row) => collect($headers)->map(fn (string $key) => $row[$key] ?? null)->all()))
            ->map(fn (array $row) => collect($row)->map(fn ($value) => '"' . str_replace('"', '""', (string) $value) . '"')->implode(','))
            ->implode("\n");

        return response($csv . "\n", 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . ($type === 'tax' ? 'template-pajak' : 'template-bpjs') . '-' . now()->format('Ymd') . '.csv"',
        ]);
    }

    private function taxRows(): Collection
    {
        return TaxProfile::with(['company', 'branch'])
            ->orderBy('employee_name')
            ->limit(500)
            ->get()
            ->map(function (TaxProfile $profile): array {
                $issues = [];
                if (! $profile->nik) {
                    $issues[] = 'NIK kosong';
                }
                if (! $profile->npwp) {
                    $issues[] = 'NPWP kosong';
                }
                if (! $profile->is_npwp_verified) {
                    $issues[] = 'NPWP belum verified';
                }
                if (! $profile->ptkp_status) {
                    $issues[] = 'PTKP kosong';
                }

                return [
                    'employee_number' => $profile->employee_number,
                    'employee_name' => $profile->employee_name,
                    'nik' => $profile->nik,
                    'npwp' => $profile->npwp,
                    'ptkp_status' => $profile->ptkp_status,
                    'tax_method' => $profile->tax_method,
                    'klu' => $profile->klu,
                    'kpp_code' => $profile->kpp_code,
                    'validation_status' => $issues === [] ? 'VALID' : 'NEED_REVIEW',
                    'validation_notes' => implode('; ', $issues),
                    'valid' => $issues === [],
                ];
            });
    }

    private function bpjsRows(): Collection
    {
        return PayrollBpjsProfile::with('employeeProfile')
            ->orderByDesc('effective_date')
            ->limit(500)
            ->get()
            ->map(function (PayrollBpjsProfile $profile): array {
                $employee = $profile->employeeProfile;
                $issues = [];
                if (! $employee?->employee_number) {
                    $issues[] = 'Employee number kosong';
                }
                if (! $profile->bpjs_kesehatan_number) {
                    $issues[] = 'BPJS Kesehatan kosong';
                }
                if (! $profile->bpjs_ketenagakerjaan_number) {
                    $issues[] = 'BPJS Ketenagakerjaan kosong';
                }
                if ($profile->verification_status !== 'verified') {
                    $issues[] = 'BPJS belum verified';
                }
                if (! $profile->is_active) {
                    $issues[] = 'Profil BPJS nonaktif';
                }

                return [
                    'employee_number' => $employee?->employee_number,
                    'employee_name' => $employee?->full_name,
                    'bpjs_kesehatan' => $profile->bpjs_kesehatan_number,
                    'bpjs_ketenagakerjaan' => $profile->bpjs_ketenagakerjaan_number,
                    'bpjs_class' => $profile->bpjs_class,
                    'effective_date' => $profile->effective_date?->format('Y-m-d'),
                    'health_base' => $profile->health_contribution_base,
                    'employment_base' => $profile->employment_contribution_base,
                    'health_employee_rate' => $profile->health_employee_rate,
                    'health_employer_rate' => $profile->health_employer_rate,
                    'jht_employee_rate' => $profile->jht_employee_rate,
                    'jht_employer_rate' => $profile->jht_employer_rate,
                    'jp_employee_rate' => $profile->jp_employee_rate,
                    'jp_employer_rate' => $profile->jp_employer_rate,
                    'validation_status' => $issues === [] ? 'VALID' : 'NEED_REVIEW',
                    'validation_notes' => implode('; ', $issues),
                    'valid' => $issues === [],
                ];
            });
    }
}
