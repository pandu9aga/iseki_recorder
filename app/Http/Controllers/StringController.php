<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\FolderString;
use Illuminate\Http\Request;

class StringController extends Controller
{
    public function store(Request $request, Folder $folder)
    {
        $this->authorizeFolder($folder);

        $data = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
        ]);

        FolderString::create([
            'folder_id' => $folder->id,
            'content' => trim($data['content']),
        ]);

        return back()->with('success', 'QR berhasil ditambahkan.');
    }

    public function destroy(Folder $folder, FolderString $string)
    {
        $this->authorizeFolder($folder);

        $string->delete();

        return back()->with('success', 'QR berhasil dihapus.');
    }

    protected function authorizeFolder(Folder $folder): void
    {
        if (session('role') === 'admin') {
            return;
        }

        if (session('role') === 'member' && session('nik') === $folder->nik) {
            return;
        }

        abort(403, 'Anda tidak memiliki akses ke folder ini.');
    }
}
