@extends('layouts.app')
@section('title', 'Ambil Foto Rack - ' . $qrFolder->qr_code)

@section('content')
<div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <div class="flex items-center gap-2 text-xs sm:text-sm">
            <a href="{{ route('member.rack.index') }}" class="text-pink-600 hover:text-pink-800 font-semibold">Rack</a>
            <span class="text-gray-300">/</span>
            <span class="text-gray-500 truncate max-w-[120px] sm:max-w-xs">{{ $parentFolder->name }}</span>
            <span class="text-gray-300">/</span>
            <span class="font-bold text-gray-800">{{ $qrFolder->qr_code }}</span>
        </div>
        <h1 class="text-xl sm:text-2xl font-bold text-pink-700 mt-1 flex items-center gap-2">
            <span>📷</span> Ambil Foto Sub-Folder: <span class="bg-pink-100 text-pink-800 px-2.5 py-0.5 rounded-lg font-mono text-base sm:text-lg">{{ $qrFolder->qr_code }}</span>
        </h1>
    </div>
    
    <div>
        <a href="{{ route('member.rack.scan', $parentFolder->id) }}" class="inline-flex items-center gap-1.5 rounded-xl border-2 border-pink-600 bg-white px-4 py-2 text-xs sm:text-sm font-bold text-pink-600 hover:bg-pink-50 transition shadow-sm">
            <span>🔄</span> Scan QR Lain / Baru
        </a>
    </div>
</div>

