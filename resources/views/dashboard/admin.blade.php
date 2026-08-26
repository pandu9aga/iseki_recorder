@extends('layouts.app')

@section('title', 'Dashboard Admin - ISEKI Recorder')

@section('content')
<div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-pink-800">Dashboard Admin</h1>
        <p class="text-sm text-pink-500">Selamat datang, {{ session('name') }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.folders.index') }}" class="rounded-xl bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-pink-700">Lihat Semua Folder</a>
    </div>
</div>

<div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
    <div class="rounded-2xl border border-pink-100 bg-white p-5 shadow-sm">
        <div class="text-3xl">📁</div>
        <div class="mt-2 text-3xl font-bold text-pink-700">{{ $stats['folders'] }}</div>
        <div class="text-sm text-pink-500">Total Folder</div>
    </div>
    <div class="rounded-2xl border border-pink-100 bg-white p-5 shadow-sm">
        <div class="text-3xl">🖼️</div>
        <div class="mt-2 text-3xl font-bold text-pink-700">{{ $stats['photos'] }}</div>
        <div class="text-sm text-pink-500">Total Foto</div>
    </div>
    <div class="rounded-2xl border border-pink-100 bg-white p-5 shadow-sm">
        <div class="text-3xl">🔤</div>
        <div class="mt-2 text-3xl font-bold text-pink-700">{{ $stats['strings'] }}</div>
        <div class="text-sm text-pink-500">Total QR</div>
    </div>
    <div class="rounded-2xl border border-pink-100 bg-white p-5 shadow-sm">
        <div class="text-3xl">⏱️</div>
        <div class="mt-2 text-3xl font-bold text-pink-700">{{ $stats['timers'] ?? 0 }}</div>
        <div class="text-sm text-pink-500">Total Timer QR</div>
    </div>
    <div class="rounded-2xl border border-pink-100 bg-white p-5 shadow-sm">
        <div class="text-3xl">👥</div>
        <div class="mt-2 text-3xl font-bold text-pink-700">{{ $stats['admins'] }}</div>
        <div class="text-sm text-pink-500">User Admin</div>
    </div>
</div>


<div class="mt-6 rounded-2xl border border-pink-100 bg-white p-5 shadow-sm">
    <h2 class="mb-4 text-lg font-semibold text-pink-800">Folder Terbaru</h2>

    @if ($recentFolders->isEmpty())
        <p class="py-6 text-center text-sm text-pink-400">Belum ada folder.</p>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($recentFolders as $folder)
                <a href="{{ route('admin.folders.show', $folder) }}" class="group rounded-xl border border-pink-100 bg-pink-50/50 p-4 transition hover:border-pink-300 hover:bg-pink-50">
                    <div class="flex items-start justify-between">
                        <span class="text-2xl">📁</span>
                        <span class="text-xs font-medium text-pink-400">{{ $folder->tanggal->format('d M Y') }}</span>
                    </div>
                    <div class="mt-2 truncate font-semibold text-pink-800">{{ $folder->nama }}</div>
                    <div class="text-xs text-pink-500">NIK {{ $folder->nik }}</div>
                    <div class="mt-2 flex gap-3 text-xs text-pink-500">
                        <span>{{ $folder->photos_count }} foto</span>
                        <span>{{ $folder->strings_count }} QR</span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
