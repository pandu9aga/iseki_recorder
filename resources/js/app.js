import './bootstrap';
import { initCameraModule } from './camera';
import { initQrModule } from './qr';

document.addEventListener('DOMContentLoaded', function () {
    const navToggle = document.getElementById('navToggle');
    const navMobile = document.getElementById('navMobile');

    if (navToggle && navMobile) {
        navToggle.addEventListener('click', function () {
            navMobile.classList.toggle('hidden');
        });
    }

    const tabAdmin = document.getElementById('tabAdmin');
    const tabMember = document.getElementById('tabMember');

    function setLoginTab(role) {
        const roleInput = document.getElementById('roleInput');
        const loginLabel = document.getElementById('loginLabel');
        const loginInput = document.getElementById('login');

        if (!roleInput || !loginLabel || !loginInput || !tabAdmin || !tabMember) {
            return;
        }

        roleInput.value = role;
        const isAdmin = role === 'admin';

        tabAdmin.className = 'tab-btn rounded-lg py-2 text-sm font-semibold ' + (isAdmin ? 'bg-pink-600 text-white' : 'text-pink-600');
        tabMember.className = 'tab-btn rounded-lg py-2 text-sm font-semibold ' + (!isAdmin ? 'bg-pink-600 text-white' : 'text-pink-600');
        loginLabel.textContent = isAdmin ? 'Username' : 'NIK';
        loginInput.placeholder = isAdmin ? 'Masukkan username' : 'Masukkan NIK';
    }

    if (tabAdmin && tabMember) {
        tabAdmin.addEventListener('click', () => setLoginTab('admin'));
        tabMember.addEventListener('click', () => setLoginTab('member'));
    }

    const openCreate = document.getElementById('openCreateFolder');
    const closeCreate = document.getElementById('closeCreateFolder');
    const createModal = document.getElementById('createFolderModal');

    if (openCreate && closeCreate && createModal) {
        openCreate.addEventListener('click', () => {
            createModal.classList.remove('hidden');
            createModal.classList.add('flex');
        });
        closeCreate.addEventListener('click', () => {
            createModal.classList.add('hidden');
            createModal.classList.remove('flex');
        });
        createModal.addEventListener('click', (e) => {
            if (e.target === createModal) {
                createModal.classList.add('hidden');
                createModal.classList.remove('flex');
            }
        });
    }

    const selectAll = document.getElementById('selectAllPhotos');
    const photoChecks = document.querySelectorAll('.photo-check');

    if (selectAll && photoChecks.length) {
        selectAll.addEventListener('change', function () {
            photoChecks.forEach((cb) => {
                cb.checked = selectAll.checked;
            });
        });

        photoChecks.forEach((cb) => {
            cb.addEventListener('change', function () {
                selectAll.checked = photoChecks.length > 0 && [...photoChecks].every((c) => c.checked);
            });
        });
    }

    const downloadZipBtn = document.getElementById('downloadZipBtn');
    const zipForm = document.getElementById('zipForm');
    const zipPhotoInputs = document.getElementById('zipPhotoInputs');

    if (downloadZipBtn && zipForm && zipPhotoInputs) {
        downloadZipBtn.addEventListener('click', function () {
            const ids = [...document.querySelectorAll('.photo-check:checked')].map((cb) => cb.value);
            if (!ids.length) {
                alert('Pilih minimal satu foto.');
                return;
            }
            zipPhotoInputs.innerHTML = '';
            ids.forEach((id) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'photo_ids[]';
                input.value = id;
                zipPhotoInputs.appendChild(input);
            });
            zipForm.submit();
        });
    }

    initCameraModule();
    initQrModule();
});
