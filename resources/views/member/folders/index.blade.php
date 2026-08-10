@extends('layouts.app')

@section('title', 'Folder Saya - ISEKI Recorder')

@section('content')
<div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-pink-800">Folder Saya</h1>
        <p class="text-sm text-pink-500">{{ session('name') }} · NIK {{ session('nik') }}</p>
    </div>
    <button type="button" id="openCreateFolder" class="inline-flex items-center gap-2 rounded-xl bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-pink-700">
        <span class="text-lg leading-none">+</span> Buat Folder Baru
    </button>
</div>

@if ($folders->isEmpty())
    <div class="rounded-2xl border border-dashed border-pink-200 bg-white p-10 text-center">
        <div class="text-4xl">📁</div>
        <p class="mt-2 text-sm text-pink-500">Anda belum memiliki folder. Buat folder baru untuk mulai merekam foto & QR.</p>
    </div>
@else
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach ($folders as $folder)
            <div class="rounded-2xl border border-pink-100 bg-white p-4 shadow-sm transition hover:shadow-md">
                <div class="flex items-start justify-between">
                    <span class="text-2xl">📁</span>
                    <span class="rounded-full bg-pink-100 px-2.5 py-0.5 text-xs font-semibold text-pink-700">{{ $folder->tanggal->format('d M Y') }}</span>
                </div>
                <div class="mt-2 truncate font-semibold text-pink-800">{{ $folder->nama }}</div>
                <div class="mt-1 flex gap-3 text-xs text-pink-500">
                    <span>🖼️ {{ $folder->photos_count }}</span>
                    <span>🔤 {{ $folder->strings_count }}</span>
                </div>
                <div class="mt-3 flex gap-2">
                    <a href="{{ route('member.folders.show', $folder) }}" class="flex-1 rounded-lg bg-pink-600 py-2 text-center text-xs font-semibold text-white hover:bg-pink-700">Buka</a>
                    <form method="POST" action="{{ route('member.folders.destroy', $folder) }}"
                          onsubmit="return confirm('Hapus folder {{ $folder->nama }} beserta isinya?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-lg bg-red-100 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-200">Hapus</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endif

<div id="createFolderModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-bold text-pink-800">Buat Folder Baru</h2>
            <button type="button" id="closeCreateFolder" class="text-2xl leading-none text-pink-400 hover:text-pink-600">&times;</button>
        </div>

        <form method="POST" action="{{ route('member.folders.store') }}">
            @csrf

            <div class="mb-4">
                <label for="nama" class="mb-1 block text-sm font-medium text-pink-700">Nama</label>
                <input type="text" id="nama" name="nama" value="{{ old('nama', session('name')) }}" required
                    class="w-full rounded-xl border border-pink-200 px-4 py-2.5 text-sm focus:border-pink-500 focus:outline-none focus:ring-2 focus:ring-pink-200">
                @error('nama') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label for="nik" class="mb-1 block text-sm font-medium text-pink-700">NIK</label>
                <input type="text" id="nik" value="{{ session('nik') }}" readonly disabled
                    class="w-full cursor-not-allowed rounded-xl border border-pink-100 bg-pink-50 px-4 py-2.5 text-sm text-pink-500">
            </div>

            <div class="mb-6">
                <label for="tanggal" class="mb-1 block text-sm font-medium text-pink-700">Tanggal</label>
                <input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required
                    class="w-full rounded-xl border border-pink-200 px-4 py-2.5 text-sm focus:border-pink-500 focus:outline-none focus:ring-2 focus:ring-pink-200">
                @error('tanggal') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3">
                <button type="submit" class="flex-1 rounded-xl bg-pink-600 py-2.5 text-sm font-semibold text-white shadow hover:bg-pink-700">Buat Folder</button>
            </div>
        </form>
    </div>
</div>
@endsection
