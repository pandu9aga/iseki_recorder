@extends('layouts.app')
@section('title', 'Admin - Foto Sub-Folder ' . $qrFolder->qr_code)

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <div class="flex items-center gap-2 text-xs sm:text-sm">
            <a href="{{ route('admin.rack.index') }}" class="text-pink-600 hover:text-pink-800 font-semibold">Semua Folder</a>
            <span class="text-gray-300">/</span>
            <a href="{{ route('admin.rack.show', $parentFolder->id) }}" class="text-pink-600 hover:text-pink-800 font-semibold truncate max-w-[150px]">{{ $parentFolder->name }}</a>
            <span class="text-gray-300">/</span>
            <span class="text-gray-600 font-medium">{{ $qrFolder->qr_code }}</span>
        </div>
        <h1 class="text-2xl font-bold text-pink-700 mt-1 flex items-center gap-2">
            <span>🏷️</span> Sub-Folder: <span class="bg-pink-100 text-pink-800 px-3 py-0.5 rounded-lg font-mono text-xl">{{ $qrFolder->qr_code }}</span>
        </h1>
        <p class="text-xs text-gray-500">
            Folder Induk: <strong class="text-gray-700">{{ $parentFolder->name }}</strong> · Member: <strong class="text-gray-700">{{ $parentFolder->member_name }}</strong> (NIK: {{ $parentFolder->nik }})
        </p>
    </div>

    <div>
        <a href="{{ route('admin.rack.show', $parentFolder->id) }}" class="inline-flex items-center gap-1 rounded-xl border border-pink-300 bg-white px-4 py-2 text-xs sm:text-sm font-bold text-pink-700 hover:bg-pink-50 shadow-sm transition">
            &larr; Kembali ke Daftar Sub-Folder
        </a>
    </div>
</div>

<div class="overflow-hidden rounded-2xl border border-pink-200 bg-white shadow-sm p-6">
    <div class="border-b border-pink-100 pb-3 mb-4 flex items-center justify-between">
        <h2 class="font-bold text-base text-pink-800 flex items-center gap-2">
            <span>🖼️</span> Galeri Foto Tersimpan (<span id="photoCount">{{ $qrFolder->photos->count() }}</span>)
        </h2>
        <span class="text-xs text-gray-400">Direktori: uploads/rack/{{ $parentFolder->name }}/...</span>
    </div>

    @if($qrFolder->photos->isEmpty())
        <div class="py-12 text-center text-gray-400">
            <span class="text-4xl">🖼️</span>
            <p class="mt-2 text-sm font-semibold">Tidak ada foto di sub-folder QR ini.</p>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($qrFolder->photos as $photo)
                <div class="group relative rounded-xl border border-gray-200 overflow-hidden bg-gray-50 shadow-sm">
                    <a href="{{ $photo->url() }}" target="_blank" class="block aspect-square overflow-hidden bg-black/5">
                        <img src="{{ $photo->url() }}" alt="Foto Rack" class="h-full w-full object-cover group-hover:scale-105 transition duration-200">
                    </a>
                    <div class="p-2 flex items-center justify-between bg-white text-[11px] text-gray-500">
                        <span class="truncate">{{ $photo->created_at->format('H:i:s') }}</span>
                        <form action="{{ route('admin.rack.photos.destroy', [$parentFolder->id, $qrFolder->id, $photo->id]) }}" method="POST" onsubmit="return confirm('Hapus foto ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 font-bold p-1 rounded hover:bg-red-50" title="Hapus foto">
                                🗑️
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
