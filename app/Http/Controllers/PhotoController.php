<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\FolderPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PhotoController extends Controller
{
    public function store(Request $request, Folder $folder)
    {
        $this->authorizeFolder($folder);

        $saved = 0;

        if ($request->filled('photo_data')) {
            $filename = $this->saveCameraPhoto($folder, $request->input('photo_data'));

            if ($filename) {
                FolderPhoto::create([
                    'folder_id' => $folder->id,
                    'filename' => $filename,
                ]);
                $saved++;
            }
        }

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $filename = $this->saveUploadedPhoto($folder, $file);

                if ($filename) {
                    FolderPhoto::create([
                        'folder_id' => $folder->id,
                        'filename' => $filename,
                    ]);
                    $saved++;
                }
            }
        }

        if ($saved === 0) {
            return back()->withErrors(['photo' => 'Tidak ada foto yang valid untuk diunggah.']);
        }

        return back()->with('success', "{$saved} foto berhasil ditambahkan.");
    }

    public function destroy(Folder $folder, FolderPhoto $photo)
    {
        $this->authorizeFolder($folder);

        $path = $folder->photoPath($photo);

        if (is_file($path)) {
            unlink($path);
        }

        $photo->delete();

        return back()->with('success', 'Foto berhasil dihapus.');
    }

    protected function saveCameraPhoto(Folder $folder, string $dataUrl): ?string
    {
        if (! preg_match('#^data:image/(png|jpeg|jpg|webp);base64,#i', $dataUrl, $matches)) {
            return null;
        }

        $ext = strtolower($matches[1]) === 'jpeg' ? 'jpg' : strtolower($matches[1]);
        $binary = base64_decode(substr($dataUrl, strpos($dataUrl, ',') + 1));

        if ($binary === false || strlen($binary) === 0 || getimagesizefromstring($binary) === false) {
            return null;
        }

        $filename = $this->newFilename($ext);

        file_put_contents($folder->photoPathFromName($filename), $binary);

        return $filename;
    }

    protected function saveUploadedPhoto(Folder $folder, $file): ?string
    {
        if (! $file->isValid()) {
            return null;
        }

        $ext = strtolower($file->getClientOriginalExtension()) ?: 'jpg';

        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp'], true)) {
            return null;
        }

        $filename = $this->newFilename($ext);

        $file->move($folder->uploadDir(), $filename);

        return $filename;
    }

    protected function newFilename(string $ext): string
    {
        return 'foto_'.date('Ymd_His').'_'.Str::random(8).'.'.$ext;
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
