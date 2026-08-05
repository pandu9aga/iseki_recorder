@extends('layouts.app')

@section('title', 'Login - ISEKI Recorder')

@section('content')
<div class="flex min-h-[80vh] items-center justify-center py-6">
    <div class="w-full max-w-md">
        <div class="mb-6 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-pink-600 text-3xl shadow-lg shadow-pink-200">📷</div>
            <h1 class="mt-4 text-2xl font-bold text-pink-800">ISEKI Recorder</h1>
            <p class="mt-1 text-sm text-pink-400">Masuk untuk mulai merekam</p>
        </div>

        <div class="rounded-2xl border border-pink-100 bg-white p-6 shadow-lg shadow-pink-100/50">
            @if ($errors->has('login'))
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                    {{ $errors->first('login') }}
                </div>
            @endif

            <div class="mb-5 grid grid-cols-2 gap-2 rounded-xl bg-pink-50 p-1">
                <button type="button" id="tabAdmin" class="tab-btn rounded-lg py-2 text-sm font-semibold bg-pink-600 text-white">Admin</button>
                <button type="button" id="tabMember" class="tab-btn rounded-lg py-2 text-sm font-semibold text-pink-600">Member</button>
            </div>

            <form method="POST" action="{{ route('login.post') }}">
                @csrf
                <input type="hidden" name="role" id="roleInput" value="admin">

                <div class="mb-4">
                    <label for="login" id="loginLabel" class="mb-1 block text-sm font-medium text-pink-700">Username</label>
                    <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus
                        class="w-full rounded-xl border border-pink-200 px-4 py-2.5 text-sm focus:border-pink-500 focus:outline-none focus:ring-2 focus:ring-pink-200">
                </div>

                <div class="mb-6">
                    <label for="password" class="mb-1 block text-sm font-medium text-pink-700">Password</label>
                    <input type="password" id="password" name="password" required
                        class="w-full rounded-xl border border-pink-200 px-4 py-2.5 text-sm focus:border-pink-500 focus:outline-none focus:ring-2 focus:ring-pink-200">
                </div>

                <button type="submit" class="w-full rounded-xl bg-pink-600 py-3 text-sm font-bold text-white shadow-lg shadow-pink-200 transition hover:bg-pink-700">
                    Masuk
                </button>
            </form>
        </div>

        <p class="mt-4 text-center text-xs text-pink-400">
            Admin login dengan username · Member login dengan NIK
        </p>
    </div>
</div>
@endsection
