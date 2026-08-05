@extends('layouts.app')

@section('title', 'Edit User Admin - ISEKI Recorder')

@section('content')
<div class="mx-auto max-w-lg">
    <h1 class="mb-1 text-2xl font-bold text-pink-800">Edit User Admin</h1>
    <p class="mb-6 text-sm text-pink-500">Kosongkan password jika tidak ingin mengubahnya.</p>

    <div class="rounded-2xl border border-pink-100 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.users.update', $admin) }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="username" class="mb-1 block text-sm font-medium text-pink-700">Username</label>
                <input type="text" id="username" name="username" value="{{ old('username', $admin->username) }}" required
                    class="w-full rounded-xl border border-pink-200 px-4 py-2.5 text-sm focus:border-pink-500 focus:outline-none focus:ring-2 focus:ring-pink-200">
                @error('username') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="mb-6">
                <label for="password" class="mb-1 block text-sm font-medium text-pink-700">Password Baru</label>
                <input type="text" id="password" name="password"
                    class="w-full rounded-xl border border-pink-200 px-4 py-2.5 text-sm focus:border-pink-500 focus:outline-none focus:ring-2 focus:ring-pink-200">
                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3">
                <button type="submit" class="rounded-xl bg-pink-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-pink-700">Simpan</button>
                <a href="{{ route('admin.users.index') }}" class="rounded-xl border border-pink-200 px-5 py-2.5 text-sm font-semibold text-pink-600 hover:bg-pink-50">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
