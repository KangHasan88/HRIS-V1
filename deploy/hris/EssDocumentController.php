<?php

namespace App\Http\Controllers;

use App\Models\EmployeeBpjsProfile;
use App\Models\EmployeeContract;
use App\Models\EmployeeDocument;
use App\Models\EmployeeProfile;
use App\Models\PayrollPayslip;
use App\Models\TaxWithholdingDocument;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class EssDocumentController extends Controller
{
    public function index(): View
    {
        $employee = $this->currentEmployee();
        $documents = $employee ? $this->documentsFor($employee) : collect();

        return view('ess-documents', [
            'active' => 'ess-documents',
            'employee' => $employee,
            'documents' => $documents,
            'summary' => [
                'available' => $documents->where('status', 'available')->count(),
                'waiting' => $documents->where('status', 'waiting')->count(),
                'payroll' => $documents->where('group', 'Payroll')->count(),
                'hr' => $documents->where('group', 'HR')->count(),
            ],
        ]);
    }

    public function download(string $type, int $id): Response
    {
        $employee = $this->currentEmployee();
        abort_unless($employee, 404);

        $documents = $this->documentsFor($employee);
        $document = $documents->first(fn (array $item) => $item['type'] === $type && (int) $item['id'] === $id);
        abort_unless($document && $document['status'] === 'available', 404);

        $payload = [
            'downloaded_at' => now()->timezone('Asia/Jakarta')->toIso8601String(),
            'employee' => [
                'employee_number' => $employee->employee_number,
                'full_name' => $employee->full_name,
            ],
            'document' => $document,
        ];

        return response(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . str_replace('/', '-', $document['number']) . '.json"',
        ]);
    }

    private function currentEmployee(): ?EmployeeProfile
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        return EmployeeProfile::with(['taxProfile', 'bpjsProfile', 'contracts', 'documents'])
            ->where('user_id', $user->id)
            ->orWhere('work_email', $user->email)
            ->orWhere('full_name', $user->name)
            ->first();
    }

    private function documentsFor(EmployeeProfile $employee): Collection
    {
        $items = collect();

        PayrollPayslip::where('employee_profile_id', $employee->id)
            ->where('status', 'published')
            ->latest()
            ->get()
            ->each(fn (PayrollPayslip $payslip) => $items->push([
                'id' => $payslip->id,
                'type' => 'payslip',
                'group' => 'Payroll',
                'title' => 'Slip Gaji',
                'number' => $payslip->payslip_number,
                'period' => $payslip->period,
                'status' => 'available',
                'status_label' => $payslip->acknowledged_at ? 'Sudah dikonfirmasi' : 'Siap download',
                'date' => $payslip->published_at?->format('d M Y'),
                'amount' => (float) $payslip->net_pay,
                'description' => 'Slip gaji yang sudah dipublish oleh payroll.',
            ]));

        if (! $items->contains(fn (array $item) => $item['type'] === 'payslip')) {
            $items->push([
                'id' => 0,
                'type' => 'payslip',
                'group' => 'Payroll',
                'title' => 'Slip Gaji',
                'number' => 'PAYSLIP-' . now()->format('Ym') . '-' . $employee->employee_number,
                'period' => now()->format('Y-m'),
                'status' => 'waiting',
                'status_label' => 'Menunggu publish payroll',
                'date' => null,
                'amount' => null,
                'description' => 'Slip gaji akan tersedia setelah payroll final dan dipublish.',
            ]);
        }

        TaxWithholdingDocument::where('tax_profile_id', $employee->tax_profile_id)
            ->where('status', 'published')
            ->latest()
            ->get()
            ->each(fn (TaxWithholdingDocument $document) => $items->push([
                'id' => $document->id,
                'type' => 'tax',
                'group' => 'Payroll',
                'title' => 'Bukti Potong PPh 21',
                'number' => $document->document_number,
                'period' => $document->period,
                'status' => 'available',
                'status_label' => 'Siap download',
                'date' => $document->published_at?->format('d M Y'),
                'amount' => (float) $document->pph21_amount,
                'description' => 'Dokumen pajak yang sudah dipublish oleh payroll.',
            ]));

        EmployeeBpjsProfile::where('employee_profile_id', $employee->id)
            ->get()
            ->each(fn (EmployeeBpjsProfile $bpjs) => $items->push([
                'id' => $bpjs->id,
                'type' => 'bpjs',
                'group' => 'Payroll',
                'title' => 'Kartu BPJS',
                'number' => $bpjs->bpjs_kesehatan_number ?: $bpjs->bpjs_ketenagakerjaan_number ?: 'BPJS-' . $employee->employee_number,
                'period' => $bpjs->registered_at?->format('Y-m') ?? '-',
                'status' => 'available',
                'status_label' => 'Siap download',
                'date' => $bpjs->registered_at?->format('d M Y'),
                'amount' => null,
                'description' => 'Metadata kartu BPJS Kesehatan dan Ketenagakerjaan.',
            ]));

        EmployeeContract::where('employee_profile_id', $employee->id)
            ->latest('start_date')
            ->get()
            ->each(fn (EmployeeContract $contract) => $items->push([
                'id' => $contract->id,
                'type' => 'contract',
                'group' => 'HR',
                'title' => 'Kontrak Kerja',
                'number' => $contract->contract_number,
                'period' => $contract->start_date?->format('Y-m') ?? '-',
                'status' => $contract->status === 'active' ? 'available' : 'waiting',
                'status_label' => $contract->status === 'active' ? 'Siap download' : ucfirst($contract->status),
                'date' => $contract->signed_at?->format('d M Y'),
                'amount' => null,
                'description' => $contract->document_reference ?: 'Dokumen kontrak kerja karyawan.',
            ]));

        EmployeeDocument::where('employee_profile_id', $employee->id)
            ->latest('issued_at')
            ->get()
            ->each(fn (EmployeeDocument $document) => $items->push([
                'id' => $document->id,
                'type' => 'letter',
                'group' => 'HR',
                'title' => $document->title,
                'number' => $document->document_number,
                'period' => $document->issued_at?->format('Y-m') ?? '-',
                'status' => $document->status === 'published' ? 'available' : 'waiting',
                'status_label' => $document->status === 'published' ? 'Siap download' : ucfirst($document->status),
                'date' => $document->issued_at?->format('d M Y'),
                'amount' => null,
                'description' => $document->file_reference ?: $document->document_type,
            ]));

        return $items->sortByDesc(fn (array $item) => $item['date'] ?? $item['period'])->values();
    }
}
