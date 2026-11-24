@extends('layouts.app')
@section('title', 'Scan QR Rack - ' . $parentFolder->name)

@section('content')
<div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <div class="flex items-center gap-2">
            <a href="{{ route('member.rack.index') }}" class="text-pink-600 hover:text-pink-800 font-bold">&larr; Kembali</a>
            <span class="text-gray-300">/</span>
            <span class="font-bold text-gray-800">{{ $parentFolder->name }}</span>
        </div>
        <h1 class="text-2xl font-bold text-pink-700 mt-1">🔍 Scan QR Code Sub-Folder</h1>
        <p class="text-xs text-gray-500">Scan QR Code untuk membuat / membuka sub-folder penyimpanan foto rack</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <!-- Scanner Card -->
    <div class="lg:col-span-7">
        <div class="overflow-hidden rounded-2xl border border-pink-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-bold text-pink-800 mb-3 flex items-center gap-2">
                <span>📷</span> Pemindai Kamera QR
            </h2>

            <!-- QR Reader Box -->
            <div id="reader" class="overflow-hidden rounded-xl border-2 border-dashed border-pink-300 bg-pink-50/30"></div>
            <p class="mt-2 text-center text-xs text-gray-500" id="scan-status">Arahkan kamera ke QR Code rack...</p>
            
            <div class="mt-3 flex justify-center gap-2">
                <button type="button" id="btn-rescan" class="hidden rounded-lg bg-pink-100 px-4 py-2 text-xs font-semibold text-pink-700 hover:bg-pink-200">
                    🔄 Scan Ulang
                </button>
            </div>

            <!-- Manual Form Input as Alternative -->
            <div class="mt-6 border-t border-gray-100 pt-5">
                <p class="text-xs font-semibold text-gray-600 mb-2">Atau masukkan string QR secara manual:</p>
                <form action="{{ route('member.rack.scan.process', $parentFolder->id) }}" method="POST" id="form-scan">
                    @csrf
                    <div class="flex gap-2">
                        <input type="text" id="qr_code" name="qr_code" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-pink-500 focus:outline-none focus:ring-1 focus:ring-pink-500" placeholder="Ketik string QR / Hasil scan">
                        <button type="submit" class="shrink-0 rounded-lg bg-pink-600 px-4 py-2 text-sm font-bold text-white hover:bg-pink-700 transition">
                            Lanjutkan &rarr;
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- History / Sub-folders created inside this parent -->
    <div class="lg:col-span-5">
        <div class="overflow-hidden rounded-2xl border border-pink-200 bg-white shadow-sm">
            <div class="border-b border-pink-100 bg-pink-50/50 px-5 py-3">
                <h2 class="font-bold text-sm text-pink-800">Daftar Sub-Folder QR di Folder Ini</h2>
            </div>
            
            @if($parentFolder->qrFolders->isEmpty())
                <div class="p-8 text-center">
                    <span class="text-3xl">📭</span>
                    <p class="mt-2 text-xs text-gray-500">Belum ada folder QR yang dibuat.<br>Scan QR Code di sebelah untuk mulai.</p>
                </div>
            @else
                <div class="max-h-[500px] overflow-y-auto divide-y divide-gray-100">
                    @foreach($parentFolder->qrFolders as $qrFolder)
                        <div class="p-3.5 hover:bg-pink-50/30 flex items-center justify-between transition">
                            <div class="truncate pr-2">
                                <div class="font-bold text-sm text-gray-800 truncate" title="{{ $qrFolder->qr_code }}">
                                    🏷️ {{ $qrFolder->qr_code }}
                                </div>
                                <div class="text-[11px] text-gray-400 mt-0.5">
                                    {{ $qrFolder->created_at->format('H:i:s') }} · <span class="text-pink-600 font-semibold">{{ $qrFolder->photos_count }} foto tersimpan</span>
                                </div>
                            </div>
                            <a href="{{ route('member.rack.camera', [$parentFolder->id, $qrFolder->id]) }}" class="shrink-0 rounded-lg bg-pink-50 text-pink-700 hover:bg-pink-600 hover:text-white px-3 py-1.5 text-xs font-bold transition">
                                Foto &rarr;
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<script src="{{ asset('js/html5-qrcode.min.js') }}" type="text/javascript"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let html5QrcodeScanner = new Html5QrcodeScanner(
            "reader",
            { fps: 10, qrbox: {width: 250, height: 250} },
            /* verbose= */ false
        );

        function startScanner() {
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        }

        startScanner();

        function onScanSuccess(decodedText, decodedResult) {
            document.getElementById('qr_code').value = decodedText;
            document.getElementById('scan-status').innerHTML = '<span class="text-green-600 font-bold">✓ QR Berhasil di-scan: ' + decodedText + '</span>';
            
            html5QrcodeScanner.clear().then(() => {
                document.getElementById('btn-rescan').classList.remove('hidden');
                // Auto submit form to enter camera
                document.getElementById('form-scan').submit();
            }).catch(err => {
                document.getElementById('btn-rescan').classList.remove('hidden');
                document.getElementById('form-scan').submit();
            });
        }

        function onScanFailure(error) {
            // keep scanning
        }

        document.getElementById('btn-rescan').addEventListener('click', function() {
            document.getElementById('qr_code').value = '';
            document.getElementById('scan-status').innerHTML = 'Arahkan kamera ke QR Code rack...';
            this.classList.add('hidden');
            
            html5QrcodeScanner = new Html5QrcodeScanner(
                "reader",
                { fps: 10, qrbox: {width: 250, height: 250} },
                /* verbose= */ false
            );
            startScanner();
        });
    });
</script>
@endsection
