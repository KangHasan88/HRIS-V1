@extends('layouts.sidebar')

@section('title', 'Matriks Kompetensi')

@php
    $tone = fn ($value) => match ($value) {
        'high', 'core', 'active' => 'bg-green-50 text-green-700',
        'medium', 'leadership', 'draft' => 'bg-amber-50 text-amber-700',
        'low', 'archived' => 'bg-gray-100 text-gray-600',
        default => 'bg-blue-50 text-blue-700',
    };
@endphp

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Matriks Kompetensi</h1>
            <p class="mt-1 text-sm text-gray-500">Mapping kompetensi per role, grade, career path, level proficiency, evidence, dan rekomendasi training.</p>
        </div>
        <a href="{{ route('performance.training-learning') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"><i class="fas fa-graduation-cap mr-2"></i>Learning</a>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first() }}</div>
    @endif

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-6">
            @foreach([
                ['label' => 'Kompetensi', 'value' => $summary['competencies'], 'color' => 'text-blue-600', 'sub' => 'Katalog'],
                ['label' => 'Core', 'value' => $summary['core'], 'color' => 'text-green-600', 'sub' => 'Wajib umum'],
                ['label' => 'Requirement', 'value' => $summary['requirements'], 'color' => 'text-gray-900', 'sub' => 'Role-grade'],
                ['label' => 'Critical', 'value' => $summary['critical'], 'color' => 'text-red-600', 'sub' => 'High priority'],
                ['label' => 'Training Link', 'value' => $summary['mapped_training'], 'color' => 'text-blue-600', 'sub' => 'Mapped'],
                ['label' => 'Target Level', 'value' => $summary['avg_target_level'], 'color' => 'text-green-600', 'sub' => 'Rata-rata'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                    <div class="text-xs text-gray-400">{{ $item['sub'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Setup Matriks</h2>
                    <p class="text-sm text-gray-500">Buat katalog kompetensi, lalu mapping ke posisi, grade, dan career path.</p>
                </div>
                <div class="group relative">
                    <span class="inline-flex h-5 w-5 cursor-help items-center justify-center rounded-full bg-gray-400 text-xs font-bold text-white">?</span>
                    <div class="pointer-events-none absolute right-0 top-7 z-20 hidden w-96 rounded-xl border border-gray-200 bg-white p-4 text-xs leading-5 text-gray-600 shadow-lg group-hover:block">
                        Matriks kompetensi dipakai sebagai standar role. Required level adalah minimal kemampuan saat ini, target level adalah ekspektasi untuk perform optimal atau promosi. Mapping training membantu sistem memberi rekomendasi learning saat ada gap kompetensi.
                    </div>
                </div>
            </div>
            <div class="grid gap-5 p-5 xl:grid-cols-2">
                <form method="POST" action="{{ route('performance.competency-matrix.competencies') }}" class="rounded-xl border border-gray-200 p-5">
                    @csrf
                    <h3 class="font-semibold text-gray-900">Katalog Kompetensi</h3>
                    <p class="mb-4 text-sm text-gray-500">Definisikan kompetensi dan level proficiency.</p>
                    <div class="grid gap-4">
                        <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Company</span><select name="company_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="">Global / semua company</option>@foreach($companies as $company)<option value="{{ $company->id }}">{{ $company->code }} - {{ $company->name }}</option>@endforeach</select></label>
                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Kode</span><input name="competency_code" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="COMP-HRIS-001" required></label>
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Kategori</span><select name="category" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="technical">Technical</option><option value="core">Core</option><option value="leadership">Leadership</option><option value="behavioral">Behavioral</option><option value="compliance">Compliance</option></select></label>
                        </div>
                        <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Nama Kompetensi</span><input name="competency_name" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="HRIS Data Accuracy" required></label>
                        <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Deskripsi</span><textarea name="description" rows="2" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="Makna kompetensi dan ruang lingkupnya."></textarea></label>
                        <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Descriptor Level</span><input name="proficiency_descriptors" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="Level 1 basic, Level 3 independent, Level 5 expert"></label>
                        <div class="grid gap-3 md:grid-cols-2">
                            <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm"><input type="checkbox" name="is_core" value="1" class="rounded border-gray-300"> Kompetensi core</label>
                            <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm"><input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300"> Aktif</label>
                        </div>
                        <div class="flex justify-end"><button class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white"><i class="fas fa-save mr-2"></i>Simpan Kompetensi</button></div>
                    </div>
                </form>

                <form method="POST" action="{{ route('performance.competency-matrix.requirements') }}" class="rounded-xl border border-gray-200 p-5">
                    @csrf
                    <h3 class="font-semibold text-gray-900">Mapping Role / Grade</h3>
                    <p class="mb-4 text-sm text-gray-500">Hubungkan kompetensi ke posisi, grade, dan career path.</p>
                    <div class="grid gap-4">
                        <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Kompetensi</span><select name="competency_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm" required><option value="">Pilih kompetensi</option>@foreach($competencies as $competency)<option value="{{ $competency->id }}">{{ $competency->competency_code }} - {{ $competency->competency_name }}</option>@endforeach</select></label>
                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Nomor Requirement</span><input name="requirement_number" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="REQ-COMP-001" required></label>
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Career Path</span><input name="career_path" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="HRIS Specialist → HRIS Lead"></label>
                        </div>
                        <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Posisi</span><select name="job_position_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="">Semua posisi / by grade</option>@foreach($jobPositions as $position)<option value="{{ $position->id }}">{{ $position->code }} - {{ $position->name }} - {{ $position->jobGrade?->code }}</option>@endforeach</select></label>
                        <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Grade</span><select name="job_grade_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="">Ikuti posisi / semua grade</option>@foreach($jobGrades as $grade)<option value="{{ $grade->id }}">{{ $grade->code }} - {{ $grade->name }} L{{ $grade->level }}</option>@endforeach</select></label>
                        <div class="grid gap-4 md:grid-cols-4">
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Min Level</span><input type="number" name="required_level" min="1" max="5" value="2" class="mt-1 w-full rounded-lg border-gray-300 text-sm" required></label>
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Target</span><input type="number" name="target_level" min="1" max="5" value="3" class="mt-1 w-full rounded-lg border-gray-300 text-sm" required></label>
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Bobot</span><input type="number" step="0.01" name="weight" value="100" class="mt-1 w-full rounded-lg border-gray-300 text-sm" required></label>
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Status</span><select name="status" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="active">Active</option><option value="draft">Draft</option><option value="archived">Archived</option></select></label>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Criticality</span><select name="criticality" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="medium">Medium</option><option value="high">High</option><option value="low">Low</option></select></label>
                            <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Assessment</span><select name="assessment_method" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="manager_review">Manager review</option><option value="assessment">Assessment</option><option value="certification">Certification</option><option value="portfolio">Portfolio</option><option value="interview">Interview</option></select></label>
                        </div>
                        <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Training Rekomendasi</span><select name="training_course_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="">Belum link training</option>@foreach($trainingCourses as $course)<option value="{{ $course->id }}">{{ $course->course_code }} - {{ $course->title }}</option>@endforeach</select></label>
                        <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Evidence</span><input name="evidence_examples" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="Project evidence, sertifikat, assessment result"></label>
                        <label class="block"><span class="text-xs font-semibold uppercase text-gray-500">Development Action</span><textarea name="development_action" rows="2" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="Aksi pengembangan jika ada gap."></textarea></label>
                        <div class="flex justify-end"><button class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white"><i class="fas fa-link mr-2"></i>Simpan Mapping</button></div>
                    </div>
                </form>
            </div>
        </section>

        <aside class="space-y-4">
            <section class="rounded-2xl border border-blue-100 bg-blue-50 p-5">
                <h2 class="text-sm font-semibold text-blue-900">Cara Baca Level</h2>
                <div class="mt-3 space-y-2 text-sm text-blue-900/75">
                    <p><b>Level 1:</b> basic, butuh arahan.</p>
                    <p><b>Level 3:</b> mandiri, bisa handle pekerjaan normal.</p>
                    <p><b>Level 5:</b> expert, bisa coach dan membuat standar.</p>
                </div>
            </section>
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900">Coverage</h2>
                <div class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Critical competency</span><span class="font-semibold text-red-600">{{ $summary['critical'] }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Training mapped</span><span class="font-semibold text-blue-600">{{ $summary['mapped_training'] }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Avg target level</span><span class="font-semibold text-green-600">{{ $summary['avg_target_level'] }}</span></div>
                </div>
            </section>
        </aside>
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Matriks</h2>
            <p class="text-sm text-gray-500">Role, grade, career path, level, evidence, dan training terkait.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr><th class="px-5 py-3 text-left">Kompetensi</th><th class="px-5 py-3 text-left">Role / Grade</th><th class="px-5 py-3 text-left">Career Path</th><th class="px-5 py-3 text-left">Level</th><th class="px-5 py-3 text-left">Training</th><th class="px-5 py-3 text-left">Status</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($requirements as $requirement)
                        <tr>
                            <td class="px-5 py-4"><div class="font-semibold text-gray-900">{{ $requirement->competency?->competency_name }}</div><div class="text-xs text-gray-500">{{ $requirement->requirement_number }} · {{ $requirement->competency?->competency_code }}</div></td>
                            <td class="px-5 py-4 text-gray-600">{{ $requirement->jobPosition?->name ?? 'Semua posisi' }}<div class="text-xs text-gray-400">{{ $requirement->jobGrade?->code ?? $requirement->jobPosition?->jobGrade?->code ?? '-' }}</div></td>
                            <td class="px-5 py-4 max-w-xs text-gray-600">{{ $requirement->career_path ?: '-' }}</td>
                            <td class="px-5 py-4"><div class="font-semibold text-blue-600">L{{ $requirement->required_level }} → L{{ $requirement->target_level }}</div><div class="text-xs text-gray-500">Bobot {{ $requirement->weight }}%</div></td>
                            <td class="px-5 py-4 text-gray-600">{{ $requirement->trainingCourse?->title ?? 'Belum link training' }}</td>
                            <td class="px-5 py-4"><span class="rounded-full px-3 py-1 text-xs font-semibold {{ $tone($requirement->criticality) }}">{{ ucfirst($requirement->criticality) }}</span><span class="ml-2 rounded-full px-3 py-1 text-xs font-semibold {{ $tone($requirement->status) }}">{{ ucfirst($requirement->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-8 text-center text-gray-500">Belum ada matriks kompetensi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
