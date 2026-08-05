@extends('layouts.app')

@section('title', 'Semua Folder - ISEKI Recorder')

@section('content')
<div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-pink-800">Semua Folder Member</h1>
        <p class="text-sm text-pink-500">Total {{ $folders->total() }} folder</p>
    </div>

    <form method="GET" action="{{ route('admin.folders.index') }}" class="flex w-full gap-2 sm:w-auto">
        <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama / NIK / tanggal..."
            class="w-full rounded-xl border border-pink-200 px-4 py-2.5 text-sm focus:border-pink-500 focus:outline-none focus:ring-2 focus:ring-pink-200 sm:w-64">
        <button type="submit" class="rounded-xl bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-pink-700">Cari</button>
    </form>
</div>

@if ($folders->isEmpty())
    <div class="rounded-2xl border border-dashed border-pink-200 bg-white p-10 text-center text-sm text-pink-400">
        Tidak ada folder ditemukan.
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
                <div class="text-xs text-pink-500">NIK {{ $folder->nik }}</div>
                <div class="mt-1 flex gap-3 text-xs text-pink-500">
                    <span>🖼️ {{ $folder->photos_count }}</span>
                    <span>🔤 {{ $folder->strings_count }}</span>
                </div>
                <div class="mt-3 flex gap-2">
                    <a href="{{ route('admin.folders.show', $folder) }}" class="flex-1 rounded-lg bg-pink-600 py-2 text-center text-xs font-semibold text-white hover:bg-pink-700">Buka</a>
                    <form method="POST" action="{{ route('admin.folders.destroy', $folder) }}"
                          onsubmit="return confirm('Hapus folder {{ $folder->nama }} beserta isinya?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-lg bg-red-100 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-200">Hapus</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $folders->links() }}
    </div>
@endif
@endsection
