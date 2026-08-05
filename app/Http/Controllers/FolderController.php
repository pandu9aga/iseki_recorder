<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FolderController extends Controller
{
    public function memberIndex()
    {
        $folders = Folder::where('nik', session('nik'))
            ->withCount('photos')
            ->withCount('strings')
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        return view('member.folders.index', compact('folders'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'tanggal' => ['required', 'date'],
        ]);

        $folder = Folder::create([
            'nama' => $data['nama'],
            'nik' => session('nik'),
            'tanggal' => $data['tanggal'],
        ]);

        $this->ensureUploadDir($folder);

        return redirect()->route('member.folders.show', $folder)->with('success', 'Folder berhasil dibuat.');
    }

    public function adminIndex(Request $request)
    {
        $search = trim((string) $request->query('q'));

        $folders = Folder::query()
            ->withCount('photos')
            ->withCount('strings')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%")
                        ->orWhere('tanggal', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('admin.folders.index', compact('folders', 'search'));
    }

    public function show(Request $request, Folder $folder)
    {
        if (! $this->canAccess($folder)) {
            abort(403, 'Anda tidak memiliki akses ke folder ini.');
        }

        $folder->load(['photos', 'strings']);

        return view('folders.show', compact('folder'));
    }

    public function destroy(Folder $folder)
    {
        if (! $this->canAccess($folder)) {
            abort(403, 'Anda tidak memiliki akses ke folder ini.');
        }

        $dir = $folder->uploadDir();

        if (is_dir($dir)) {
            $this->deleteDirectory($dir);
        }

        $folder->delete();

        if (session('role') === 'admin') {
            return redirect()->route('admin.folders.index')->with('success', 'Folder berhasil dihapus.');
        }

        return redirect()->route('member.folders.index')->with('success', 'Folder berhasil dihapus.');
    }

    protected function canAccess(Folder $folder): bool
    {
        if (session('role') === 'admin') {
            return true;
        }

        return session('role') === 'member' && session('nik') === $folder->nik;
    }

    protected function ensureUploadDir(Folder $folder): void
    {
        $dir = $folder->uploadDir();

        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
    }

    protected function deleteDirectory(string $dir): void
    {
        $items = array_diff(scandir($dir), ['.', '..']);

        foreach ($items as $item) {
            $path = $dir.DIRECTORY_SEPARATOR.$item;

            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }
}
