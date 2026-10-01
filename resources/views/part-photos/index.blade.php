@extends('layouts.app')
@section('title', 'Daftar Photo Part')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-pink-700">📷 Photo Part</h1>
    <div class="flex gap-2">
        @if(session('role') === 'admin')
        <a href="{{ route('admin.part-photos.export') }}" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 shadow-md transition-colors">
            📥 Download Excel
        </a>
        @endif
        <a href="{{ route(session('role') . '.part-photos.create') }}" class="rounded-lg bg-pink-600 px-4 py-2 text-sm font-semibold text-white hover:bg-pink-700 shadow-md transition-colors">
            + Tambah Data
        </a>
    </div>
</div>

<div class="mb-4">
    <form action="{{ route(session('role') . '.part-photos.index') }}" method="GET" class="flex items-center max-w-sm">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari berdasarkan nama..." class="w-full rounded-l-lg border border-gray-300 px-3 py-2 text-sm focus:border-pink-500 focus:outline-none focus:ring-1 focus:ring-pink-500">
        @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
        @if(request('direction')) <input type="hidden" name="direction" value="{{ request('direction') }}"> @endif
        <button type="submit" class="rounded-r-lg bg-pink-600 px-4 py-2 text-sm font-semibold text-white hover:bg-pink-700">Cari</button>
    </form>
</div>

<div class="overflow-hidden rounded-xl border border-pink-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-pink-50 text-pink-800">
                <tr>
                    <th class="px-4 py-3 font-semibold">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => ($sort == 'created_at' && $direction == 'asc') ? 'desc' : 'asc']) }}" class="hover:text-pink-600">
                            Waktu @if($sort == 'created_at') {{ $direction == 'asc' ? '↑' : '↓' }} @endif
                        </a>
                    </th>
                    <th class="px-4 py-3 font-semibold">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => ($sort == 'name' && $direction == 'asc') ? 'desc' : 'asc']) }}" class="hover:text-pink-600">
                            Name (QR) @if($sort == 'name') {{ $direction == 'asc' ? '↑' : '↓' }} @endif
                        </a>
                    </th>
                    <th class="px-4 py-3 font-semibold">Photo</th>
                    <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-pink-100">
                @forelse($partPhotos as $photo)
                    <tr class="hover:bg-pink-50/50">
                        <td class="px-4 py-3">{{ $photo->created_at->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $photo->name }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ asset($photo->photo_path) }}" target="_blank" class="text-pink-600 hover:underline">
                                Lihat Foto
                            </a>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <form action="{{ route(session('role') . '.part-photos.destroy', $photo) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 font-semibold text-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                            Belum ada data photo part.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $partPhotos->links() }}
</div>
@endsection
