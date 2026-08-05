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
    }

    initCameraModule();
    initQrModule();
});
