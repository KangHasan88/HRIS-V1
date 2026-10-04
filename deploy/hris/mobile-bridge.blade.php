@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Mobile WebView Bridge</h1>
            <p class="mt-1 text-sm text-gray-500">Kontrak JavaScript SDK untuk Android/iOS full WebView ESS.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ $sdkUrl }}" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                <i class="fas fa-code"></i>
                Lihat SDK
            </a>
            <button type="button" id="bridgeProbeButton" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">
                <i class="fas fa-mobile-screen-button"></i>
                Test Bridge
            </button>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="grid gap-5 md:grid-cols-4">
            @foreach([
                ['label' => 'Host Saat Ini', 'value' => 'Browser/WebView', 'color' => 'text-blue-600'],
                ['label' => 'Capability', 'value' => count($capabilities), 'color' => 'text-green-600'],
                ['label' => 'Event Contract', 'value' => count($events), 'color' => 'text-indigo-600'],
                ['label' => 'SDK Version', 'value' => '1.0', 'color' => 'text-gray-900'],
            ] as $item)
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold {{ $item['color'] }}">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-[1.4fr_0.8fr]">
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Capability Contract</h2>
                <p class="text-sm text-gray-500">Daftar fungsi native yang boleh dipanggil dari ESS WebView.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3">Capability</th>
                            <th class="px-5 py-3">Request</th>
                            <th class="px-5 py-3">Response</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($capabilities as $capability)
                            <tr class="align-top hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-900">{{ $capability['label'] }}</div>
                                    <div class="font-mono text-xs text-gray-500">{{ $capability['name'] }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <code class="block max-w-sm whitespace-normal break-words rounded-lg bg-gray-50 px-3 py-2 text-xs text-gray-700">{{ $capability['request'] }}</code>
                                </td>
                                <td class="px-5 py-4">
                                    <code class="block max-w-sm whitespace-normal break-words rounded-lg bg-gray-50 px-3 py-2 text-xs text-gray-700">{{ $capability['response'] }}</code>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full {{ $capability['status'] === 'Ready' ? 'bg-green-50 text-green-600' : 'bg-yellow-50 text-yellow-700' }} px-3 py-1 text-xs font-semibold">{{ $capability['status'] }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-5">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-5 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Event</h2>
                    <p class="text-sm text-gray-500">Format komunikasi native dan web.</p>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($events as $event)
                        <div class="px-5 py-4">
                            <div class="flex items-center justify-between gap-3">
                                <div class="font-mono text-sm font-semibold text-gray-900">{{ $event['name'] }}</div>
                                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">{{ $event['direction'] }}</span>
                            </div>
                            <code class="mt-3 block whitespace-normal break-words rounded-lg bg-gray-50 px-3 py-2 text-xs text-gray-700">{{ $event['payload'] }}</code>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Probe Result</h2>
                        <p class="text-sm text-gray-500">Hasil deteksi host dan fallback SDK.</p>
                    </div>
                    <span id="bridgeHostBadge" class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">Pending</span>
                </div>
                <pre id="bridgeProbeOutput" class="mt-4 max-h-56 overflow-auto rounded-xl bg-gray-950 p-4 text-xs text-gray-100">Menunggu test...</pre>
            </div>
        </div>
    </div>
</div>

<script src="{{ $sdkUrl }}"></script>
<script>
    const output = document.getElementById('bridgeProbeOutput');
    const badge = document.getElementById('bridgeHostBadge');
    const button = document.getElementById('bridgeProbeButton');

    function printProbe(data) {
        output.textContent = JSON.stringify(data, null, 2);
        badge.textContent = window.KurmigoBridge.host();
    }

    window.addEventListener('kurmigo:bridge-ready', (event) => {
        printProbe(event.detail);
    });

    button.addEventListener('click', async () => {
        const result = await window.KurmigoBridge.getDeviceInfo();
        printProbe(result);
    });
</script>
@endsection
