<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
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

        $maxParts = $strings->map(fn ($string) => count($string->parts()))->max() ?? 0;

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('QR');

        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Waktu');

        for ($i = 1; $i <= $maxParts; $i++) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 2).'1', 'QR '.$i);
        }

        $lastColumn = Coordinate::stringFromColumnIndex($maxParts + 2);
        $sheet->getStyle('A1:'.$lastColumn.'1')->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(22);

        for ($i = 1; $i <= $maxParts; $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i + 2))->setWidth(40);
        }

        $row = 2;

        foreach ($strings as $index => $string) {
            $sheet->setCellValue('A'.$row, $index + 1);
            $sheet->setCellValue('B'.$row, $string->created_at ? $string->created_at->format('Y-m-d H:i:s') : '');

            foreach ($string->parts() as $i => $part) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 3).$row, $part);
            }

            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'qr_'.Str::slug($folder->nama).'_'.$folder->tanggal->format('Ymd').'.xlsx';
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
