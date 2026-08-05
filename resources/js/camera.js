export function initCameraModule() {
    const modal = document.getElementById('cameraModal');
    const openBtn = document.getElementById('openCamera');
    const video = document.getElementById('cameraFeed');
    const captureBtn = document.getElementById('capturePhoto');
    const statusEl = document.getElementById('cameraStatus');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (!modal || !openBtn || !video || !captureBtn) {
        return;
    }

    let stream = null;
    let submitting = false;

    async function startCamera() {
        statusEl.textContent = '';
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment' },
                audio: false,
            });
            video.srcObject = stream;
            await video.play();
        } catch (err) {
            statusEl.textContent = 'Tidak dapat mengakses kamera: ' + (err.message || 'izin ditolak');
        }
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach((track) => track.stop());
            stream = null;
        }
        video.srcObject = null;
    }

    function show(show) {
        if (show) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            startCamera();
        } else {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            stopCamera();
        }
    }

    async function capture() {
        if (submitting || !stream || video.videoWidth === 0) {
            return;
        }

        submitting = true;
        captureBtn.disabled = true;
        statusEl.textContent = 'Mengirim foto...';

        try {
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            const dataUrl = canvas.toDataURL('image/jpeg', 0.85);

            const form = new FormData();
            form.append('_token', csrf);
            form.append('photo_data', dataUrl);

            await fetch(openBtn.getAttribute('data-action'), {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: form,
            });

            window.location.reload();
        } catch (err) {
            statusEl.textContent = 'Gagal mengirim foto: ' + err.message;
            submitting = false;
            captureBtn.disabled = false;
        }
    }

    openBtn.addEventListener('click', () => show(true));
    modal.querySelectorAll('.closeCamera').forEach((btn) => btn.addEventListener('click', () => show(false)));
    captureBtn.addEventListener('click', capture);

    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            show(false);
        }
    });
}
