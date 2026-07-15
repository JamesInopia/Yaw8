// ═══════════════════════════════════════════
// AUTH GUARD
// Shared on every protected page. Handles:
//   1. Opening/closing the navbar user dropdown
//      (on pages that don't already load script.js).
//   2. Signing people out from the nav dropdown
//      (with a confirmation modal first).
//   3. Hiding the "My Profile" dropdown item for
//      guest sessions, since guests don't have a
//      real profile or saved library yet.
//   4. Sending "My Profile" to its page when clicked.
//      (My Games now lives inside My Profile, so
//      there's a single merged account destination.)
//   5. Sending "Settings" to its page when clicked.
// ═══════════════════════════════════════════
(function () {
    const signOutBtn = document.getElementById('signOutBtn');
    if (!signOutBtn) return;

    const inHtmlFolder = /\/html\//.test(window.location.pathname) ||
        /^(games|developers|about|gamepage|login|myprofile|mygames|settings)\.html/i.test(window.location.pathname.split('/').pop());
    const loginPath = inHtmlFolder ? 'login.html' : 'html/login.html';
    const myProfilePath = inHtmlFolder ? 'myprofile.html' : 'html/myprofile.html';
    const settingsPath = inHtmlFolder ? 'settings.html' : 'html/settings.html';

    // ─────────────────────────────────────────
    // DROPDOWN OPEN/CLOSE TOGGLE
    // Pages like myprofile.html don't load script.js, so this is
    // the only place that opens/closes the dropdown for them.
    // Guarded so pages that DO load script.js (e.g. index.html)
    // so it don't end up with the toggle wired twice.
    // ─────────────────────────────────────────
    (function wireDropdownToggle() {
        const avatarBtn = document.getElementById('avatarBtn');
        const userDropdown = document.getElementById('userDropdown');
        if (!avatarBtn || !userDropdown || window.__yaw8DropdownWired) return;
        const avatarWrap = avatarBtn.closest('.avatar-wrap');
        if (!avatarWrap) return;

        window.__yaw8DropdownWired = true;

        avatarBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            const isOpen = userDropdown.classList.contains('open');
            userDropdown.classList.toggle('open', !isOpen);
            avatarBtn.classList.toggle('open', !isOpen);
        });

        document.addEventListener('click', function (e) {
            if (!avatarWrap.contains(e.target)) {
                userDropdown.classList.remove('open');
                avatarBtn.classList.remove('open');
            }
        });

        userDropdown.addEventListener('click', function (e) {
            e.stopPropagation();
            setTimeout(() => {
                userDropdown.classList.remove('open');
                avatarBtn.classList.remove('open');
            }, 120);
        });
    })();

    // ─────────────────────────────────────────
    // GUEST-ONLY DROPDOWN FILTERING
    // ─────────────────────────────────────────
    (function hideAccountOnlyItemsForGuests() {
        let isGuest = false;
        try {
            const user = JSON.parse(localStorage.getItem('yaw8_user'));
            isGuest = !!(user && user.isGuest);
        } catch (err) {}

        if (!isGuest) return;

        const item = document.getElementById('dropdownMyProfile');
        if (item) item.style.display = 'none';
    })();

    // ─────────────────────────────────────────
    // DROPDOWN NAVIGATION — My Profile (Mygames and Myprofile are separate files from my previous code)
    // (My Games was merged into the My Profile page,
    // so there's a single account destination now.)
    // ─────────────────────────────────────────
    const dropdownMyProfile = document.getElementById('dropdownMyProfile');

    if (dropdownMyProfile) {
        dropdownMyProfile.addEventListener('click', function () {
            window.location.href = myProfilePath;
        });
    }

    // ─────────────────────────────────────────
    // DROPDOWN NAVIGATION — Settings
    // ─────────────────────────────────────────
    const dropdownSettings = document.getElementById('dropdownSettings');

    if (dropdownSettings) {
        dropdownSettings.addEventListener('click', function () {
            window.location.href = settingsPath;
        });
    }

    // ─────────────────────────────────────────
    // SIGN OUT CONFIRMATION MODAL
    // ─────────────────────────────────────────
    const confirmModal = document.createElement('div');
    confirmModal.className = 'modal';
    confirmModal.id = 'signOutConfirmModal';
    confirmModal.innerHTML = `
        <div class="modal-overlay"></div>
        <div class="modal-content" style="max-width: 380px; margin-top: 120px;">
            <div class="modal-inner" style="padding: 32px;">
                <h1 style="font-size: 19px; font-weight: 800; color: #F5F7FA; margin-bottom: 8px;">Sign out?</h1>
                <p style="font-size: 13px; color: #8892a4; line-height: 1.6; margin-bottom: 24px;">
                    You'll need to log back in to play, rate, and submit games.
                </p>
                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <button class="form-btn-cancel" id="signOutCancelBtn">Cancel</button>
                    <button class="form-btn-submit" id="signOutConfirmBtn" style="background: #FF4FD8; box-shadow: 0 0 22px rgba(255, 79, 216, 0.3);">
                        <svg viewBox="0 0 24 24" style="width:16px;height:16px;fill:currentColor;flex-shrink:0;">
                            <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                        </svg>
                        Sign Out
                    </button>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(confirmModal);

    const confirmCancelBtn  = confirmModal.querySelector('#signOutCancelBtn');
    const confirmSignOutBtn = confirmModal.querySelector('#signOutConfirmBtn');

    function openConfirmModal() {
        confirmModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeConfirmModal() {
        confirmModal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    function doSignOut() {
        try {
            localStorage.removeItem('yaw8_loggedIn');
            localStorage.removeItem('yaw8_user');
        } catch (err) {}
        window.location.href = loginPath;
    }

    signOutBtn.addEventListener('click', function (e) {
        e.preventDefault();
        openConfirmModal();
    });

    confirmCancelBtn.addEventListener('click', closeConfirmModal);
    confirmModal.querySelector('.modal-overlay').addEventListener('click', closeConfirmModal);
    confirmSignOutBtn.addEventListener('click', doSignOut);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && confirmModal.classList.contains('active')) {
            closeConfirmModal();
        }
    });
})();