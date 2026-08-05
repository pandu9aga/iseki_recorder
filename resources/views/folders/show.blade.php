@extends('layouts.app')

@section('title', $folder->nama.' - ISEKI Recorder')

@php
    $prefix = session('role') === 'admin' ? 'admin' : 'member';
@endphp

@section('content')
<div class="mb-6">
    <div class="mb-3">
        <a href="{{ $prefix === 'admin' ? route('admin.folders.index') : route('member.folders.index') }}" class="text-sm font-medium text-pink-500 hover:text-pink-700">
            &larr; Kembali ke daftar folder
        </a>
    </div>

    <div class="flex flex-col gap-4 rounded-2xl border border-pink-100 bg-white p-5 shadow-sm md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-pink-100 text-3xl">📁</div>
            <div>
                <h1 class="text-xl font-bold text-pink-800">{{ $folder->nama }}</h1>
                <p class="text-sm text-pink-500">NIK {{ $folder->nik }} · {{ $folder->tanggal->format('d M Y') }}</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route("$prefix.download.strings", $folder) }}" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-emerald-700">⬇ Unduh Excel String</a>
            <form method="POST" action="{{ route("$prefix.folders.destroy", $folder) }}" onsubmit="return confirm('Hapus folder {{ $folder->nama }} beserta isinya?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-xl bg-red-500 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-red-600">Hapus Folder</button>
            </form>
        </div>
    </div>
</div>

@if ($errors->has('photo'))
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first('photo') }}</div>
@endif

<div class="grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2">
        <div class="rounded-2xl border border-pink-100 bg-white p-5 shadow-sm">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-lg font-semibold text-pink-800">🖼️ Foto ({{ $folder->photos->count() }})</h2>
                <div class="flex flex-wrap gap-2">
                    <label for="photoUpload" class="inline-flex cursor-pointer items-center gap-2 rounded-xl bg-pink-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-pink-700">
                        <span class="text-lg leading-none">📤</span> Upload Foto
                    </label>
                    <button type="button" id="openCamera" data-action="{{ route("$prefix.photos.store", $folder) }}" class="inline-flex items-center gap-2 rounded-xl bg-purple-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-purple-700">
                        <span class="text-lg leading-none">📷</span> Kamera
                    </button>
                </div>
            </div>

            <form method="POST" action="{{ route("$prefix.photos.store", $folder) }}" enctype="multipart/form-data" id="uploadForm">
                @csrf
                <input type="file" id="photoUpload" name="photos[]" accept="image/*" multiple class="hidden" onchange="document.getElementById('uploadForm').submit()">
            </form>

            @if ($folder->photos->isEmpty())
                <p class="py-8 text-center text-sm text-pink-400">Belum ada foto di folder ini.</p>
            @else
                <form method="POST" action="{{ route("$prefix.download.photos", $folder) }}" id="zipForm">
                    @csrf

                    <div class="mb-3 flex items-center justify-between text-sm">
                        <label class="flex items-center gap-2 text-pink-600">
                            <input type="checkbox" id="selectAllPhotos" class="h-4 w-4 rounded border-pink-300 accent-pink-600">
                            <span>Pilih semua</span>
                        </label>
                        <button type="submit" class="rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-amber-600">
                            ⬇ Download ZIP Terpilih
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4">
                        @foreach ($folder->photos as $photo)
                            <div class="group relative overflow-hidden rounded-xl border border-pink-100">
                                <input type="checkbox" name="photo_ids[]" value="{{ $photo->id }}"
                                    class="photo-check absolute left-2 top-2 z-10 h-4 w-4 rounded border-pink-300 accent-pink-600">
                                <a href="{{ $folder->photoUrl($photo) }}" target="_blank">
                                    <img src="{{ $folder->photoUrl($photo) }}" alt="{{ $photo->filename }}"
                                        class="aspect-square w-full object-cover transition group-hover:scale-105" loading="lazy">
                                </a>
                                <div class="absolute inset-x-0 bottom-0 flex items-center justify-between bg-gradient-to-t from-black/60 to-transparent px-2 pb-1.5 pt-5">
                                    <span class="truncate text-[10px] text-white">{{ $photo->filename }}</span>
                                    <form method="POST" action="{{ route("$prefix.photos.destroy", [$folder, $photo]) }}"
                                          onsubmit="return confirm('Hapus foto ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded bg-red-500 px-1.5 py-0.5 text-[10px] font-bold text-white hover:bg-red-600">✕</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </form>
            @endif
        </div>
    </div>

    <div>
        <div class="rounded-2xl border border-pink-100 bg-white p-5 shadow-sm">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-lg font-semibold text-pink-800">🔤 String ({{ $folder->strings->count() }})</h2>
                <button type="button" id="openQr" data-action="{{ route("$prefix.strings.store", $folder) }}" class="inline-flex items-center gap-2 rounded-xl bg-pink-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-pink-700">
                    <span class="text-lg leading-none">📲</span> Scan QR
                </button>
            </div>

            @if ($folder->strings->isEmpty())
                <p class="py-8 text-center text-sm text-pink-400">Belum ada string. Scan QR untuk menambah.</p>
            @else
                <ul class="space-y-2">
                    @foreach ($folder->strings as $string)
                        <li class="flex items-start justify-between gap-3 rounded-xl border border-pink-100 bg-pink-50/60 p-3">
                            <div class="min-w-0">
                                <p class="break-all text-sm text-gray-700">{{ $string->content }}</p>
                                <p class="mt-1 text-xs text-pink-400">{{ $string->created_at ? $string->created_at->format('d M Y H:i:s') : '-' }}</p>
                            </div>
                            <form method="POST" action="{{ route("$prefix.strings.destroy", [$folder, $string]) }}"
                                  onsubmit="return confirm('Hapus string ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded bg-red-100 px-2 py-1 text-xs font-semibold text-red-700 hover:bg-red-200">✕</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>

{{-- Modal Kamera --}}
<div id="cameraModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4">
    <div class="w-full max-w-md rounded-2xl bg-white p-5">
        <div class="mb-3 flex items-center justify-between">
            <h3 class="text-lg font-bold text-pink-800">Jepret Foto</h3>
            <button type="button" class="closeCamera text-2xl leading-none text-pink-400 hover:text-pink-600">&times;</button>
        </div>
        <video id="cameraFeed" autoplay playsinline class="aspect-video w-full rounded-xl bg-black object-cover"></video>
        <div class="mt-4 flex justify-center">
            <button type="button" id="capturePhoto" class="rounded-xl bg-pink-600 px-6 py-2.5 text-sm font-semibold text-white shadow hover:bg-pink-700">Jepret</button>
        </div>
        <p id="cameraStatus" class="mt-3 text-center text-xs text-red-500"></p>
    </div>
</div>

{{-- Modal QR --}}
<div id="qrModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4">
    <div class="w-full max-w-md rounded-2xl bg-white p-5">
        <div class="mb-3 flex items-center justify-between">
            <h3 class="text-lg font-bold text-pink-800">Scan QR</h3>
            <button type="button" class="closeQr text-2xl leading-none text-pink-400 hover:text-pink-600">&times;</button>
        </div>
        <div id="qrReader" class="w-full rounded-xl bg-black"></div>
        <p id="qrStatus" class="mt-3 text-center text-xs text-pink-500">Arahkan kamera ke kode QR.</p>
    </div>
</div>
@endsection
