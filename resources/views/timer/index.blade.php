@extends('layouts.app')

@section('title', 'Menu Timer QR - ISEKI Recorder')

@php
    $prefix = session('role') === 'admin' ? 'admin' : 'member';
@endphp

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-pink-800">⏱️ Menu Timer QR</h1>
        <p class="text-sm text-pink-500">
            Scan QR pertama untuk memulai timer, scan QR yang sama lagi untuk menghentikan & menghitung selisih waktu.
        </p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route("$prefix.timer.export", request()->query()) }}" 
           class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-emerald-700">
            <span>⬇️ Unduh Excel</span>
        </a>
    </div>
</div>

{{-- Scan Action Area --}}
<div class="mb-6 rounded-2xl border border-pink-200 bg-white p-5 shadow-sm">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="flex-1">
            <label for="manualQrInput" class="block text-xs font-bold uppercase tracking-wider text-pink-600 mb-1.5">
                Input / Barcode Scanner USB (Auto Submit pada Enter)
            </label>
            <form id="scanForm" class="flex gap-2" data-action="{{ route("$prefix.timer.scan") }}">
                @csrf
                <div class="relative flex-1">
                    <input type="text" id="manualQrInput" name="qr_code" autofocus
                           placeholder="Ketik isi QR / scan dengan alat barcode scanner..."
                           class="w-full rounded-xl border border-pink-200 bg-pink-50/50 px-4 py-2.5 text-sm font-medium text-gray-800 focus:border-pink-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-pink-300">
                </div>
                <button type="submit" id="submitScanBtn"
                        class="inline-flex items-center gap-2 rounded-xl bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-pink-700">
                    <span>Submit</span>
                </button>
            </form>
        </div>

        <div class="flex items-center justify-center md:border-l md:border-pink-100 md:pl-6">
            <button type="button" id="openTimerQrModal" data-action="{{ route("$prefix.timer.scan") }}"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-purple-600 px-6 py-3 text-base font-bold text-white shadow-md hover:bg-purple-700 md:w-auto">
                <span class="text-xl">📷</span>
                <span>Buka Scanner Kamera</span>
            </button>
        </div>
    </div>

    {{-- Scan Toast / Alert Box --}}
    <div id="scanAlert" class="mt-4 hidden rounded-xl p-4 text-sm font-semibold transition-all"></div>
</div>

{{-- Overview Stats (Tetap 1 baris di HP, ukuran lebih ringkas) --}}
<div class="mb-6 grid grid-cols-3 gap-2 sm:gap-4">
    <div class="rounded-xl sm:rounded-2xl border border-amber-100 bg-amber-50/70 p-2.5 sm:p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <span class="text-[10px] sm:text-xs font-bold uppercase tracking-tight text-amber-700 truncate">Running</span>
            <span class="text-sm sm:text-xl">⏳</span>
        </div>
        <div id="statRunning" class="mt-1 sm:mt-2 text-base sm:text-2xl font-black text-amber-700 leading-none">{{ $stats['running'] }}</div>
    </div>
    <div class="rounded-xl sm:rounded-2xl border border-emerald-100 bg-emerald-50/70 p-2.5 sm:p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <span class="text-[10px] sm:text-xs font-bold uppercase tracking-tight text-emerald-700 truncate">Selesai</span>
            <span class="text-sm sm:text-xl">✅</span>
        </div>
        <div class="mt-1 sm:mt-2 text-base sm:text-2xl font-black text-emerald-700 leading-none">{{ $stats['completed'] }}</div>
    </div>
    <div class="rounded-xl sm:rounded-2xl border border-pink-100 bg-pink-50/70 p-2.5 sm:p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <span class="text-[10px] sm:text-xs font-bold uppercase tracking-tight text-pink-700 truncate">Total</span>
            <span class="text-sm sm:text-xl">📊</span>
        </div>
        <div class="mt-1 sm:mt-2 text-base sm:text-2xl font-black text-pink-700 leading-none">{{ $stats['total'] }}</div>
    </div>
</div>

