@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Learning & Training</h1>
            <p class="mt-1 text-sm text-gray-500">Katalog training, enrollment peserta, attendance, sertifikat, dan evaluasi.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif

    <section class="panel rounded-2xl p-5">
        <div class="grid gap-5 md:grid-cols-4">
            @foreach([
                ['label' => 'Katalog Training', 'value' => $summary['courses'], 'color' => 'text-blue-600'],
                ['label' => 'Published', 'value' => $summary['published'], 'color' => 'text-green-600'],
                ['label' => 'Enrollment Aktif', 'value' => $summary['enrolled'], 'color' => 'text-amber-600'],
                ['label' => 'Sertifikat Terbit', 'value' => $summary['certified'], 'color' => 'text-indigo-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-5 xl:grid-cols-2">
        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Buat Katalog Training</h2>
                    @include('partials.form-help', [
                        'title' => 'Panduan katalog training',
                        'description' => 'Form ini dipakai HR untuk membuat master training yang bisa diikuti karyawan.',
                        'steps' => [
                            'Isi kode dan nama training sebagai identitas katalog.',
                            'Pilih kategori, metode belajar, provider, periode, kapasitas, dan status publish.',
                            'Gunakan scope perusahaan/cabang jika training hanya berlaku untuk area tertentu.',
                            'Setelah katalog dibuat, tambahkan peserta melalui form Enrollment Peserta.',
                        ],
                        'notes' => [
                            'Status published berarti training siap dipakai untuk enrollment.',
                            'Data ini menjadi sumber ESS learning, sertifikat, dan laporan kompetensi.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Master training untuk program internal, eksternal, compliance, HSE, dan sertifikasi.</p>
            </div>

            <form action="{{ route('performance.training-learning.courses') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Identitas Training</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kode Training</label>
                            <input name="course_code" required placeholder="Contoh: TRN-HSE-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nama Training</label>
                            <input name="title" required placeholder="Contoh: Safety Induction Site" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kategori</label>
                            <select name="category" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="technical">Technical</option>
                                <option value="leadership">Leadership</option>
                                <option value="compliance">Compliance</option>
                                <option value="hse">HSE</option>
                                <option value="soft_skill">Soft Skill</option>
                                <option value="certification">Certification</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Metode Belajar</label>
                            <select name="delivery_mode" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="classroom">Classroom</option>
                                <option value="online">Online</option>
                                <option value="hybrid">Hybrid</option>
                                <option value="on_the_job">On the job</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Provider</label>
                            <input name="provider" placeholder="Internal HR / vendor training" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status</label>
                            <select name="status" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="closed">Closed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Scope & Jadwal</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Perusahaan</label>
                            <select name="company_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Semua perusahaan</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Cabang / Site</label>
                            <select name="branch_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Semua cabang/site</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->code }} - {{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Mulai</label>
                            <input type="date" name="start_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Selesai</label>
                            <input type="date" name="end_date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Kapasitas Peserta</label>
                            <input type="number" min="0" max="10000" name="capacity" value="30" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Deskripsi</label>
                            <input name="description" placeholder="Ringkasan tujuan dan output training" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-book-open"></i> Simpan Training
                </button>
            </form>
        </section>

        <section class="panel rounded-2xl p-5">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">Enrollment Peserta</h2>
                    @include('partials.form-help', [
                        'title' => 'Panduan enrollment peserta',
                        'description' => 'Form ini dipakai untuk mendaftarkan karyawan ke training dan mencatat hasil akhirnya.',
                        'steps' => [
                            'Pilih training dan karyawan yang akan didaftarkan.',
                            'Set status enrollment dan attendance sesuai proses berjalan.',
                            'Jika training selesai, isi nomor sertifikat, tanggal terbit, file reference, score, dan feedback.',
                            'Gunakan catatan manager untuk follow-up kompetensi atau career path.',
                        ],
                        'notes' => [
                            'Satu karyawan hanya bisa didaftarkan sekali pada training yang sama.',
                            'Sertifikat dan evaluasi akan menjadi riwayat learning karyawan.',
                        ],
                    ])
                </div>
                <p class="text-sm text-gray-500">Daftarkan peserta, catat kehadiran, sertifikat, dan evaluasi hasil training.</p>
            </div>

            <form action="{{ route('performance.training-learning.enrollments') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Peserta & Training</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Training</label>
                            <select name="training_course_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Pilih training</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->course_code }} - {{ $course->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Karyawan</label>
                            <select name="employee_profile_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Pilih karyawan</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->employee_number }} - {{ $employee->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Enrollment</label>
                            <input name="enrollment_number" required placeholder="Contoh: ENR-TRN-001" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Enrollment</label>
                            <select name="enrollment_status" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="enrolled">Enrolled</option>
                                <option value="nominated">Nominated</option>
                                <option value="waitlisted">Waitlisted</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Attendance & Sertifikat</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status Kehadiran</label>
                            <select name="attendance_status" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="not_started">Belum mulai</option>
                                <option value="present">Hadir</option>
                                <option value="absent">Tidak hadir</option>
                                <option value="partial">Hadir sebagian</option>
                                <option value="excused">Izin</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Waktu Hadir</label>
                            <input type="datetime-local" name="attended_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Sertifikat</label>
                            <input name="certificate_number" placeholder="Opsional jika sudah lulus" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal Sertifikat</label>
                            <input type="datetime-local" name="certificate_issued_at" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">File Sertifikat</label>
                            <input name="certificate_file_reference" placeholder="Path file / URL sertifikat" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Evaluasi</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Score Evaluasi (0-100)</label>
                            <input type="number" min="0" max="100" step="0.01" name="evaluation_score" value="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Manager</label>
                            <input name="manager_notes" placeholder="Follow-up kompetensi / career path" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Feedback Evaluasi</label>
                            <textarea name="evaluation_feedback" rows="3" placeholder="Ringkasan hasil training dan rekomendasi berikutnya." class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                        </div>
                    </div>
                </div>

                <button class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-user-graduate"></i> Simpan Enrollment
                </button>
            </form>
        </section>
    </div>

    <div class="grid gap-5 xl:grid-cols-[.9fr_1.1fr]">
        <section class="panel rounded-2xl">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Katalog</h2>
                <p class="text-sm text-gray-500">Training yang tersedia untuk enrollment dan ESS learning.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($courses as $course)
                    <div class="px-5 py-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-semibold text-gray-900">{{ $course->title }}</div>
                                <div class="font-mono text-xs text-gray-500">{{ $course->course_code }}</div>
                            </div>
                            <span class="rounded-full {{ $course->status === 'published' ? 'bg-green-50 text-green-700' : ($course->status === 'cancelled' ? 'bg-red-50 text-red-700' : 'bg-blue-50 text-blue-700') }} px-2 py-1 text-xs font-semibold">{{ ucfirst($course->status) }}</span>
                        </div>
                        <div class="mt-3 grid gap-2 text-xs text-gray-500 sm:grid-cols-2">
                            <div>{{ ucwords(str_replace('_', ' ', $course->category)) }} - {{ ucwords(str_replace('_', ' ', $course->delivery_mode)) }}</div>
                            <div>{{ $course->start_date?->format('d M Y') ?: '-' }} - {{ $course->end_date?->format('d M Y') ?: '-' }}</div>
                            <div>{{ $course->provider ?: 'Provider belum diisi' }}</div>
                            <div>{{ $course->enrollments_count }} / {{ $course->capacity }} peserta</div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-gray-500">Belum ada katalog training.</div>
                @endforelse
            </div>
        </section>

        <section class="panel rounded-2xl">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Riwayat Enrollment</h2>
                <p class="text-sm text-gray-500">Peserta, attendance, sertifikat, dan evaluasi training.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Peserta</th>
                            <th class="px-5 py-3">Training</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Sertifikat</th>
                            <th class="px-5 py-3">Evaluasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($enrollments as $enrollment)
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $enrollment->employeeProfile?->full_name }}</div>
                                    <div class="font-mono text-xs text-gray-500">{{ $enrollment->employeeProfile?->employee_number }}</div>
                                    <div class="text-xs text-gray-500">{{ $enrollment->enrollment_number }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $enrollment->course?->title }}</div>
                                    <div class="font-mono text-xs text-gray-500">{{ $enrollment->course?->course_code }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700">{{ ucfirst($enrollment->enrollment_status) }}</span>
                                    <div class="mt-2 text-xs text-gray-500">Attendance: {{ ucwords(str_replace('_', ' ', $enrollment->attendance_status)) }}</div>
                                    <div class="text-xs text-gray-500">{{ $enrollment->attended_at?->format('d M Y H:i') ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $enrollment->certificate_number ?: '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $enrollment->certificate_issued_at?->format('d M Y') ?: '-' }}</div>
                                    <div class="break-all text-xs text-gray-500">{{ $enrollment->certificate_file_reference ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ number_format((float) $enrollment->evaluation_score, 1) }}</div>
                                    <div class="max-w-xs text-xs text-gray-500">{{ $enrollment->evaluation_feedback ?: '-' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-10 text-center text-gray-500">Belum ada enrollment training.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection
