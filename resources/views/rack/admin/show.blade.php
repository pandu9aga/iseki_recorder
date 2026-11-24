@extends('layouts.app')
@section('title', 'Admin - Detail Folder Rack ' . $parentFolder->name)

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <div class="flex items-center gap-2 text-xs sm:text-sm">
            <a href="{{ route('admin.rack.index') }}" class="text-pink-600 hover:text-pink-800 font-semibold">&larr; Kembali ke Daftar</a>
            <span class="text-gray-300">/</span>
            <span class="text-gray-600 font-medium">{{ $parentFolder->name }}</span>
        </div>
        <h1 class="text-2xl font-bold text-pink-700 mt-1">📁 {{ $parentFolder->name }}</h1>
        <p class="text-xs text-gray-500">
            Dibuat oleh: <strong class="text-gray-700">{{ $parentFolder->member_name }}</strong> (NIK: {{ $parentFolder->nik }}) · {{ $parentFolder->created_at->format('d M Y, H:i:s') }}
        </p>
    </div>

    <div>
        <form action="{{ route('admin.rack.destroy', $parentFolder->id) }}" method="POST" onsubmit="return confirm('Hapus seluruh folder induk ini beserta semua subfolder dan foto di dalamnya?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="rounded-xl bg-red-600 px-4 py-2 text-xs sm:text-sm font-bold text-white hover:bg-red-700 transition">
                🗑️ Hapus Seluruh Folder
            </button>
        </form>
    </div>
</div>

<div class="space-y-6">
    @forelse($parentFolder->qrFolders as $qrFolder)
        <div class="overflow-hidden rounded-2xl border border-pink-200 bg-white shadow-sm">
            <div class="border-b border-pink-100 bg-pink-50/50 px-5 py-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-lg">🏷️</span>
                    <span class="font-bold text-gray-800 text-sm">QR: {{ $qrFolder->qr_code }}</span>
                    <span class="rounded-full bg-pink-100 text-pink-700 px-2.5 py-0.5 text-[11px] font-semibold">
                        {{ $qrFolder->photos->count() }} Foto
                    </span>
                </div>
                <div class="text-xs text-gray-400">
                    {{ $qrFolder->created_at->format('H:i:s') }}
                </div>
            </div>

            <div class="p-4">
                @if($qrFolder->photos->isEmpty())
                    <p class="text-xs text-gray-400 italic">Tidak ada foto pada sub-folder QR ini.</p>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
                        @foreach($qrFolder->photos as $photo)
                            <div class="group relative rounded-xl border border-gray-200 overflow-hidden bg-gray-50 shadow-sm">
                                <a href="{{ $photo->url() }}" target="_blank" class="block aspect-square overflow-hidden bg-black/5">
                                    <img src="{{ $photo->url() }}" alt="Foto" class="h-full w-full object-cover group-hover:scale-105 transition duration-200">
                                </a>
                                <div class="p-1 flex items-center justify-between bg-white text-[10px] text-gray-500">
                                    <span class="truncate">{{ $photo->created_at->format('H:i') }}</span>
                                    <form action="{{ route('admin.rack.photos.destroy', [$parentFolder->id, $qrFolder->id, $photo->id]) }}" method="POST" onsubmit="return confirm('Hapus foto ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 font-bold p-1" title="Hapus foto">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="overflow-hidden rounded-2xl border border-pink-200 bg-white p-12 text-center text-gray-400">
            <span class="text-4xl">📭</span>
            <p class="mt-2 text-sm font-semibold">Belum ada sub-folder QR yang dibuat di folder ini.</p>
        </div>
    @endforelse
</div>
@endsection
