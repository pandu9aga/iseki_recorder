import { Html5Qrcode } from 'html5-qrcode';

export function initQrModule() {
    const modal = document.getElementById('qrModal');
    const openBtn = document.getElementById('openQr');
    const readerDiv = document.getElementById('qrReader');
    const statusEl = document.getElementById('qrStatus');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (!modal || !openBtn || !readerDiv || !statusEl) {
        return;
    }

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

        readerDiv.innerHTML = '';
    }

    async function show(show) {
        if (show) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            startScanner();
        } else {
            await stopScanner();
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    async function startScanner() {
        statusEl.textContent = 'Arahkan kamera ke kode QR.';

        try {
            scanner = new Html5Qrcode('qrReader');

            await scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 220, height: 220 } },
                async (decodedText) => {
                    await stopScanner();
                    statusEl.textContent = 'QR terbaca, menyimpan...';

                    const form = new FormData();
                    form.append('_token', csrf);
                    form.append('content', decodedText);

                    try {
                        await fetch(openBtn.getAttribute('data-action'), {
                            method: 'POST',
                            headers: { 'Accept': 'application/json' },
                            body: form,
                        });

                        window.location.reload();
                    } catch (err) {
                        statusEl.textContent = 'Gagal menyimpan: ' + err.message;
                    }
                },
                () => {
                    // keep scanning
                }
            );

            scanning = true;
        } catch (err) {
            statusEl.textContent = 'Tidak dapat memulai kamera: ' + (err.message || 'izin ditolak');
        }
    }

    openBtn.addEventListener('click', () => show(true));
    modal.querySelectorAll('.closeQr').forEach((btn) => btn.addEventListener('click', () => show(false)));

    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            show(false);
        }
    });
}
