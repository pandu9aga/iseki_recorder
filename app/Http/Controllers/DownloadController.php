<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use ZipArchive;

class DownloadController extends Controller
{
    public function photosZip(Request $request, Folder $folder)
    {
        $this->authorizeFolder($folder);

        $selected = $request->input('photo_ids', []);

        if (empty($selected)) {
            return back()->withErrors(['photo' => 'Pilih minimal satu foto untuk diunduh.']);
        }

        $photos = $folder->photos()->whereIn('id', $selected)->get();

        if ($photos->isEmpty()) {
            return back()->withErrors(['photo' => 'Foto yang dipilih tidak ditemukan.']);
        }

        $zipName = 'foto_'.Str::slug($folder->nama).'_'.$folder->tanggal->format('Ymd').'.zip';
        $tempPath = tempnam(sys_get_temp_dir(), 'recorder_').'.zip';

        $zip = new ZipArchive;

        if ($zip->open($tempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->withErrors(['photo' => 'Gagal membuat file ZIP.']);
        }

        foreach ($photos as $photo) {
            $path = $folder->photoPath($photo);

            if (is_file($path)) {
                $zip->addFile($path, 'foto-foto/'.$photo->filename);
            }
        }

        $zip->close();

        return response()
            ->download($tempPath, $zipName)
            ->deleteFileAfterSend(true);
    }

    public function stringsExcel(Folder $folder)
    {
        $this->authorizeFolder($folder);

        $strings = $folder->strings()->orderBy('id')->get();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Strings');

        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Isi String');
        $sheet->setCellValue('C1', 'Waktu');

        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(60);
        $sheet->getColumnDimension('C')->setWidth(22);

        $row = 2;

        foreach ($strings as $index => $string) {
            $sheet->setCellValue('A'.$row, $index + 1);
            $sheet->setCellValue('B'.$row, $string->content);
            $sheet->setCellValue('C'.$row, $string->created_at ? $string->created_at->format('Y-m-d H:i:s') : '');
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'string_'.Str::slug($folder->nama).'_'.$folder->tanggal->format('Ymd').'.xlsx';
        $tempPath = tempnam(sys_get_temp_dir(), 'recorder_').'.xlsx';

        $writer->save($tempPath);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return response()
            ->download($tempPath, $fileName)
            ->deleteFileAfterSend(true);
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
