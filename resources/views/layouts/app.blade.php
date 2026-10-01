<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ISEKI Recorder')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-pink-50 text-gray-800 antialiased">
    <nav class="bg-pink-600 shadow-lg sticky top-0 z-40">
        <div class="px-4">
            <div class="flex h-16 items-center justify-between">
                <a href="{{ route('dashboard.landing') }}" class="flex items-center gap-2">
                    <span class="text-2xl">📷</span>
                    <span class="text-lg font-bold text-white">ISEKI Recorder</span>
                </a>

                <button id="navToggle" type="button" class="text-white focus:outline-none md:hidden" aria-label="Menu">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <div class="hidden md:flex md:items-center md:gap-6">
                    @if (session('role') === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-pink-100 hover:bg-pink-700 hover:text-white">Dashboard</a>
                        <a href="{{ route('admin.folders.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-pink-100 hover:bg-pink-700 hover:text-white">Semua Folder</a>
                        <a href="{{ route('admin.timer.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-pink-100 hover:bg-pink-700 hover:text-white">⏱️ Timer</a>
                        <a href="{{ route('admin.part-photos.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-pink-100 hover:bg-pink-700 hover:text-white">📷 Photo Part</a>
                        <a href="{{ route('admin.users.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-pink-100 hover:bg-pink-700 hover:text-white">User Admin</a>
                    @else
                        <a href="{{ route('member.dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-pink-100 hover:bg-pink-700 hover:text-white">Dashboard</a>
                        <a href="{{ route('member.folders.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-pink-100 hover:bg-pink-700 hover:text-white">Folder Saya</a>
                        <a href="{{ route('member.timer.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-pink-100 hover:bg-pink-700 hover:text-white">⏱️ Timer</a>
                        <a href="{{ route('member.part-photos.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-pink-100 hover:bg-pink-700 hover:text-white">📷 Photo Part</a>
                    @endif

                    <div class="flex items-center gap-3 border-l border-pink-400 pl-4">
                        <div class="text-right leading-tight">
                            <div class="text-sm font-semibold text-white">{{ session('name') }}</div>
                            <div class="text-xs text-pink-200">{{ session('role') === 'admin' ? 'Admin' : 'Member · NIK '.session('nik') }}</div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-lg bg-white/20 px-3 py-1.5 text-sm font-semibold text-white hover:bg-white/30">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div id="navMobile" class="hidden border-t border-pink-500 bg-pink-600 px-4 pb-4 md:hidden">
            <div class="flex flex-col gap-1 pt-3">
                @if (session('role') === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-pink-100 hover:bg-pink-700 hover:text-white">Dashboard</a>
                    <a href="{{ route('admin.folders.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-pink-100 hover:bg-pink-700 hover:text-white">Semua Folder</a>
                    <a href="{{ route('admin.timer.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-pink-100 hover:bg-pink-700 hover:text-white">⏱️ Timer</a>
                    <a href="{{ route('admin.part-photos.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-pink-100 hover:bg-pink-700 hover:text-white">📷 Photo Part</a>
                    <a href="{{ route('admin.users.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-pink-100 hover:bg-pink-700 hover:text-white">User Admin</a>
                @else
                    <a href="{{ route('member.dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-pink-100 hover:bg-pink-700 hover:text-white">Dashboard</a>
                    <a href="{{ route('member.folders.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-pink-100 hover:bg-pink-700 hover:text-white">Folder Saya</a>
                    <a href="{{ route('member.timer.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-pink-100 hover:bg-pink-700 hover:text-white">⏱️ Timer</a>
                    <a href="{{ route('member.part-photos.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-pink-100 hover:bg-pink-700 hover:text-white">📷 Photo Part</a>
                @endif

                <div class="mt-2 flex items-center justify-between rounded-lg bg-pink-700/60 px-3 py-2">
                    <span class="text-sm text-white">{{ session('name') }} · {{ session('role') === 'admin' ? 'Admin' : 'NIK '.session('nik') }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg bg-white/20 px-3 py-1.5 text-sm font-semibold text-white">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="px-4 py-6">
        @if (session('success'))
            <div class="mb-4 flex items-center justify-between rounded-xl border border-pink-200 bg-pink-100 px-4 py-3 text-sm font-medium text-pink-800">
                <span>{{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="ml-3 text-pink-500 hover:text-pink-700">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 flex items-center justify-between rounded-xl border border-red-200 bg-red-100 px-4 py-3 text-sm font-medium text-red-800">
                <span>{{ session('error') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="ml-3 text-red-500 hover:text-red-700">&times;</button>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="mt-10 pb-6 text-center text-xs text-pink-400">
        ISEKI Recorder &copy; {{ date('Y') }}
    </footer>
</body>
</html>