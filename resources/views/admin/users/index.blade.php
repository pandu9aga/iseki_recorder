@extends('layouts.app')

@section('title', 'User Admin - ISEKI Recorder')

@section('content')
<div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-pink-800">User Admin</h1>
        <p class="text-sm text-pink-500">Kelola akun admin aplikasi</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-pink-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-pink-700">
        <span class="text-lg leading-none">+</span> Tambah User Admin
    </a>
</div>

@if ($errors->has('error'))
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $errors->first('error') }}</div>
@endif

<div class="overflow-x-auto rounded-2xl border border-pink-100 bg-white shadow-sm">
    <table class="min-w-full divide-y divide-pink-100 text-sm">
        <thead class="bg-pink-50">
            <tr>
                <th class="px-5 py-3 text-left font-semibold text-pink-700">ID</th>
                <th class="px-5 py-3 text-left font-semibold text-pink-700">Username</th>
                <th class="px-5 py-3 text-left font-semibold text-pink-700">Dibuat</th>
                <th class="px-5 py-3 text-right font-semibold text-pink-700">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-pink-50">
            @foreach ($admins as $admin)
                <tr class="hover:bg-pink-50/50">
                    <td class="px-5 py-3 text-pink-600">{{ $admin->id }}</td>
                    <td class="px-5 py-3 font-medium text-gray-700">{{ $admin->username }}</td>
                    <td class="px-5 py-3 text-gray-500">{{ $admin->created_at ? $admin->created_at->format('d M Y H:i') : '-' }}</td>
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.users.edit', $admin) }}" class="rounded-lg bg-pink-100 px-3 py-1.5 text-xs font-semibold text-pink-700 hover:bg-pink-200">Edit</a>
                            <form method="POST" action="{{ route('admin.users.destroy', $admin) }}"
                                  onsubmit="return confirm('Hapus user admin {{ $admin->username }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg bg-red-100 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-200">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
