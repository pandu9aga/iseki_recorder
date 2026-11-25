@extends('layouts.app')
@section('title', 'Admin - Sub-Folder Rack ' . $parentFolder->name)

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
        width: 200px;
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
        padding: 10px 14px !important;
        border-bottom: 2px solid #fbcfe8 !important;
    }
    table.dataTable tbody td {
        padding: 10px 14px !important;
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
        <div class="flex items-center gap-2 text-xs sm:text-sm">
            <a href="{{ route('admin.rack.index') }}" class="text-pink-600 hover:text-pink-800 font-semibold">&larr; Kembali ke Semua Folder</a>
            <span class="text-gray-300">/</span>
            <span class="text-gray-600 font-medium truncate max-w-xs">{{ $parentFolder->name }}</span>
        </div>
        <h1 class="text-2xl font-bold text-pink-700 mt-1">📁 {{ $parentFolder->name }}</h1>
        <p class="text-xs text-gray-500">
            Dibuat oleh: <strong class="text-gray-700">{{ $parentFolder->member_name }}</strong> (NIK: {{ $parentFolder->nik }}) · {{ $parentFolder->created_at->format('d M Y, H:i:s') }}
        </p>
    </div>

    <div>
        <form action="{{ route('admin.rack.destroy', $parentFolder->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus SELURUH folder induk ini beserta semua sub-folder dan foto di dalamnya?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-danger-solid">
                <span>🗑️</span> Hapus Seluruh Folder Induk
            </button>
        </form>
    </div>
</div>

<div class="overflow-hidden rounded-2xl border border-pink-200 bg-white shadow-sm p-6">
    <div class="border-b border-pink-100 pb-3 mb-4 flex items-center justify-between">
        <h2 class="font-bold text-base text-pink-800 flex items-center gap-2">
            <span>📑</span> Daftar Sub-Folder QR di Folder Ini
        </h2>
        <span class="rounded-full bg-pink-100 text-pink-700 px-3 py-0.5 text-xs font-bold">
            {{ $parentFolder->qrFolders->count() }} Sub-folder
        </span>
    </div>

    <div class="overflow-x-auto">
        <table id="adminSubfoldersTable" class="w-full text-left text-sm text-gray-600">
            <thead>
                <tr>
                    <th style="width: 45px;" class="text-center">No</th>
                    <th>Nama Sub-Folder (QR)</th>
                    <th style="width: 120px;" class="text-center">Total Foto</th>
                    <th style="width: 160px;" class="text-center">Waktu Dibuat</th>
                    <th style="width: 140px;" class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($parentFolder->qrFolders as $index => $qrFolder)
                    <tr>
                        <td class="text-center font-bold text-gray-400 text-xs">
                            {{ $index + 1 }}
                        </td>
                        <td class="font-bold text-gray-800">
                            <div class="flex items-center gap-2">
                                <span class="text-base">🏷️</span>
                                <span class="truncate max-w-[280px]" title="{{ $qrFolder->qr_code }}">
                                    {{ $qrFolder->qr_code }}
                                </span>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="inline-flex items-center gap-1 rounded-full bg-pink-100 px-2.5 py-0.5 text-xs font-bold text-pink-700">
                                🖼️ {{ $qrFolder->photos_count }} foto
                            </span>
                        </td>
                        <td class="text-center text-xs text-gray-500 font-mono">
                            {{ $qrFolder->created_at->format('d M Y - H:i:s') }}
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.rack.qr.show', [$parentFolder->id, $qrFolder->id]) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-pink-600 px-3.5 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-pink-700 transition">
                                <span>🖼️</span> Lihat Foto &rarr;
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Scripts -->
<script src="{{ asset('js/jquery.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/datatables.min.js') }}" type="text/javascript"></script>
<script>
    $(document).ready(function() {
        $('#adminSubfoldersTable').DataTable({
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
</script>
@endsection
