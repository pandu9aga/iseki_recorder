@extends('layouts.app')
@section('title', 'Menu Rack - Member')

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-pink-700">📦 Menu Rack</h1>
        <p class="text-sm text-gray-500">Kelola folder induk perekaman foto rack Anda</p>
    </div>
    <form action="{{ route('member.rack.folders.store') }}" method="POST">
        @csrf
        <button type="submit" class="flex items-center gap-2 rounded-xl bg-pink-600 px-5 py-2.5 font-bold text-white shadow hover:bg-pink-700 focus:outline-none focus:ring-4 focus:ring-pink-500/50 transition">
            <span>➕</span> Buat Folder Baru
        </button>
    </form>
</div>

<div class="overflow-hidden rounded-2xl border border-pink-200 bg-white shadow-sm">
    <div class="border-b border-pink-100 bg-pink-50/50 px-6 py-4">
        <h2 class="font-bold text-pink-800">Daftar Folder Induk Saya</h2>
    </div>

    @if($folders->isEmpty())
        <div class="p-12 text-center">
            <span class="text-5xl">📁</span>
            <p class="mt-3 text-base font-semibold text-gray-700">Belum ada folder induk yang dibuat</p>
            <p class="text-sm text-gray-400">Klik tombol "Buat Folder Baru" di atas untuk memulai sesi scan QR dan pemotretan rack.</p>
        </div>
    @else
        <div class="divide-y divide-gray-100">
            @foreach($folders as $folder)
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-4 sm:p-5 hover:bg-pink-50/30 transition">
                    <div class="mb-3 sm:mb-0">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">📁</span>
                            <span class="font-bold text-gray-800 text-base">{{ $folder->name }}</span>
                        </div>
                        <div class="mt-1 flex flex-wrap gap-4 text-xs text-gray-500">
                            <span>📅 {{ $folder->created_at->format('d M Y - H:i') }}</span>
                            <span class="font-semibold text-pink-600">🏷️ {{ $folder->qr_folders_count }} Sub-folder QR</span>
                            <span class="font-semibold text-blue-600">🖼️ {{ $folder->photos_count }} Total Foto</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('member.rack.scan', $folder->id) }}" class="rounded-lg bg-pink-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-pink-700 transition">
                            📷 Buka / Scan QR
                        </a>
                        <form action="{{ route('member.rack.destroy', $folder->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus folder ini beserta seluruh foto di dalamnya?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-lg bg-red-50 p-2 text-red-600 hover:bg-red-100 transition" title="Hapus Folder">
                                🗑️
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="p-4 border-t border-gray-100">
            {{ $folders->links() }}
        </div>
    @endif
</div>
@endsection
