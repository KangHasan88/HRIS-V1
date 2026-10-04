<?php

namespace App\Http\Controllers;

use App\Models\EmployeeDocument;
use App\Models\EmployeeProfile;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeDocumentController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage employees');

        $documents = EmployeeDocument::with(['employeeProfile.company', 'employeeProfile.branch', 'parentDocument'])
            ->latest()
            ->limit(150)
            ->get();

        return view('employee-documents', [
            'active' => 'employee-documents',
            'employees' => EmployeeProfile::with(['company', 'branch'])->orderBy('full_name')->get(),
            'documentOptions' => EmployeeDocument::with('employeeProfile')->orderByDesc('created_at')->get(),
            'documents' => $documents,
            'summary' => [
                'total' => EmployeeDocument::count(),
                'active' => EmployeeDocument::where('status', 'active')->count(),
                'expiring' => EmployeeDocument::whereNotNull('reminder_date')->whereDate('reminder_date', '<=', now()->addDays(30))->count(),
                'ocr_ready' => EmployeeDocument::whereIn('ocr_status', ['manual', 'processed'])->count(),
                'versions' => EmployeeDocument::whereNotNull('parent_document_id')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage employees');

        $data = $request->validate([
            'employee_profile_id' => ['required', 'exists:employee_profiles,id'],
            'parent_document_id' => ['nullable', 'exists:employee_documents,id'],
            'document_number' => ['required', 'string', 'max:80', 'unique:employee_documents,document_number'],
            'document_type' => ['required', Rule::in(['ktp', 'npwp', 'contract', 'certificate', 'bpjs', 'medical', 'education', 'other'])],
            'title' => ['required', 'string', 'max:180'],
            'status' => ['required', Rule::in(['draft', 'active', 'expired', 'archived'])],
            'issued_at' => ['nullable', 'date'],
            'expired_at' => ['nullable', 'date'],
            'reminder_date' => ['nullable', 'date'],
            'file_reference' => ['nullable', 'string', 'max:180'],
            'storage_path' => ['nullable', 'string', 'max:255'],
            'ocr_status' => ['required', Rule::in(['manual', 'queued', 'processed', 'failed'])],
            'ocr_text' => ['nullable', 'string', 'max:20000'],
            'metadata_tags' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $employee = EmployeeProfile::with(['company', 'branch'])->findOrFail($data['employee_profile_id']);
        $parent = filled($data['parent_document_id'] ?? null) ? EmployeeDocument::find($data['parent_document_id']) : null;

        $document = EmployeeDocument::create([
            ...collect($data)->except('metadata_tags')->all(),
            'version' => $parent ? $parent->versions()->count() + 2 : 1,
            'metadata' => [
                'tags' => collect(explode(',', $data['metadata_tags'] ?? ''))->map(fn ($tag) => trim($tag))->filter()->values()->all(),
                'ocr_excerpt' => Str::limit($data['ocr_text'] ?? '', 160),
                'employee_number' => $employee->employee_number,
                'company' => $employee->company?->code,
                'branch' => $employee->branch?->code,
            ],
            'created_by' => auth()->id(),
        ]);

        AuditLogger::record(
            'employee_document.created',
            "Dokumen {$document->document_number} untuk {$employee->employee_number} dibuat.",
            $document,
            [],
            $document->toArray(),
            $request
        );

        return redirect()->route('hris.employee-documents')->with('success', 'Dokumen karyawan berhasil disimpan.');
    }
}
