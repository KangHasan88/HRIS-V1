<?php

namespace App\Http\Controllers;

use App\Models\EmployeeContract;
use App\Models\EmployeeDocument;
use App\Models\EmployeeProfile;
use App\Models\LeavePolicyType;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HrDocumentAssistantController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAnalytics();

        $question = trim((string) $request->input('question', 'cuti tahunan dan dokumen kontrak'));
        $employeeId = $request->integer('employee_id') ?: null;
        $draftType = $request->input('draft_type', 'work_certificate');
        $tone = $request->input('tone', 'formal');

        $employee = $employeeId ? EmployeeProfile::with(['company', 'branch'])->find($employeeId) : null;
        $knowledgeItems = $this->knowledgeBase($question, $employee);
        $answer = $this->answerFromKnowledge($question, $knowledgeItems, $employee);
        $draft = $this->draftLetter($draftType, $tone, $employee);

        return view('analytics.hr-document-assistant', [
            'active' => 'hr-document-assistant',
            'filters' => [
                'question' => $question,
                'employee_id' => $employeeId,
                'draft_type' => $draftType,
                'tone' => $tone,
            ],
            'employees' => EmployeeProfile::with(['company', 'branch'])
                ->where('is_active', true)
                ->orderBy('employee_number')
                ->limit(150)
                ->get(),
            'employee' => $employee,
            'answer' => $answer,
            'knowledgeItems' => $knowledgeItems,
            'draft' => $draft,
            'quickQuestions' => [
                'Berapa kuota dan aturan cuti tahunan?',
                'Dokumen kontrak apa yang akan expired?',
                'Apa syarat lampiran untuk izin sakit?',
                'Buatkan draft surat keterangan kerja.',
                'Kebijakan cuti mana yang membutuhkan approval?',
            ],
            'summary' => [
                'knowledge_items' => $knowledgeItems->count(),
                'leave_policies' => LeavePolicyType::where('status', 'active')->count(),
                'employee_documents' => EmployeeDocument::count(),
                'contract_expiring' => EmployeeContract::whereNotNull('end_date')->whereBetween('end_date', [now(), now()->addDays(60)])->count(),
            ],
        ]);
    }

    private function knowledgeBase(string $question, ?EmployeeProfile $employee): Collection
    {
        $keywords = str($question)->lower()->explode(' ')->filter()->values();
        $items = collect();

        LeavePolicyType::with('rules.company', 'rules.branch')
            ->where('status', 'active')
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->each(function (LeavePolicyType $policy) use ($items, $keywords) {
                $text = strtolower($policy->name.' '.$policy->category.' '.$policy->description);
                $matchScore = $keywords->filter(fn ($keyword) => str_contains($text, (string) $keyword))->count();

                if ($matchScore > 0 || $keywords->contains('cuti') || $keywords->contains('izin')) {
                    $items->push([
                        'source' => 'Kebijakan Cuti',
                        'title' => $policy->name,
                        'confidence' => min(95, 58 + ($matchScore * 12)),
                        'evidence' => collect([
                            'Kuota tahunan: '.number_format((float) $policy->annual_quota_days, 0).' hari',
                            'Maks per request: '.($policy->max_days_per_request ?: '-').' hari',
                            $policy->requires_attachment ? 'Butuh lampiran' : 'Lampiran tidak wajib',
                            $policy->approval_required ? 'Butuh approval' : 'Tidak butuh approval',
                            $policy->requires_balance ? 'Mengurangi saldo cuti' : 'Tidak mengurangi saldo',
                        ])->implode(' · '),
                        'action' => 'Gunakan policy ini sebagai sumber jawaban cuti/izin dan validasi ESS.',
                    ]);
                }
            });

        if ($employee) {
            EmployeeDocument::where('employee_profile_id', $employee->id)
                ->latest('issued_at')
                ->limit(12)
                ->get()
                ->each(function (EmployeeDocument $document) use ($items) {
                    $items->push([
                        'source' => 'Dokumen Karyawan',
                        'title' => $document->title,
                        'confidence' => 82,
                        'evidence' => 'Tipe '.$document->document_type.' · Status '.$document->status.' · Expired '.($document->expired_at?->format('d M Y') ?? '-'),
                        'action' => $document->expired_at && $document->expired_at->lt(now()->addDays(45))
                            ? 'Prioritaskan renewal atau reminder dokumen.'
                            : 'Dokumen bisa dipakai sebagai referensi administrasi.',
                    ]);
                });

            EmployeeContract::where('employee_profile_id', $employee->id)
                ->latest('start_date')
                ->limit(8)
                ->get()
                ->each(function (EmployeeContract $contract) use ($items) {
                    $items->push([
                        'source' => 'Kontrak Kerja',
                        'title' => $contract->contract_number,
                        'confidence' => 86,
                        'evidence' => strtoupper($contract->contract_type).' · '.$contract->start_date?->format('d M Y').' - '.($contract->end_date?->format('d M Y') ?? 'Tidak terbatas').' · '.$contract->status,
                        'action' => $contract->end_date && $contract->end_date->lt(now()->addDays(60))
                            ? 'Siapkan keputusan perpanjangan atau offboarding.'
                            : 'Kontrak masih bisa menjadi sumber draft surat.',
                    ]);
                });
        }

        if ($items->isEmpty()) {
            $items->push([
                'source' => 'Panduan HR',
                'title' => 'Belum ada sumber spesifik',
                'confidence' => 40,
                'evidence' => 'Pertanyaan belum cocok dengan data kebijakan/dokumen yang tersedia.',
                'action' => 'Tambahkan policy, dokumen, atau pilih karyawan agar jawaban lebih presisi.',
            ]);
        }

        return $items->sortByDesc('confidence')->take(10)->values();
    }

    private function answerFromKnowledge(string $question, Collection $knowledgeItems, ?EmployeeProfile $employee): array
    {
        $top = $knowledgeItems->first();
        $sources = $knowledgeItems->pluck('source')->unique()->values()->implode(', ');
        $employeeContext = $employee ? ' untuk '.$employee->full_name : '';

        return [
            'headline' => 'Jawaban sementara'.$employeeContext,
            'body' => 'Berdasarkan '.$sources.', pertanyaan "'.$question.'" paling relevan dijawab dari sumber "'.$top['title'].'". '.$top['evidence'].'.',
            'confidence' => $top['confidence'],
            'next_step' => $top['action'],
            'disclaimer' => 'Tetap review manual sebelum dipakai untuk keputusan final, surat resmi, atau komunikasi hukum/ketenagakerjaan.',
        ];
    }

    private function draftLetter(string $draftType, string $tone, ?EmployeeProfile $employee): array
    {
        $name = $employee?->full_name ?? '[Nama Karyawan]';
        $position = $employee?->position ?? '[Jabatan]';
        $company = $employee?->company?->name ?? '[Nama Perusahaan]';
        $date = now()->translatedFormat('d F Y');
        $toneLabel = $tone === 'concise' ? 'Ringkas' : ($tone === 'friendly' ? 'Humanis' : 'Formal');

        $templates = [
            'work_certificate' => [
                'title' => 'Draft Surat Keterangan Kerja',
                'subject' => 'Surat Keterangan Kerja - '.$name,
                'body' => "Pada tanggal {$date}, {$company} menerangkan bahwa {$name} bekerja sebagai {$position}. Surat ini dibuat untuk keperluan administrasi karyawan dan dapat diverifikasi oleh HR sesuai data employee profile.",
            ],
            'warning_letter' => [
                'title' => 'Draft Surat Pembinaan / Peringatan',
                'subject' => 'Surat Pembinaan - '.$name,
                'body' => "{$company} menyampaikan catatan pembinaan kepada {$name} terkait area perbaikan yang telah dibahas bersama atasan. Karyawan diminta melakukan perbaikan sesuai timeline dan HR akan memantau tindak lanjutnya.",
            ],
            'leave_confirmation' => [
                'title' => 'Draft Konfirmasi Cuti',
                'subject' => 'Konfirmasi Pengajuan Cuti - '.$name,
                'body' => "HR mengonfirmasi bahwa pengajuan cuti {$name} akan diproses berdasarkan saldo cuti, lampiran, dan approval berjenjang yang berlaku di {$company}.",
            ],
            'document_request' => [
                'title' => 'Draft Permintaan Dokumen',
                'subject' => 'Permintaan Kelengkapan Dokumen - '.$name,
                'body' => "HR meminta {$name} melengkapi dokumen karyawan yang diperlukan untuk administrasi perusahaan. Mohon dokumen dikirim melalui kanal resmi ESS atau HR sesuai instruksi.",
            ],
        ];

        $draft = $templates[$draftType] ?? $templates['work_certificate'];
        $draft['tone'] = $toneLabel;
        $draft['checklist'] = [
            'Pastikan nama, jabatan, company, dan tanggal sudah benar.',
            'Review wording oleh HR/Legal bila surat berdampak hukum.',
            'Simpan versi final di Dokumen Karyawan jika sudah disetujui.',
        ];

        return $draft;
    }

    private function authorizeAnalytics(): void
    {
        $user = auth()->user();
        abort_unless($user?->hasPermission('view attendance') || $user?->hasPermission('view delivery progress') || $user?->hasPermission('manage modules'), 403);
    }
}
