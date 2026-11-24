<?php

namespace App\Http\Controllers;

use App\Models\RackParentFolder;
use App\Models\RackQrFolder;
use App\Models\RackPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class RackController extends Controller
{
    // ==========================================
    // MEMBER METHODS
    // ==========================================

    public function memberIndex(Request $request)
    {
        $nik = session('nik');
        $folders = RackParentFolder::where('nik', $nik)
            ->withCount(['qrFolders', 'qrFolders as photos_count' => function ($query) {
                $query->join('rack_photos', 'rack_qr_folders.id', '=', 'rack_photos.rack_qr_folder_id');
            }])
            ->orderByDesc('id')
            ->paginate(15);

        return view('rack.member.index', compact('folders'));
    }

    public function storeParentFolder(Request $request)
    {
        $nik = session('nik');
        $name = session('name') ?: 'Member';

        // Format nama folder: [Nama Member] - [timestamp/Y-m-d_H-i-s]
        $folderName = $name . ' - ' . date('Y-m-d_H-i-s');
        // Bersihkan karakter aneh untuk filesystem nama folder
        $safeFolderName = preg_replace('/[\\/\\\\:*?"<>|]/', '_', $folderName);

        $parentFolder = RackParentFolder::create([
            'name' => $safeFolderName,
            'nik' => $nik,
            'member_name' => $name,
        ]);

        $folderPath = $parentFolder->uploadDir();
        if (!File::exists($folderPath)) {
            File::makeDirectory($folderPath, 0755, true);
        }

        return redirect()->route('member.rack.scan', $parentFolder->id)
            ->with('success', 'Folder induk baru berhasil dibuat!');
    }

    public function scanView(RackParentFolder $parentFolder)
    {
        // Pastikan kepemilikan member
        if ($parentFolder->nik !== session('nik')) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $parentFolder->load(['qrFolders' => function ($q) {
            $q->withCount('photos')->orderByDesc('id');
        }]);

        return view('rack.member.scan', compact('parentFolder'));
    }

    public function processQrScan(Request $request, RackParentFolder $parentFolder)
    {
        if ($parentFolder->nik !== session('nik')) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $request->validate([
            'qr_code' => 'required|string|max:500',
        ]);

        $qrCode = trim($request->qr_code);

        // Cek atau buat folder QR di dalam folder induk
        $qrFolder = RackQrFolder::firstOrCreate([
            'rack_parent_folder_id' => $parentFolder->id,
            'qr_code' => $qrCode,
        ]);

        // Buat folder fisik di filesystem
        $subDirPath = $qrFolder->uploadDir();
        if (!File::exists($subDirPath)) {
            File::makeDirectory($subDirPath, 0755, true);
        }

        return redirect()->route('member.rack.camera', [$parentFolder->id, $qrFolder->id]);
    }

    public function cameraView(RackParentFolder $parentFolder, RackQrFolder $qrFolder)
    {
        if ($parentFolder->nik !== session('nik') || $qrFolder->rack_parent_folder_id !== $parentFolder->id) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $qrFolder->load(['photos' => function ($q) {
            $q->orderByDesc('id');
        }]);

        return view('rack.member.camera', compact('parentFolder', 'qrFolder'));
    }

    public function uploadPhoto(Request $request, RackParentFolder $parentFolder, RackQrFolder $qrFolder)
    {
        if ($parentFolder->nik !== session('nik') || $qrFolder->rack_parent_folder_id !== $parentFolder->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $request->validate([
            'photo' => 'required|file|image|max:15360', // max 15MB
        ]);

        $file = $request->file('photo');
        $subDirPath = $qrFolder->uploadDir();

        if (!File::exists($subDirPath)) {
            File::makeDirectory($subDirPath, 0755, true);
        }

        $ext = $file->getClientOriginalExtension() ?: 'webp';
        $filename = time() . '_' . uniqid() . '.' . $ext;

        $file->move($subDirPath, $filename);

        $sanitizedQr = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $qrFolder->qr_code);
        $relativePath = 'uploads/rack/' . $parentFolder->name . '/' . $sanitizedQr . '/' . $filename;

        $photo = RackPhoto::create([
            'rack_qr_folder_id' => $qrFolder->id,
            'photo_path' => $relativePath,
            'filename' => $filename,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Foto berhasil disimpan!',
            'photo' => [
                'id' => $photo->id,
                'url' => asset($relativePath),
                'filename' => $filename,
                'created_at' => $photo->created_at->format('H:i:s'),
            ],
        ]);
    }

    public function deletePhoto(RackParentFolder $parentFolder, RackQrFolder $qrFolder, RackPhoto $photo)
    {
        if ($parentFolder->nik !== session('nik') && session('role') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        if (File::exists($photo->fullPath())) {
            @unlink($photo->fullPath());
        }

        $photo->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Foto berhasil dihapus.']);
        }

        return back()->with('success', 'Foto berhasil dihapus.');
    }

    public function destroyParentFolder(RackParentFolder $parentFolder)
    {
        if (session('role') !== 'admin' && $parentFolder->nik !== session('nik')) {
            abort(403, 'Akses tidak diizinkan.');
        }

        if (File::exists($parentFolder->uploadDir())) {
            File::deleteDirectory($parentFolder->uploadDir());
        }

        $parentFolder->delete();

        return back()->with('success', 'Folder Rack beserta seluruh fotonya berhasil dihapus.');
    }

    // ==========================================
    // ADMIN METHODS
    // ==========================================

    public function adminIndex(Request $request)
    {
        $query = RackParentFolder::with(['qrFolders.photos']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('member_name', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        $folders = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('rack.admin.index', compact('folders'));
    }

    public function adminShow(RackParentFolder $parentFolder)
    {
        $parentFolder->load(['qrFolders' => function ($q) {
            $q->with('photos')->orderByDesc('id');
        }]);

        return view('rack.admin.show', compact('parentFolder'));
    }
}
