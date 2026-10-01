<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PartPhotoController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\PartPhoto::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        
        $allowedSorts = ['created_at', 'name'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $direction === 'asc' ? 'asc' : 'desc');
        }

        $partPhotos = $query->paginate(50)->withQueryString();

        return view('part-photos.index', compact('partPhotos', 'sort', 'direction'));
    }

    public function exportExcel()
    {
        if (session('role') !== 'admin') {
            abort(403);
        }

        $photos = \App\Models\PartPhoto::orderBy('created_at', 'desc')->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Name (QR)');
        $sheet->setCellValue('C1', 'Waktu Dibuat');

        $row = 2;
        $no = 1;
        foreach ($photos as $p) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $p->name);
            $sheet->setCellValue('C' . $row, $p->created_at->format('Y-m-d H:i:s'));
            $row++;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'Data_Part_Photos_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
        $writer->save('php://output');
        exit;
    }

    public function create()
    {
        return view('part-photos.create');
    }

    public function checkName(Request $request)
    {
        $exists = \App\Models\PartPhoto::where('name', $request->name)->exists();
        return response()->json(['exists' => $exists]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'photo' => 'required|image|max:10240', // max 10MB
        ]);

        $file = $request->file('photo');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('uploads/part photos'), $filename);
        $photoPath = 'uploads/part photos/' . $filename;

        $existing = \App\Models\PartPhoto::where('name', $request->name)->first();

        if ($existing) {
            if (file_exists(public_path($existing->photo_path))) {
                @unlink(public_path($existing->photo_path));
            }
            $existing->update([
                'photo_path' => $photoPath,
            ]);
            $msg = 'Photo part berhasil diperbarui!';
        } else {
            \App\Models\PartPhoto::create([
                'name' => $request->name,
                'photo_path' => $photoPath,
            ]);
            $msg = 'Photo part berhasil disimpan!';
        }

        return redirect()->route(session('role') . '.part-photos.index')->with('success', $msg);
    }

    public function destroy(\App\Models\PartPhoto $partPhoto)
    {

        if (file_exists(public_path($partPhoto->photo_path))) {
            @unlink(public_path($partPhoto->photo_path));
        }

        $partPhoto->delete();

        return redirect()->back()->with('success', 'Photo part berhasil dihapus!');
    }
}
