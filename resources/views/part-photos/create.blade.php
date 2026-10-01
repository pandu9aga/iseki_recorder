@extends('layouts.app')
@section('title', 'Tambah Photo Part')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-pink-700">Tambah Photo Part</h1>
    <a href="{{ route(session('role') . '.part-photos.index') }}" class="text-sm font-semibold text-pink-600 hover:text-pink-800">
        &larr; Kembali
    </a>
</div>

<div class="mx-auto max-w-lg overflow-hidden rounded-2xl border border-pink-200 bg-white p-6 shadow-sm">
    
    <!-- QR Scanner Section -->
    <div class="mb-6">
        <label class="mb-2 block text-sm font-semibold text-gray-700">1. Scan QR Code Part</label>
        <div id="reader" class="overflow-hidden rounded-xl border-2 border-dashed border-pink-300"></div>
        <p class="mt-2 text-xs text-gray-500 text-center" id="scan-status">Arahkan kamera ke QR Code...</p>
    </div>

    <!-- Form Section -->
    <form action="{{ route(session('role') . '.part-photos.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="mb-4">
            <label for="name" class="mb-1 block text-sm font-semibold text-gray-700">Name (Dari QR Code)</label>
            <input type="text" id="name" name="name" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm focus:border-pink-500 focus:outline-none focus:ring-1 focus:ring-pink-500" required readonly placeholder="Hasil scan akan muncul di sini">
            @error('name')
                <span class="mt-1 text-xs text-red-500">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-6">
            <label for="photo" class="mb-1 block text-sm font-semibold text-gray-700">2. Foto Part</label>
            <input type="file" id="photo" name="photo" accept="image/*" capture="environment" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm file:mr-4 file:rounded-md file:border-0 file:bg-pink-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-pink-700 hover:file:bg-pink-200" required onchange="previewImage(event)">
            <div id="preview-container" class="mt-3 hidden">
                <p class="mb-1 text-xs font-semibold text-gray-500">Preview:</p>
                <img id="photo-preview" src="#" alt="Preview Foto" class="max-h-64 rounded-xl border border-pink-200 object-cover shadow-sm">
            </div>
            @error('photo')
                <span class="mt-1 text-xs text-red-500">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="w-full rounded-xl bg-pink-600 py-3 text-center font-bold text-white shadow hover:bg-pink-700 focus:outline-none focus:ring-4 focus:ring-pink-500/50">
            Simpan Photo Part
        </button>
    </form>
</div>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const html5QrcodeScanner = new Html5QrcodeScanner(
            "reader",
            { fps: 10, qrbox: {width: 250, height: 250} },
            /* verbose= */ false);
        
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);

        function onScanSuccess(decodedText, decodedResult) {
            // Fill the input
            document.getElementById('name').value = decodedText;
            document.getElementById('scan-status').innerHTML = '<span class="text-green-600 font-bold">✓ QR Berhasil di-scan!</span>';
            
            // Stop scanning
            html5QrcodeScanner.clear();
        }

        function onScanFailure(error) {
            // handle scan failure, usually better to ignore and keep scanning
        }
    });

    function previewImage(event) {
        var input = event.target;
        var previewContainer = document.getElementById('preview-container');
        var previewImage = document.getElementById('photo-preview');

        if (input.files && input.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                previewContainer.classList.remove('hidden');
            }
            
            reader.readAsDataURL(input.files[0]);
        } else {
            previewImage.src = '#';
            previewContainer.classList.add('hidden');
        }
    }
</script>
@endsection