<!-- Upload & Camera Section -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <div class="lg:col-span-4">
        <div class="overflow-hidden rounded-2xl border border-pink-200 bg-white p-5 shadow-sm sticky top-20">
            <h2 class="text-sm font-bold text-pink-800 mb-3 flex items-center gap-1.5">
                <span>📸</span> Jepret / Pilih Foto
            </h2>

            <div class="mb-4">
                <label for="cameraInput" class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-pink-300 rounded-xl cursor-pointer bg-pink-50/50 hover:bg-pink-100/50 transition">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center px-2">
                        <span class="text-3xl mb-1">📷</span>
                        <p class="text-xs font-bold text-pink-700">Ketuk untuk Ambil Foto Kamera</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Otomatis terkompresi size-nya (.webp)</p>
                    </div>
                    <input id="cameraInput" type="file" accept="image/*" capture="environment" class="hidden" onchange="handlePhotoUpload(event)">
                </label>
            </div>

            <!-- Upload Progress Status -->
            <div id="uploadStatus" class="hidden rounded-lg p-3 text-xs mb-3"></div>

            <div class="rounded-xl bg-gray-50 p-3 text-[12px] text-gray-600 space-y-1">
                <p class="font-semibold text-gray-700">💡 Informasi:</p>
                <p>• Anda dapat mengambil foto bebas sebanyak-banyaknya di folder QR ini.</p>
                <p>• Setelah selesai mengambil foto di rak ini, klik tombol <strong>"Scan QR Lain / Baru"</strong> di atas untuk memindai rak berikutnya.</p>
            </div>
        </div>
    </div>

    <!-- Gallery of Photos in This QR Folder -->
    <div class="lg:col-span-8">
        <div class="overflow-hidden rounded-2xl border border-pink-200 bg-white shadow-sm">
            <div class="border-b border-pink-100 bg-pink-50/50 px-5 py-3 flex items-center justify-between">
                <h2 class="font-bold text-sm text-pink-800">
                    Foto Tersimpan (<span id="photoCount">{{ $qrFolder->photos->count() }}</span>)
                </h2>
                <span class="text-xs text-gray-400">Tersimpan di direktori uploads</span>
            </div>

            <div id="photosGrid" class="p-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                @forelse($qrFolder->photos as $photo)
                    <div class="group relative rounded-xl border border-gray-200 overflow-hidden bg-gray-50 shadow-sm" id="photo-card-{{ $photo->id }}">
                        <a href="{{ $photo->url() }}" target="_blank" class="block aspect-square overflow-hidden bg-black/5">
                            <img src="{{ $photo->url() }}" alt="Foto Rack" class="h-full w-full object-cover group-hover:scale-105 transition duration-200">
                        </a>
                        <div class="p-1.5 flex items-center justify-between bg-white text-[10px] text-gray-500">
                            <span class="truncate">{{ $photo->created_at->format('H:i:s') }}</span>
                            <button type="button" onclick="deletePhoto({{ $photo->id }})" class="text-red-500 hover:text-red-700 font-bold p-1" title="Hapus foto">
                                🗑️
                            </button>
                        </div>
                    </div>
                @empty
                    <div id="emptyPlaceholder" class="col-span-full py-12 text-center text-gray-400">
                        <span class="text-4xl">🖼️</span>
                        <p class="mt-2 text-xs font-semibold">Belum ada foto yang diambil untuk QR ini.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
    const csrfToken = '{{ csrf_token() }}';
    const uploadUrl = '{{ route('member.rack.photos.upload', [$parentFolder->id, $qrFolder->id]) }}';
    const deleteUrlBase = '{{ url("member/rack/{$parentFolder->id}/qr/{$qrFolder->id}/photos") }}';

    function setStatus(type, message) {
        const statusBox = document.getElementById('uploadStatus');
        statusBox.classList.remove('hidden', 'bg-blue-50', 'text-blue-700', 'bg-green-50', 'text-green-700', 'bg-red-50', 'text-red-700');
        
        if (type === 'loading') {
            statusBox.classList.add('bg-blue-50', 'text-blue-700');
            statusBox.innerHTML = `⏳ ${message}`;
        } else if (type === 'success') {
            statusBox.classList.add('bg-green-50', 'text-green-700');
            statusBox.innerHTML = `✓ ${message}`;
            setTimeout(() => { statusBox.classList.add('hidden'); }, 3000);
        } else {
            statusBox.classList.add('bg-red-50', 'text-red-700');
            statusBox.innerHTML = `✗ ${message}`;
        }
    }

    function handlePhotoUpload(event) {
        const file = event.target.files[0];
        if (!file || !file.type.match(/image.*/)) return;

        setStatus('loading', 'Mengompresi foto...');

        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                const maxWidth = 3600;
                const maxHeight = 3600;
                let width = img.width;
                let height = img.height;

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

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob(function(blob) {
                    if (!blob) {
                        setStatus('error', 'Gagal memproses gambar');
                        return;
                    }

                    setStatus('loading', 'Mengunggah foto terkompres...');

                    const originalName = file.name.split('.').slice(0, -1).join('.') || 'rack_photo';
                    const compressedFileName = originalName + '.webp';

                    const formData = new FormData();
                    formData.append('photo', blob, compressedFileName);
                    formData.append('_token', csrfToken);

                    fetch(uploadUrl, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            setStatus('success', 'Foto berhasil disimpan!');
                            appendPhotoCard(data.photo);
                            // Reset input agar bisa jepret foto berikutnya
                            event.target.value = '';
                        } else {
                            setStatus('error', data.message || 'Gagal mengunggah');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        setStatus('error', 'Terjadi kesalahan saat unggah.');
                    });

                }, 'image/webp', 0.8);
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    function appendPhotoCard(photo) {
        const placeholder = document.getElementById('emptyPlaceholder');
        if (placeholder) placeholder.remove();

        const grid = document.getElementById('photosGrid');
        const card = document.createElement('div');
        card.id = `photo-card-${photo.id}`;
        card.className = 'group relative rounded-xl border border-gray-200 overflow-hidden bg-gray-50 shadow-sm animate-fade-in';
        card.innerHTML = `
            <a href="${photo.url}" target="_blank" class="block aspect-square overflow-hidden bg-black/5">
                <img src="${photo.url}" alt="Foto Rack" class="h-full w-full object-cover group-hover:scale-105 transition duration-200">
            </a>
            <div class="p-1.5 flex items-center justify-between bg-white text-[10px] text-gray-500">
                <span class="truncate">${photo.created_at}</span>
                <button type="button" onclick="deletePhoto(${photo.id})" class="text-red-500 hover:text-red-700 font-bold p-1" title="Hapus foto">
                    🗑️
                </button>
            </div>
        `;
        grid.prepend(card);

        const countSpan = document.getElementById('photoCount');
        countSpan.innerText = parseInt(countSpan.innerText || 0) + 1;
    }

    function deletePhoto(photoId) {
        if (!confirm('Apakah Anda yakin ingin menghapus foto ini?')) return;

        fetch(`${deleteUrlBase}/${photoId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const el = document.getElementById(`photo-card-${photoId}`);
                if (el) el.remove();
                const countSpan = document.getElementById('photoCount');
                countSpan.innerText = Math.max(0, parseInt(countSpan.innerText || 0) - 1);
            } else {
                alert(data.message || 'Gagal menghapus foto');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan saat menghapus foto');
        });
    }
</script>
@endsection
