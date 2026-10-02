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
    
    <!-- Form Section -->
    <form action="{{ route(session('role') . '.part-photos.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="mb-4">
            <label for="name" class="mb-1 block text-sm font-semibold text-gray-700">1. Name Part</label>
            <div class="flex gap-2">
                <input type="text" id="name" name="name" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-pink-500 focus:outline-none focus:ring-1 focus:ring-pink-500" required placeholder="Ketik nama part secara manual atau scan QR">
                <button type="button" id="btn-start-scan" class="shrink-0 rounded-lg bg-pink-100 px-4 py-2 text-sm font-semibold text-pink-700 hover:bg-pink-200">
                    📷 Scan QR
                </button>
            </div>
            @error('name')
                <span class="mt-1 text-xs text-red-500">{{ $message }}</span>
            @enderror
        </div>

        <!-- QR Scanner Section -->
        <div id="scanner-section" class="mb-6 hidden">
            <div id="reader" class="overflow-hidden rounded-xl border-2 border-dashed border-pink-300"></div>
            <p class="mt-2 text-xs text-gray-500 text-center" id="scan-status">Arahkan kamera ke QR Code...</p>
            <div class="mt-3 text-center flex justify-center gap-2">
                <button type="button" id="btn-rescan" class="hidden rounded-lg bg-pink-100 px-4 py-2 text-sm font-semibold text-pink-700 hover:bg-pink-200">
                    🔄 Scan Ulang
                </button>
                <button type="button" id="btn-close-scan" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200">
                    Tutup Scanner
                </button>
            </div>
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

<script src="{{ asset('js/html5-qrcode.min.js') }}" type="text/javascript"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let html5QrcodeScanner = new Html5QrcodeScanner(
            "reader",
            { fps: 10, qrbox: {width: 250, height: 250} },
            /* verbose= */ false);
        
        function startScanner() {
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        }

        function onScanSuccess(decodedText, decodedResult) {
            // Fill the input
            document.getElementById('name').value = decodedText;
            document.getElementById('scan-status').innerHTML = '<span class="text-green-600 font-bold">✓ QR Berhasil di-scan! Memeriksa database...</span>';
            
            // Stop scanning and show rescan button
            html5QrcodeScanner.clear().then(() => {
                document.getElementById('btn-rescan').classList.remove('hidden');
            }).catch(err => {
                document.getElementById('btn-rescan').classList.remove('hidden');
            });

            // Check database for existing name
            fetch(`{{ route(session('role') . '.part-photos.check') }}?name=${encodeURIComponent(decodedText)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.exists) {
                        document.getElementById('scan-status').innerHTML = '<span class="text-orange-600 font-bold">⚠️ Part ini sudah difoto. Upload foto baru akan mereplace foto yang lama.</span>';
                        alert("Peringatan: Part ini sudah pernah di-foto!\n\nJika Anda mengupload foto baru sekarang, foto yang lama akan ditimpa (replace).");
                    } else {
                        document.getElementById('scan-status').innerHTML = '<span class="text-green-600 font-bold">✓ QR Berhasil di-scan!</span>';
                    }
                })
                .catch(err => {
                    console.error(err);
                    document.getElementById('scan-status').innerHTML = '<span class="text-green-600 font-bold">✓ QR Berhasil di-scan!</span>';
                });
        }

        function onScanFailure(error) {
            // handle scan failure, usually better to ignore and keep scanning
        }

        document.getElementById('btn-start-scan').addEventListener('click', function() {
            document.getElementById('scanner-section').classList.remove('hidden');
            this.classList.add('hidden'); // Sembunyikan tombol scan QR
            startScanner();
        });

        document.getElementById('btn-close-scan').addEventListener('click', function() {
            document.getElementById('scanner-section').classList.add('hidden');
            document.getElementById('btn-start-scan').classList.remove('hidden');
            html5QrcodeScanner.clear().catch(e => {}); // Ignore clear errors if not running
        });

        document.getElementById('btn-rescan').addEventListener('click', function() {
            document.getElementById('name').value = '';
            document.getElementById('scan-status').innerHTML = 'Arahkan kamera ke QR Code...';
            this.classList.add('hidden');
            
            html5QrcodeScanner = new Html5QrcodeScanner(
                "reader",
                { fps: 10, qrbox: {width: 250, height: 250} },
                /* verbose= */ false
            );
            startScanner();
        });
    });

    function previewImage(event) {
        var input = event.target;
        var previewContainer = document.getElementById('preview-container');
        var previewImage = document.getElementById('photo-preview');

        if (input.files && input.files[0]) {
            var file = input.files[0];
            
            if (!file.type.match(/image.*/)) return;

            var reader = new FileReader();
            
            reader.onload = function(e) {
                var img = new Image();
                img.onload = function() {
                    var maxWidth = 3600;
                    var maxHeight = 3600;
                    var width = img.width;
                    var height = img.height;

                    if (width > height) {
                        if (width > maxWidth) {
                            height = Math.round((height *= maxWidth / width));
                            width = maxWidth;
                        }
                    } else {
                        if (height > maxHeight) {
                            width = Math.round((width *= maxHeight / height));
                            height = maxHeight;
                        }
                    }

                    var canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;

                    var ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob(function(blob) {
                        if (!blob) return;
                        
                        var objectUrl = URL.createObjectURL(blob);
                        previewImage.src = objectUrl;
                        previewContainer.classList.remove('hidden');

                        // Ganti ekstensi file menjadi .webp
                        var originalName = file.name.split('.').slice(0, -1).join('.') || file.name;
                        var newFileName = originalName + '.webp';

                        var compressedFile = new File([blob], newFileName, {
                            type: 'image/webp',
                            lastModified: Date.now()
                        });

                        var dataTransfer = new DataTransfer();
                        dataTransfer.items.add(compressedFile);
                        input.files = dataTransfer.files;
                        
                    }, 'image/webp', 0.8);
                };
                img.src = e.target.result;
            }
            
            reader.readAsDataURL(file);
        } else {
            previewImage.src = '#';
            previewContainer.classList.add('hidden');
        }
    }
</script>
@endsection
