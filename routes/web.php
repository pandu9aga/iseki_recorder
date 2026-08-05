<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\StringController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (session('role') === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    if (session('role') === 'member') {
        return redirect()->route('member.dashboard');
    }

    return redirect()->route('login');
})->name('dashboard.landing');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
    Route::get('/', function () {
        $stats = [
            'folders' => \App\Models\Folder::count(),
            'photos' => \App\Models\FolderPhoto::count(),
            'strings' => \App\Models\FolderString::count(),
            'admins' => \App\Models\Admin::count(),
        ];

        $recentFolders = \App\Models\Folder::withCount('photos')
            ->withCount('strings')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        return view('dashboard.admin', compact('stats', 'recentFolders'));
    })->name('dashboard');

    Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('users/{admin}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('users/{admin}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('users/{admin}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    Route::get('folders', [FolderController::class, 'adminIndex'])->name('folders.index');
    Route::get('folders/{folder}', [FolderController::class, 'show'])->name('folders.show');
    Route::delete('folders/{folder}', [FolderController::class, 'destroy'])->name('folders.destroy');

    Route::post('folders/{folder}/photos', [PhotoController::class, 'store'])->name('photos.store');
    Route::delete('folders/{folder}/photos/{photo}', [PhotoController::class, 'destroy'])->name('photos.destroy');
    Route::post('folders/{folder}/strings', [StringController::class, 'store'])->name('strings.store');
    Route::delete('folders/{folder}/strings/{string}', [StringController::class, 'destroy'])->name('strings.destroy');
    Route::post('folders/{folder}/download/photos', [DownloadController::class, 'photosZip'])->name('download.photos');
    Route::get('folders/{folder}/download/strings', [DownloadController::class, 'stringsExcel'])->name('download.strings');
});

Route::prefix('member')->name('member.')->middleware('member')->group(function () {
    Route::get('/', function () {
        $nik = session('nik');

        $stats = [
            'folders' => \App\Models\Folder::where('nik', $nik)->count(),
            'photos' => \App\Models\FolderPhoto::whereIn('folder_id', \App\Models\Folder::where('nik', $nik)->pluck('id'))->count(),
            'strings' => \App\Models\FolderString::whereIn('folder_id', \App\Models\Folder::where('nik', $nik)->pluck('id'))->count(),
        ];

        $recentFolders = \App\Models\Folder::where('nik', $nik)
            ->withCount('photos')
            ->withCount('strings')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        return view('dashboard.member', compact('stats', 'recentFolders'));
    })->name('dashboard');

    Route::get('folders', [FolderController::class, 'memberIndex'])->name('folders.index');
    Route::post('folders', [FolderController::class, 'store'])->name('folders.store');
    Route::get('folders/{folder}', [FolderController::class, 'show'])->name('folders.show');
    Route::delete('folders/{folder}', [FolderController::class, 'destroy'])->name('folders.destroy');

    Route::post('folders/{folder}/photos', [PhotoController::class, 'store'])->name('photos.store');
    Route::delete('folders/{folder}/photos/{photo}', [PhotoController::class, 'destroy'])->name('photos.destroy');
    Route::post('folders/{folder}/strings', [StringController::class, 'store'])->name('strings.store');
    Route::delete('folders/{folder}/strings/{string}', [StringController::class, 'destroy'])->name('strings.destroy');
    Route::post('folders/{folder}/download/photos', [DownloadController::class, 'photosZip'])->name('download.photos');
    Route::get('folders/{folder}/download/strings', [DownloadController::class, 'stringsExcel'])->name('download.strings');
});
