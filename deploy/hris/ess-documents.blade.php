@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Dokumen</h1>
            <p class="mt-1 text-sm text-gray-500">Pusat dokumen karyawan untuk slip gaji, pajak, BPJS, kontrak, dan surat HR.</p>
        </div>
        <a href="{{ route('hris.ess') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            <i class="fas fa-mobile-screen-button"></i>
            Beranda
        </a>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-4">
            @foreach([
                ['label' => 'Siap Download', 'value' => $summary['available'], 'color' => 'text-green-600'],
                ['label' => 'Menunggu', 'value' => $summary['waiting'], 'color' => 'text-yellow-600'],
                ['label' => 'Payroll', 'value' => $summary['payroll'], 'color' => 'text-blue-600'],
                ['label' => 'HR', 'value' => $summary['hr'], 'color' => 'text-indigo-600'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    @if(! $employee)
        <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500">
            Profil karyawan belum terhubung ke akun ini.
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse($documents as $document)
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $document['group'] }} / {{ strtoupper($document['type']) }}</div>
                            <h2 class="mt-1 text-base font-semibold text-gray-900">{{ $document['title'] }}</h2>
                            <div class="mt-1 font-mono text-xs text-gray-500">{{ $document['number'] }}</div>
                        </div>
                        <span class="rounded-full {{ $document['status'] === 'available' ? 'bg-green-50 text-green-600' : 'bg-yellow-50 text-yellow-600' }} px-3 py-1 text-xs font-semibold">{{ $document['status_label'] }}</span>
                    </div>
                    <div class="mt-5 space-y-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <span class="text-gray-500">Periode</span>
                            <span class="font-semibold text-gray-900">{{ $document['period'] ?: '-' }}</span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span class="text-gray-500">Tanggal</span>
                            <span class="font-semibold text-gray-900">{{ $document['date'] ?: '-' }}</span>
                        </div>
                        @if($document['amount'] !== null)
                            <div class="flex justify-between gap-4">
                                <span class="text-gray-500">Nominal</span>
                                <span class="font-semibold text-red-600">Rp {{ number_format($document['amount'], 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="mt-4 rounded-lg bg-gray-50 p-3 text-xs text-gray-500">{{ $document['description'] }}</div>
                    @if($document['status'] === 'available')
                        <a href="{{ route('ess.documents.download', ['type' => $document['type'], 'id' => $document['id']]) }}" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">
                            <i class="fas fa-download"></i>
                            Download
                        </a>
                    @else
                        <button disabled class="mt-4 inline-flex w-full cursor-not-allowed items-center justify-center gap-2 rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-400">
                            <i class="fas fa-clock"></i>
                            Belum Tersedia
                        </button>
                    @endif
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500">
                    Belum ada dokumen untuk karyawan ini.
                </div>
            @endforelse
        </div>
    @endif
</div>
@endsection