{{-- Section 1: Active Running Timers --}}
<div class="mb-6 rounded-2xl border border-pink-100 bg-white p-5 shadow-sm">
    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-bold text-pink-800 flex items-center gap-2">
            <span class="inline-block h-3 w-3 animate-ping rounded-full bg-amber-400"></span>
            <span>Timer Yang Sedang Berjalan ({{ $runningTimers->count() }})</span>
        </h2>
        <span class="text-xs font-medium text-pink-400">Scan QR yang sama untuk menyelesaikan</span>
    </div>

    @if ($runningTimers->isEmpty())
        <div id="noRunningMsg" class="py-8 text-center text-sm text-pink-400 bg-pink-50/30 rounded-xl border border-dashed border-pink-200">
            Tidak ada timer yang sedang berjalan. Mulai scan QR di atas!
        </div>
        <div id="runningTimersList" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3"></div>
    @else
        <div id="noRunningMsg" class="hidden py-8 text-center text-sm text-pink-400 bg-pink-50/30 rounded-xl border border-dashed border-pink-200">
            Tidak ada timer yang sedang berjalan. Mulai scan QR di atas!
        </div>
        <div id="runningTimersList" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($runningTimers as $timer)
                <div class="running-timer-card relative overflow-hidden rounded-xl border border-amber-200 bg-gradient-to-br from-amber-50/80 to-amber-100/50 p-4 shadow-sm"
                     data-id="{{ $timer->id }}"
                     data-start="{{ $timer->start_time->toIso8601String() }}">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-2.5 w-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-amber-800">Running</span>
                        </div>
                        <span class="text-xs text-amber-700 font-mono">{{ $timer->start_time->format('H:i:s') }}</span>
                    </div>

                    <div class="mt-2.5">
                        <div class="font-mono text-2xl font-black text-amber-900 live-stopwatch">00:00:00</div>
                        <div class="mt-1 break-all text-xs font-semibold text-gray-700 line-clamp-2">
                            {{ $timer->qr_code }}
                        </div>
                        <div class="mt-1 text-[11px] text-amber-700">
                            Member: {{ $timer->member_name }} (NIK: {{ $timer->nik }})
                        </div>
                    </div>

                    <div class="mt-3 flex items-center justify-between border-t border-amber-200/60 pt-2.5">
                        <span class="text-[11px] text-amber-600">Mulai: {{ $timer->start_time->format('d M Y') }}</span>
                        @if (session('role') === 'admin')
                            <form method="POST" action="{{ route("admin.timer.destroy", $timer) }}"
                                  onsubmit="return confirm('Hapus/batalkan timer berjalan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-600 hover:bg-red-200">
                                    Batalkan
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Section 2: Completed Timers History --}}
<div class="rounded-2xl border border-pink-100 bg-white p-5 shadow-sm">
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold text-pink-800">📋 Riwayat Timer (Selesai)</h2>
            <p class="text-xs text-pink-500">
                @if (session('role') === 'admin')
                    Menampilkan seluruh data scan timer dari semua member.
                @else
                    Menampilkan data scan timer untuk <strong>{{ session('name') }} (NIK {{ session('nik') }})</strong>.
                @endif
            </p>
        </div>

        {{-- Filter Form --}}
        <form method="GET" action="{{ route("$prefix.timer.index") }}" class="flex flex-wrap items-center gap-2">
            <input type="date" name="date" value="{{ request('date') }}"
                   class="rounded-xl border border-pink-200 bg-pink-50/50 px-3 py-1.5 text-xs font-medium text-gray-700 focus:border-pink-500 focus:outline-none">

            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari QR / Member..."
                   class="rounded-xl border border-pink-200 bg-pink-50/50 px-3 py-1.5 text-xs font-medium text-gray-700 focus:border-pink-500 focus:outline-none">

            <button type="submit" class="rounded-xl bg-pink-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-pink-700">
                Filter
            </button>
            @if(request()->hasAny(['date', 'search', 'filter_nik']))
                <a href="{{ route("$prefix.timer.index") }}" class="rounded-xl bg-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-300">
                    Reset
                </a>
            @endif
        </form>
    </div>

    @if ($completedTimers->isEmpty())
        <div class="py-10 text-center text-sm text-pink-400 bg-pink-50/30 rounded-xl border border-dashed border-pink-200">
            Belum ada riwayat timer selesai.
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full min-w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-pink-100 bg-pink-50/60 text-xs uppercase text-pink-700">
                        <th class="px-2.5 sm:px-3.5 py-3 font-semibold w-10">No</th>
                        <th class="px-2.5 sm:px-3.5 py-3 font-semibold min-w-[120px] max-w-[160px] sm:max-w-xs">Data QR</th>
                        <th class="px-2.5 sm:px-3.5 py-3 font-semibold">Scan Mulai</th>
                        <th class="px-2.5 sm:px-3.5 py-3 font-semibold">Scan Selesai</th>
                        <th class="px-2.5 sm:px-3.5 py-3 font-semibold text-center">Selisih Waktu</th>
                        <th class="px-2.5 sm:px-3.5 py-3 font-semibold">Member</th>
                        @if (session('role') === 'admin')
                            <th class="px-2.5 sm:px-3.5 py-3 text-right font-semibold">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-pink-50">
                    @foreach ($completedTimers as $index => $item)
                        <tr class="hover:bg-pink-50/40 transition">
                            <td class="px-2.5 sm:px-3.5 py-3 text-xs text-pink-400 font-medium">
                                {{ $completedTimers->firstItem() + $index }}
                            </td>
                            <td class="min-w-[120px] max-w-[160px] sm:max-w-xs break-all px-2.5 sm:px-3.5 py-3 text-[11px] sm:text-xs font-mono font-medium text-gray-800">
                                {{ $item->qr_code }}
                            </td>
                            <td class="whitespace-nowrap px-2.5 sm:px-3.5 py-3 text-xs text-gray-600">
                                <div>{{ $item->start_time ? $item->start_time->format('d M Y') : '-' }}</div>
                                <div class="font-mono text-pink-700 font-bold">{{ $item->start_time ? $item->start_time->format('H:i:s') : '-' }}</div>
                            </td>
                            <td class="whitespace-nowrap px-2.5 sm:px-3.5 py-3 text-xs text-gray-600">
                                <div>{{ $item->end_time ? $item->end_time->format('d M Y') : '-' }}</div>
                                <div class="font-mono text-pink-700 font-bold">{{ $item->end_time ? $item->end_time->format('H:i:s') : '-' }}</div>
                            </td>
                            <td class="whitespace-nowrap px-2.5 sm:px-3.5 py-3 text-center">
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 sm:px-3 py-1 text-[11px] sm:text-xs font-bold text-emerald-800 border border-emerald-200 shadow-sm font-mono">
                                    <span>⏱️</span>
                                    <span>{{ $item->formatted_duration }}</span>
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-2.5 sm:px-3.5 py-3 text-xs text-gray-700">
                                <div class="font-semibold">{{ $item->member_name ?? '-' }}</div>
                                <div class="text-[11px] text-pink-500 font-mono">NIK: {{ $item->nik }}</div>
                            </td>
                            @if (session('role') === 'admin')
                                <td class="px-2.5 sm:px-3.5 py-3 text-right whitespace-nowrap">
                                    <form method="POST" action="{{ route("admin.timer.destroy", $item) }}"
                                          onsubmit="return confirm('Hapus riwayat timer ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-600 hover:bg-red-100 hover:text-red-700">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>


        <div class="mt-4">
            {{ $completedTimers->links() }}
        </div>
    @endif
</div>

{{-- Modal Scanner Kamera --}}
<div id="timerQrModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4">
    <div class="w-full max-w-md rounded-2xl bg-white p-5 shadow-2xl">
        <div class="mb-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xl">📷</span>
                <h3 class="text-lg font-bold text-pink-800">Scan QR Timer</h3>
            </div>
            <button type="button" class="closeTimerQr text-2xl leading-none text-pink-400 hover:text-pink-600">&times;</button>
        </div>

        <div class="overflow-hidden rounded-xl bg-black">
            <div id="timerQrReader" class="w-full"></div>
        </div>

        <p id="timerQrStatus" class="mt-3 text-center text-xs text-pink-600 font-medium">
            Arahkan kamera ke kode QR...
        </p>

        <div class="mt-4 flex justify-end">
            <button type="button" class="closeTimerQr rounded-xl bg-gray-100 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-200">
                Tutup
            </button>
        </div>
    </div>
</div>
@endsection
