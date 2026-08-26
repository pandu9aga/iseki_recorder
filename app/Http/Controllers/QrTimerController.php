<?php

namespace App\Http\Controllers;

use App\Models\QrTimer;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class QrTimerController extends Controller
{
    public function index(Request $request)
    {
        $role = session('role');
        $nik = session('nik');

        $query = QrTimer::query();

        // If member, only see their own records. Admin sees all (or filter by NIK).
        if ($role === 'member') {
            $query->where('nik', $nik);
        } elseif ($request->filled('filter_nik')) {
            $query->where('nik', $request->filter_nik);
        }

        if ($request->filled('date')) {
            $query->whereDate('start_time', $request->date);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('qr_code', 'like', "%{$search}%")
                  ->orWhere('member_name', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        // Active timers (running)
        $runningTimers = (clone $query)
            ->where('status', 'running')
            ->orderByDesc('start_time')
            ->get();

        // Completed timers
        $completedTimers = (clone $query)
            ->where('status', 'completed')
            ->orderByDesc('end_time')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'running' => (clone $query)->where('status', 'running')->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'total' => (clone $query)->count(),
        ];

        return view('timer.index', compact('runningTimers', 'completedTimers', 'stats'));
    }

    public function scan(Request $request): JsonResponse
    {
        $request->validate([
            'qr_code' => ['required', 'string'],
        ]);

        $qrCode = trim($request->qr_code);
        $role = session('role');
        $nik = session('nik') ?? 'admin';
        $memberName = session('name') ?? ($role === 'admin' ? 'Admin' : 'Member');

        $now = Carbon::now();

        // Find the earliest active (running) timer with the same QR for this user
        $runningTimer = QrTimer::where('status', 'running')
            ->where('nik', $nik)
            ->where('qr_code', $qrCode)
            ->orderBy('start_time', 'asc')
            ->first();

        if ($runningTimer) {
            // Scan Kedua (End / Stop Timer)
            $startTime = Carbon::parse($runningTimer->start_time);
            $durationSeconds = max(0, $startTime->diffInSeconds($now));

            $runningTimer->update([
                'end_time' => $now,
                'duration_seconds' => $durationSeconds,
                'status' => 'completed',
            ]);

            return response()->json([
                'status' => 'completed',
                'action' => 'stop',
                'message' => 'Scan selesai! Durasi: ' . $runningTimer->formatted_duration,
                'timer' => [
                    'id' => $runningTimer->id,
                    'qr_code' => $runningTimer->qr_code,
                    'start_time' => $runningTimer->start_time->format('d/m/Y H:i:s'),
                    'end_time' => $runningTimer->end_time->format('d/m/Y H:i:s'),
                    'duration_clock' => $runningTimer->duration_clock,
                    'formatted_duration' => $runningTimer->formatted_duration,
                    'duration_seconds' => $runningTimer->duration_seconds,
                ],
            ]);
        }

        // Scan Pertama (Start Timer)
        $newTimer = QrTimer::create([
            'nik' => $nik,
            'member_name' => $memberName,
            'qr_code' => $qrCode,
            'start_time' => $now,
            'status' => 'running',
        ]);

        return response()->json([
            'status' => 'running',
            'action' => 'start',
            'message' => 'Scan pertama berhasil dicatat! Timer mulai berjalan.',
            'timer' => [
                'id' => $newTimer->id,
                'qr_code' => $newTimer->qr_code,
                'start_time' => $newTimer->start_time->format('d/m/Y H:i:s'),
                'start_time_iso' => $newTimer->start_time->toIso8601String(),
            ],
        ]);
    }

    public function destroy(QrTimer $timer)
    {
        $this->authorizeTimer($timer);

        $timer->delete();

        return back()->with('success', 'Data timer berhasil dihapus.');
    }

    public function exportExcel(Request $request)
    {
        $role = session('role');
        $nik = session('nik');

        $query = QrTimer::query()->orderBy('start_time', 'asc');

        if ($role === 'member') {
            $query->where('nik', $nik);
        } elseif ($request->filled('filter_nik')) {
            $query->where('nik', $request->filter_nik);
        }

        if ($request->filled('date')) {
            $query->whereDate('start_time', $request->date);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('qr_code', 'like', "%{$search}%")
                  ->orWhere('member_name', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        $timers = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Timer QR');

        // Headers
        $headers = [
            'A1' => 'No',
            'B1' => 'NIK',
            'C1' => 'Nama Member',
            'D1' => 'Data QR',
            'E1' => 'Scan Pertama (Start)',
            'F1' => 'Scan Terakhir (End)',
            'G1' => 'Selisih / Durasi',
            'H1' => 'Durasi (Detik)',
            'I1' => 'Status',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        $sheet->getStyle('A1:I1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFCE7F3'); // soft pink

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(22);
        $sheet->getColumnDimension('D')->setWidth(40);
        $sheet->getColumnDimension('E')->setWidth(22);
        $sheet->getColumnDimension('F')->setWidth(22);
        $sheet->getColumnDimension('G')->setWidth(18);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(14);

        $row = 2;
        foreach ($timers as $index => $t) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValueExplicit('B' . $row, $t->nik, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $row, $t->member_name ?? '-');
            $sheet->setCellValue('D' . $row, $t->qr_code);
            $sheet->setCellValue('E' . $row, $t->start_time ? $t->start_time->format('Y-m-d H:i:s') : '-');
            $sheet->setCellValue('F' . $row, $t->end_time ? $t->end_time->format('Y-m-d H:i:s') : '-');
            $sheet->setCellValue('G' . $row, $t->formatted_duration);
            $sheet->setCellValue('H' . $row, $t->duration_seconds ?? 0);
            $sheet->setCellValue('I' . $row, $t->status === 'completed' ? 'Selesai' : 'Sedang Berjalan');

            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'laporan_timer_qr_' . date('Ymd_His') . '.xlsx';
        $tempPath = tempnam(sys_get_temp_dir(), 'timer_') . '.xlsx';

        $writer->save($tempPath);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return response()
            ->download($tempPath, $fileName)
            ->deleteFileAfterSend(true);
    }

    protected function authorizeTimer(QrTimer $timer): void
    {
        if (session('role') === 'admin') {
            return;
        }

        abort(403, 'Hanya admin yang memiliki izin untuk menghapus data timer.');
    }
}

