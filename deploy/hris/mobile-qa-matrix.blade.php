@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">QA Mobile WebView</h1>
            <p class="mt-1 text-sm text-gray-500">Matriks pengujian Android dan iOS untuk shell WebView HRIS/ESS.</p>
        </div>
        <a href="{{ route('settings.mobile-bridge') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-code"></i> Mobile Bridge
        </a>
    </div>

    <section class="panel rounded-2xl p-5">
        <div class="grid gap-5 md:grid-cols-4">
            @foreach([
                ['label' => 'Skenario QA', 'value' => $summary['scenarios'], 'color' => 'text-gray-900'],
                ['label' => 'Android Ready', 'value' => $summary['ready_android'], 'color' => 'text-green-600'],
                ['label' => 'iOS Ready', 'value' => $summary['ready_ios'], 'color' => 'text-blue-600'],
                ['label' => 'Priority P0', 'value' => $summary['p0'], 'color' => 'text-red-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="panel rounded-2xl">
        <div class="border-b border-gray-100 px-5 py-4">
            <div class="flex items-center gap-2">
                <h2 class="text-lg font-semibold text-gray-900">Matriks Skenario</h2>
                @include('partials.form-help', [
                    'title' => 'Cara pakai matriks QA Mobile WebView',
                    'description' => 'Matriks ini dipakai untuk memastikan shell Android/iOS, web ESS, bridge, dan permission diuji sebelum build dibagikan ke user.',
                    'steps' => [
                        'Prioritaskan semua skenario P0 untuk smoke test harian.',
                        'Jalankan Android dan iOS terpisah karena permission, WebView engine, dan behavior file berbeda.',
                        'Update status dari Planned ke Ready/Blocked setelah test device nyata.',
                        'Catat blocker di Kanban card terkait agar WBS dan Gantt tetap sinkron.',
                    ],
                    'notes' => [
                        'Ready berarti skeleton/kontrak sudah tersedia dan lolos test otomatis.',
                        'Planned berarti butuh validasi device nyata atau implementasi native berikutnya.',
                    ],
                ])
            </div>
            <p class="text-sm text-gray-500">Coverage awal untuk login, navigasi, request center, bridge, permission, offline, dokumen, dan security.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Area</th>
                        <th class="px-5 py-3">Skenario</th>
                        <th class="px-5 py-3">Android</th>
                        <th class="px-5 py-3">iOS</th>
                        <th class="px-5 py-3">Priority</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($scenarios as $scenario)
                        <tr class="align-top hover:bg-gray-50">
                            <td class="px-5 py-4 font-semibold text-gray-900">{{ $scenario['area'] }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $scenario['scenario'] }}</td>
                            <td class="px-5 py-4"><span class="rounded-full bg-green-50 px-2 py-1 text-xs font-semibold text-green-700">{{ $scenario['android'] }}</span></td>
                            <td class="px-5 py-4"><span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700">{{ $scenario['ios'] }}</span></td>
                            <td class="px-5 py-4"><span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700">{{ $scenario['priority'] }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel rounded-2xl p-5">
        <h2 class="text-lg font-semibold text-gray-900">Artifact QA</h2>
        <div class="mt-4 grid gap-3 md:grid-cols-3">
            @foreach($artifacts as $label => $path)
                <div class="rounded-xl border border-gray-200 p-4">
                    <div class="text-sm font-semibold text-gray-900">{{ $label }}</div>
                    <div class="mt-2 break-all font-mono text-xs text-gray-500">{{ $path }}</div>
                </div>
            @endforeach
        </div>
    </section>
</div>
@endsection
