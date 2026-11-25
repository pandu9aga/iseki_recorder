@extends('layouts.app')
@section('title', 'Scan QR Rack - ' . $parentFolder->name)

@section('content')
<style>
    /* Styling Tombol Danger */
    .btn-danger-solid {
        background-color: #dc2626 !important;
        color: #ffffff !important;
        border: 1px solid #b91c1c !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        padding: 8px 16px !important;
        border-radius: 12px !important;
        font-weight: 700 !important;
        font-size: 13px !important;
        box-shadow: 0 2px 4px rgba(220, 38, 38, 0.25) !important;
        cursor: pointer !important;
        transition: all 0.2s ease-in-out !important;
    }
    .btn-danger-solid:hover {
        background-color: #b91c1c !important;
        box-shadow: 0 4px 6px rgba(185, 28, 28, 0.35) !important;
    }
    .btn-danger-solid:active {
        background-color: #991b1b !important;
    }

    /* Rapi DataTables Container */
    .dataTables_wrapper {
        font-family: inherit;
        width: 100% !important;
    }
    /* Controls Header: Search & Entries */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        display: inline-block;
        margin-bottom: 14px;
    }
    .dataTables_wrapper .dataTables_length {
        float: left;
    }
    .dataTables_wrapper .dataTables_filter {
        float: right;
        text-align: right;
    }
    .dataTables_wrapper .dataTables_length select {
        padding: 5px 28px 5px 10px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        outline: none;
        background-color: #ffffff;
        font-size: 13px;
    }
    .dataTables_wrapper .dataTables_filter input {
        padding: 6px 12px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        outline: none;
        margin-left: 8px;
        background-color: #ffffff;
        font-size: 13px;
        width: 180px;
        transition: border-color 0.2s;
    }
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #db2777;
    }
    /* Table Styling */
    table.dataTable {
        width: 100% !important;
        border-collapse: collapse !important;
        clear: both;
        margin-top: 6px !important;
        margin-bottom: 14px !important;
    }
    table.dataTable thead th {
        background-color: #fdf2f8 !important;
        color: #831843 !important;
        font-weight: 700 !important;
        font-size: 12px !important;
        text-transform: uppercase !important;
        padding: 10px 12px !important;
        border-bottom: 2px solid #fbcfe8 !important;
    }
    table.dataTable tbody td {
        padding: 10px 12px !important;
        border-bottom: 1px solid #f1f5f9 !important;
        vertical-align: middle !important;
    }
    table.dataTable tbody tr:hover {
        background-color: #fff1f2 !important;
    }
    /* Controls Footer: Info & Pagination */
    .dataTables_wrapper .dataTables_info {
        float: left;
        padding-top: 10px;
        font-size: 12px;
        color: #64748b;
    }
    .dataTables_wrapper .dataTables_paginate {
        float: right;
        text-align: right;
        padding-top: 8px;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        display: inline-block;
        padding: 5px 12px !important;
        margin-left: 4px;
        border-radius: 8px !important;
        border: 1px solid #e2e8f0 !important;
        background: #ffffff !important;
        color: #475569 !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        cursor: pointer !important;
        text-decoration: none !important;
        transition: all 0.15s ease-in-out;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #fdf2f8 !important;
        border-color: #f472b6 !important;
        color: #db2777 !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #db2777 !important;
        border-color: #db2777 !important;
        color: #ffffff !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        background: #f8fafc !important;
        border-color: #e2e8f0 !important;
        color: #94a3b8 !important;
        cursor: not-allowed !important;
    }
    /* Clearfix */
    .dataTables_wrapper::after {
        content: "";
        display: table;
        clear: both;
    }
</style>

