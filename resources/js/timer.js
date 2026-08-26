import { Html5Qrcode } from 'html5-qrcode';

export function initTimerModule() {
    const manualInput = document.getElementById('manualQrInput');
    const scanForm = document.getElementById('scanForm');
    const scanAlert = document.getElementById('scanAlert');
    const modal = document.getElementById('timerQrModal');
    const openModalBtn = document.getElementById('openTimerQrModal');
    const readerDiv = document.getElementById('timerQrReader');
    const statusEl = document.getElementById('timerQrStatus');
    const closeBtns = document.querySelectorAll('.closeTimerQr');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (!scanForm && !openModalBtn) {
        return;
    }

    const scanActionUrl = scanForm?.getAttribute('data-action') || openModalBtn?.getAttribute('data-action');

    // 1. Live Stopwatch Counter for Running Timers
    function updateLiveTimers() {
        const cards = document.querySelectorAll('.running-timer-card');
        const now = new Date();

        cards.forEach((card) => {
            const startStr = card.getAttribute('data-start');
            if (!startStr) return;

            const startDate = new Date(startStr);
            const diffSeconds = Math.max(0, Math.floor((now - startDate) / 1000));

            const hours = String(Math.floor(diffSeconds / 3600)).padStart(2, '0');
            const minutes = String(Math.floor((diffSeconds % 3600) / 60)).padStart(2, '0');
            const seconds = String(diffSeconds % 60).padStart(2, '0');

            const stopwatchEl = card.querySelector('.live-stopwatch');
            if (stopwatchEl) {
                stopwatchEl.textContent = `${hours}:${minutes}:${seconds}`;
            }
        });
    }

    setInterval(updateLiveTimers, 1000);
    updateLiveTimers();

    // 2. Alert Notification Helper
    function showAlert(message, type = 'success') {
        if (!scanAlert) return;

        scanAlert.className = 'mt-4 rounded-xl p-4 text-sm font-semibold transition-all ';
        if (type === 'success') {
            scanAlert.className += 'border border-emerald-200 bg-emerald-50 text-emerald-800';
        } else if (type === 'start') {
            scanAlert.className += 'border border-amber-200 bg-amber-50 text-amber-800';
        } else {
            scanAlert.className += 'border border-red-200 bg-red-50 text-red-800';
        }

        scanAlert.textContent = message;
        scanAlert.classList.remove('hidden');

        setTimeout(() => {
            scanAlert.classList.add('hidden');
        }, 5000);
    }

    // 3. Audio Beep feedback
    function playBeep(success = true) {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.connect(gain);
            gain.connect(ctx.destination);

            osc.type = 'sine';
            osc.frequency.value = success ? 880 : 330; // A5 for success, E4 for alert
            gain.gain.setValueAtTime(0.15, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.2);

            osc.start(ctx.currentTime);
            osc.stop(ctx.currentTime + 0.2);
        } catch (e) {
            // ignore audio context restrictions
        }
    }

    // 4. Send Scan Request
    let isProcessing = false;
    async function processScan(qrCode) {
        if (!qrCode || isProcessing) return;
        isProcessing = true;

        const formData = new FormData();
        formData.append('_token', csrf);
        formData.append('qr_code', qrCode);

        try {
            const res = await fetch(scanActionUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const data = await res.json();

            if (!res.ok) {
                throw new Error(data.message || 'Gagal memproses QR code.');
            }

            playBeep(true);

            if (data.action === 'stop') {
                showAlert(`✅ ${data.message} (QR: ${data.timer.qr_code})`, 'success');
            } else {
                showAlert(`⏳ ${data.message} (QR: ${data.timer.qr_code})`, 'start');
            }

            // Reload page to refresh tables and stats
            setTimeout(() => {
                window.location.reload();
            }, 800);

        } catch (err) {
            playBeep(false);
            showAlert(`❌ ${err.message}`, 'error');
            isProcessing = false;
        }
    }

    // 5. Form Submit (Manual / Barcode Scanner)
    if (scanForm && manualInput) {
        scanForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const val = manualInput.value.trim();
            if (!val) return;
            manualInput.value = '';
            processScan(val);
        });
    }

    // 6. Camera Scanner (html5-qrcode)
    let scanner = null;
    let scanning = false;

    async function stopScanner() {
        if (scanner && scanning) {
            try {
                await scanner.stop();
            } catch (e) {
                // ignore
            }
            scanning = false;
        }

        if (scanner) {
            try {
                scanner.clear();
            } catch (e) {
                // ignore
            }
            scanner = null;
        }

        if (readerDiv) {
            readerDiv.innerHTML = '';
        }
    }

    async function toggleModal(show) {
        if (!modal) return;

        if (show) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            startCameraScanner();
        } else {
            await stopScanner();
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    async function startCameraScanner() {
        if (!statusEl || !readerDiv) return;
        statusEl.textContent = 'Menghubungkan ke kamera...';

        try {
            scanner = new Html5Qrcode('timerQrReader');

            await scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 240, height: 240 } },
                async (decodedText) => {
                    await stopScanner();
                    toggleModal(false);
                    processScan(decodedText);
                },
                () => {
                    // Scanning active
                }
            );

            scanning = true;
            statusEl.textContent = 'Arahkan kamera ke kode QR...';
        } catch (err) {
            statusEl.textContent = 'Gagal mengakses kamera: ' + (err.message || 'Izin kamera ditolak');
        }
    }

    if (openModalBtn) {
        openModalBtn.addEventListener('click', () => toggleModal(true));
    }

    closeBtns.forEach((btn) => {
        btn.addEventListener('click', () => toggleModal(false));
    });

    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                toggleModal(false);
            }
        });
    }
}
