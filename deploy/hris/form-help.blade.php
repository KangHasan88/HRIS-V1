@php
    $title = $title ?? 'Panduan form';
    $description = $description ?? 'Gunakan form ini sesuai alur proses.';
    $steps = collect($steps ?? [])->filter()->values();
    $notes = collect($notes ?? [])->filter()->values();
@endphp

<span role="button" tabindex="0" class="group relative inline-flex h-5 w-5 items-center justify-center rounded-full text-gray-400 outline-none transition hover:text-blue-600 focus:text-blue-600" aria-label="Panduan form: {{ $title }}">
    <i class="fas fa-circle-question text-sm"></i>
    <span class="pointer-events-none invisible absolute left-0 top-full z-50 mt-2 w-80 rounded-lg border border-gray-200 bg-white p-4 text-left opacity-0 shadow-lg transition group-hover:visible group-hover:opacity-100 group-focus:visible group-focus:opacity-100">
        <span class="block text-sm font-semibold text-gray-900">{{ $title }}</span>
        <span class="mt-1 block text-xs leading-5 text-gray-600">{{ $description }}</span>
        @if($steps->isNotEmpty())
            <span class="mt-3 block text-xs font-semibold uppercase tracking-wide text-gray-500">Cara pakai</span>
            <span class="mt-1 block space-y-1">
                @foreach($steps as $step)
                    <span class="block text-xs leading-5 text-gray-600">{{ $loop->iteration }}. {{ $step }}</span>
                @endforeach
            </span>
        @endif
        @if($notes->isNotEmpty())
            <span class="mt-3 block text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan</span>
            <span class="mt-1 block space-y-1">
                @foreach($notes as $note)
                    <span class="block text-xs leading-5 text-gray-600">- {{ $note }}</span>
                @endforeach
            </span>
        @endif
    </span>
</span>