<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <div class="flex items-center gap-2">
            <a href="{{ route('member.rack.index') }}" class="text-pink-600 hover:text-pink-800 font-bold">&larr; Kembali</a>
            <span class="text-gray-300">/</span>
            <span class="font-bold text-gray-800 truncate max-w-xs">{{ $parentFolder->name }}</span>
        </div>
        <h1 class="text-2xl font-bold text-pink-700 mt-1">🔍 Scan QR Code Sub-Folder</h1>
        <p class="text-xs text-gray-500">Scan QR Code untuk membuat / membuka sub-folder penyimpanan foto rack</p>
    </div>

    <div>
        <form action="{{ route('member.rack.destroy', $parentFolder->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus SELURUH folder induk ini beserta semua sub-folder dan foto di dalamnya?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-danger-solid">
                <span>🗑️</span> Hapus Seluruh Folder Induk
            </button>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <!-- Scanner Card -->
    <div class="lg:col-span-5">
        <div class="overflow-hidden rounded-2xl border border-pink-200 bg-white p-5 shadow-sm sticky top-20">
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
            <div class="mt-5 border-t border-gray-100 pt-4">
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

    <!-- DataTables Card for QR Sub-Folders -->
    <div class="lg:col-span-7">
        <div class="overflow-hidden rounded-2xl border border-pink-200 bg-white shadow-sm p-5">
            <div class="border-b border-pink-100 pb-3 mb-4 flex items-center justify-between">
                <h2 class="font-bold text-base text-pink-800 flex items-center gap-2">
                    <span>📑</span> Daftar Sub-Folder QR
                </h2>
                <span class="rounded-full bg-pink-100 text-pink-700 px-3 py-0.5 text-xs font-bold">
                    {{ $parentFolder->qrFolders->count() }} Sub-folder
                </span>
            </div>
            
            <div class="overflow-x-auto">
                <table id="subfoldersTable" class="w-full text-left text-sm text-gray-600">
                    <thead>
                        <tr>
                            <th style="width: 45px;" class="text-center">No</th>
                            <th>Nama Sub-Folder (QR)</th>
                            <th style="width: 110px;" class="text-center">Total Foto</th>
                            <th style="width: 90px;" class="text-center">Jam</th>
                            <th style="width: 120px;" class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($parentFolder->qrFolders as $index => $qrFolder)
                            <tr>
                                <td class="text-center font-bold text-gray-400 text-xs">
                                    {{ $index + 1 }}
                                </td>
                                <td class="font-bold text-gray-800">
                                    <div class="flex items-center gap-1.5">
                                        <span>🏷️</span>
                                        <span class="truncate max-w-[180px] sm:max-w-[220px]" title="{{ $qrFolder->qr_code }}">
                                            {{ $qrFolder->qr_code }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-pink-100 px-2.5 py-0.5 text-xs font-bold text-pink-700">
                                        🖼️ {{ $qrFolder->photos_count }}
                                    </span>
                                </td>
                                <td class="text-center text-xs text-gray-500 font-mono">
                                    {{ $qrFolder->created_at->format('H:i:s') }}
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('member.rack.camera', [$parentFolder->id, $qrFolder->id]) }}" class="inline-flex items-center gap-1 rounded-lg bg-pink-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-pink-700 transition">
                                        <span>📷</span> Foto &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="{{ asset('js/jquery.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/datatables.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/html5-qrcode.min.js') }}" type="text/javascript"></script>
<script>
    $(document).ready(function() {
        $('#subfoldersTable').DataTable({
            language: {
                search: "Cari:",
                lengthMenu: "Tampil _MENU_",
                zeroRecords: "Tidak ada sub-folder yang sesuai",
                info: "Hal _PAGE_ dari _PAGES_ (Total _TOTAL_)",
                infoEmpty: "0 sub-folder",
                infoFiltered: "(filter dari _MAX_)",
                paginate: {
                    first: "«",
                    last: "»",
                    next: "›",
                    previous: "‹"
                }
            },
            order: [[3, 'desc']],
            pageLength: 10,
            autoWidth: false
        });
    });

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
