@extends('layouts.app')
@section('title', 'Admin - Semua Folder Rack')

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-pink-700">📦 Semua Folder Rack (Admin)</h1>
        <p class="text-sm text-gray-500">Monitoring seluruh folder induk dan foto rack dari semua member</p>
    </div>

    <!-- Search Box -->
    <form action="{{ route('admin.rack.index') }}" method="GET" class="flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama folder / member / NIK..." class="rounded-xl border border-pink-300 px-4 py-2 text-sm focus:border-pink-500 focus:outline-none focus:ring-1 focus:ring-pink-500 w-64">
        <button type="submit" class="rounded-xl bg-pink-600 px-4 py-2 text-sm font-bold text-white hover:bg-pink-700 transition">
            Cari
        </button>
        @if(request('search'))
            <a href="{{ route('admin.rack.index') }}" class="rounded-xl bg-gray-100 px-3 py-2 text-sm text-gray-600 hover:bg-gray-200 transition flex items-center">
                Reset
            </a>
        @endif
    </form>
</div>

<div class="overflow-hidden rounded-2xl border border-pink-200 bg-white shadow-sm">
    <div class="border-b border-pink-100 bg-pink-50/50 px-6 py-4 flex items-center justify-between">
        <h2 class="font-bold text-pink-800">Daftar Seluruh Folder Induk</h2>
        <span class="text-xs text-gray-500">Total: {{ $folders->total() }} Folder</span>
    </div>

    @if($folders->isEmpty())
        <div class="p-12 text-center text-gray-400">
            <span class="text-5xl">📁</span>
            <p class="mt-3 text-base font-semibold text-gray-700">Belum ada folder induk yang ditemukan</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-pink-50/50 text-xs font-bold uppercase text-pink-900 border-b border-pink-100">
                    <tr>
                        <th class="px-6 py-3.5">Nama Folder Induk</th>
                        <th class="px-6 py-3.5">Member (NIK)</th>
                        <th class="px-6 py-3.5">Waktu Dibuat</th>
                        <th class="px-6 py-3.5 text-center">Sub-Folder QR</th>
                        <th class="px-6 py-3.5 text-center">Total Foto</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($folders as $folder)
                        @php
                            $totalPhotos = $folder->qrFolders->sum(fn($q) => $q->photos->count());
                        @endphp
                        <tr class="hover:bg-pink-50/20 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">📁</span>
                                    <span class="font-bold text-gray-800">{{ $folder->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-800">{{ $folder->member_name }}</div>
                                <div class="text-xs text-gray-400 font-mono">NIK: {{ $folder->nik }}</div>
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-500">
                                {{ $folder->created_at->format('d M Y - H:i') }}
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-pink-600">
                                {{ $folder->qrFolders->count() }}
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-blue-600">
                                {{ $totalPhotos }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.rack.show', $folder->id) }}" class="rounded-lg bg-pink-100 text-pink-700 hover:bg-pink-200 px-3 py-1.5 text-xs font-bold transition">
                                        Lihat Isi &rarr;
                                    </a>
                                    <form action="{{ route('admin.rack.destroy', $folder->id) }}" method="POST" onsubmit="return confirm('Hapus folder ini beserta semua file foto di dalamnya?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background-color: #dc2626 !important; color: #ffffff !important; border: 1px solid #b91c1c !important;" class="rounded-lg px-3 py-1.5 text-xs font-bold shadow hover:opacity-90 transition" title="Hapus Seluruh Folder">
                                            🗑️ Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-100">
            {{ $folders->links() }}
        </div>
    @endif
</div>
@endsection
