<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\TaxMonthlyExportTemplate;
use App\Models\TaxMonthlyCalculation;
use App\Models\TaxMonthlyReportPackage;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TaxMonthlyReportPackageController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage payroll settings');

        return view('payroll.tax-monthly-report-packages', [
            'active' => 'tax-report-packages',
            'companies' => Company::where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
            'packages' => TaxMonthlyReportPackage::with(['company', 'exportTemplates'])->latest()->limit(100)->get(),
            'templates' => TaxMonthlyExportTemplate::with('package.company')->latest()->limit(50)->get(),
            'summary' => [
                'packages' => TaxMonthlyReportPackage::count(),
                'final' => TaxMonthlyReportPackage::where('status', 'final')->count(),
                'employees' => TaxMonthlyReportPackage::sum('employee_count'),
                'total_pph21' => TaxMonthlyReportPackage::sum('total_pph21'),
                'templates' => TaxMonthlyExportTemplate::count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'period' => ['required', 'date_format:Y-m'],
            'status' => ['required', 'in:draft,final,ready_for_coretax'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $calculations = TaxMonthlyCalculation::with(['taxProfile', 'taxProfile.company'])
            ->where('period', $data['period'])
            ->where('calculation_status', 'final')
            ->when($data['company_id'] ?? null, fn ($query, $companyId) => $query->where('company_id', $companyId))
            ->orderBy('tax_profile_id')
            ->get();

        $detailSnapshot = $calculations->map(fn (TaxMonthlyCalculation $calculation) => [
            'employee_number' => $calculation->taxProfile?->employee_number,
            'employee_name' => $calculation->taxProfile?->employee_name,
            'company_code' => $calculation->taxProfile?->company?->code,
            'ptkp_status' => $calculation->source_snapshot['ptkp_status'] ?? null,
            'tax_method' => $calculation->tax_method,
            'gross_income' => (float) $calculation->gross_income,
            'tax_base' => (float) $calculation->ter_base_amount,
            'pph21_amount' => (float) $calculation->pph21_amount,
            'take_home_pay' => (float) $calculation->take_home_pay,
        ])->values();

        $validationNotes = [];
        if ($calculations->isEmpty()) {
            $validationNotes[] = 'Belum ada kalkulasi PPh 21 final untuk periode ini.';
        }
        if ($detailSnapshot->contains(fn ($item) => blank($item['employee_number']) || blank($item['employee_name']))) {
            $validationNotes[] = 'Ada baris karyawan yang belum lengkap untuk template Coretax.';
        }

        $package = TaxMonthlyReportPackage::create([
            'company_id' => $data['company_id'] ?? null,
            'period' => $data['period'],
            'package_number' => $this->packageNumber($data['period']),
            'status' => $data['status'],
            'employee_count' => $calculations->pluck('tax_profile_id')->unique()->count(),
            'total_gross_income' => $calculations->sum('gross_income'),
            'total_tax_base' => $calculations->sum('ter_base_amount'),
            'total_pph21' => $calculations->sum('pph21_amount'),
            'summary_snapshot' => [
                'period' => $data['period'],
                'calculation_count' => $calculations->count(),
                'tax_methods' => $calculations->groupBy('tax_method')->map->count()->all(),
                'notes' => $data['notes'] ?? null,
            ],
            'detail_snapshot' => $detailSnapshot->all(),
            'validation_notes' => $validationNotes,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'tax_monthly_report_package.created',
            "Paket laporan pajak bulanan {$package->package_number} dibuat.",
            $package,
            [],
            $package->only(['period', 'package_number', 'status', 'employee_count', 'total_pph21']),
            $request
        );

        return redirect()->route('payroll.tax-report-packages')->with('success', 'Paket laporan pajak bulanan berhasil dibuat.');
    }

    public function generateTemplate(Request $request, TaxMonthlyReportPackage $package): RedirectResponse
    {
        Gate::authorize('manage payroll settings');

        $data = $request->validate([
            'schema_version' => ['required', 'string', 'max:30'],
            'format' => ['required', 'in:csv,xlsx,json'],
            'status' => ['required', 'in:generated,validated,ready_to_export'],
        ]);

        $columns = $this->coretaxColumns($data['schema_version']);
        $payload = collect($package->detail_snapshot ?? [])->map(function (array $row) use ($package) {
            return [
                'masa_pajak' => $package->period,
                'npwp_pemotong' => $package->company?->tax_id,
                'kode_karyawan' => $row['employee_number'] ?? null,
                'nama_karyawan' => $row['employee_name'] ?? null,
                'status_ptkp' => $row['ptkp_status'] ?? null,
                'metode_pajak' => $row['tax_method'] ?? null,
                'penghasilan_bruto' => $row['gross_income'] ?? 0,
                'dasar_pajak' => $row['tax_base'] ?? 0,
                'pph21' => $row['pph21_amount'] ?? 0,
                'take_home_pay' => $row['take_home_pay'] ?? 0,
            ];
        })->values()->all();

        $template = TaxMonthlyExportTemplate::create([
            'tax_monthly_report_package_id' => $package->id,
            'template_number' => $this->templateNumber($package->period),
            'schema_version' => $data['schema_version'],
            'format' => $data['format'],
            'status' => $data['status'],
            'columns_schema' => $columns,
            'payload_snapshot' => $payload,
            'checksum' => hash('sha256', json_encode([$columns, $payload], JSON_UNESCAPED_UNICODE)),
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'tax_monthly_export_template.generated',
            "Template ekspor Coretax {$template->template_number} dibuat.",
            $template,
            [],
            $template->only(['template_number', 'schema_version', 'format', 'status', 'checksum']),
            $request
        );

        return redirect()->route('payroll.tax-report-packages')->with('success', 'Template ekspor Coretax berhasil dibuat.');
    }

    private function packageNumber(string $period): string
    {
        return 'TAXPKG-' . str_replace('-', '', $period) . '-' . Str::upper(Str::random(6));
    }

    private function templateNumber(string $period): string
    {
        return 'CORETAX-' . str_replace('-', '', $period) . '-' . Str::upper(Str::random(6));
    }

    private function coretaxColumns(string $schemaVersion): array
    {
        return [
            ['key' => 'masa_pajak', 'label' => 'Masa Pajak', 'required' => true, 'schema_version' => $schemaVersion],
            ['key' => 'npwp_pemotong', 'label' => 'NPWP Pemotong', 'required' => false, 'schema_version' => $schemaVersion],
            ['key' => 'kode_karyawan', 'label' => 'Kode Karyawan', 'required' => true, 'schema_version' => $schemaVersion],
            ['key' => 'nama_karyawan', 'label' => 'Nama Karyawan', 'required' => true, 'schema_version' => $schemaVersion],
            ['key' => 'status_ptkp', 'label' => 'Status PTKP', 'required' => true, 'schema_version' => $schemaVersion],
            ['key' => 'metode_pajak', 'label' => 'Metode Pajak', 'required' => true, 'schema_version' => $schemaVersion],
            ['key' => 'penghasilan_bruto', 'label' => 'Penghasilan Bruto', 'required' => true, 'schema_version' => $schemaVersion],
            ['key' => 'dasar_pajak', 'label' => 'Dasar Pajak', 'required' => true, 'schema_version' => $schemaVersion],
            ['key' => 'pph21', 'label' => 'PPh 21', 'required' => true, 'schema_version' => $schemaVersion],
            ['key' => 'take_home_pay', 'label' => 'Take Home Pay', 'required' => false, 'schema_version' => $schemaVersion],
        ];
    }
}
