// ═══════════════════════════════════════════════════
// SETTINGS.JS
// Logic used only on settings.html.
// ═══════════════════════════════════════════════════

// ═══════════════════════════════════════════
// SESSION + STORAGE HELPERS
// ═══════════════════════════════════════════
function getCurrentUser() {
    try {
        const user = JSON.parse(localStorage.getItem('yaw8_user'));
        if (user && user.name) return user;
    } catch (e) {}
    return null;
}

function saveCurrentUser(user) {
    try { localStorage.setItem('yaw8_user', JSON.stringify(user)); } catch (e) {}
}

function getAccounts() {
    try { return JSON.parse(localStorage.getItem('yaw8_accounts')) || {}; } catch (e) { return {}; }
}

function saveAccounts(accounts) {
    try { localStorage.setItem('yaw8_accounts', JSON.stringify(accounts)); } catch (e) {}
}

function getSiteSettings() {
    try {
        const s = JSON.parse(localStorage.getItem('yaw8_settings'));
        if (s) return s;
    } catch (e) {}
    // Sensible defaults — most notifications on, preferences off
    return {
        notifyComments: true,
        notifyGameStatus: true,
        notifyDigest: false,
        notifyAnnouncements: true,
        prefReduceMotion: false,
        prefCompactCards: false
    };
}

function saveSiteSettings(settings) {
    try { localStorage.setItem('yaw8_settings', JSON.stringify(settings)); } catch (e) {}
}

function getInitials(name) {
    if (!name) return '?';
    const parts = name.trim().split(/\s+/);
    const first = parts[0] ? parts[0][0] : '';
    const last = parts.length > 1 ? parts[parts.length - 1][0] : '';
    return (first + last).toUpperCase();
}

function clearSession() {
    try {
        localStorage.removeItem('yaw8_loggedIn');
        localStorage.removeItem('yaw8_user');
    } catch (e) {}
}

// ═══════════════════════════════════════════
// GATE — so it's like no session at all? Then send to login.
// (Unlike My Profile, guests ARE allowed here —
// they just get a reduced, read-only account section.)
// ═══════════════════════════════════════════
const currentUser = getCurrentUser();
let activeUser = currentUser;

if (!currentUser) {
    window.location.replace('login.html?redirect=' + encodeURIComponent('settings.html'));
} else {
    renderAccountSummary(activeUser);
    initAccountInfoForm(activeUser);
    initPasswordForm(activeUser);
    initTogglePrefs();
    initDangerZone(activeUser);
    applyGuestRestrictions(activeUser);
}

// ═══════════════════════════════════════════
// ACCOUNT SUMMARY (side card + account info fields)
// ═══════════════════════════════════════════
function renderAccountSummary(user) {
    document.getElementById('sideAvatar').textContent = getInitials(user.name);
    document.getElementById('sideName').textContent = user.name;
    document.getElementById('sideEmail').textContent = user.email;

    document.getElementById('settingsName').value = user.name || '';
    document.getElementById('settingsUsername').value = user.username || '';
    document.getElementById('settingsEmail').value = user.email || '';
}

// ═══════════════════════════════════════════
// ACCOUNT INFO FORM
// ═══════════════════════════════════════════
function initAccountInfoForm(user) {
    const form = document.getElementById('accountInfoForm');
    const status = document.getElementById('accountInfoStatus');

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (activeUser.isGuest) return;

        const newName = document.getElementById('settingsName').value.trim();
        const newUsername = document.getElementById('settingsUsername').value.trim();

        if (!newName || !newUsername) return;

        const updatedUser = Object.assign({}, activeUser, {
            name: newName,
            username: newUsername
        });

        saveCurrentUser(updatedUser);

        // Keep the mock accounts store (keyed by email) in sync
        const accounts = getAccounts();
        const key = (activeUser.email || '').toLowerCase();
        if (accounts[key]) {
            accounts[key].name = newName;
            accounts[key].username = newUsername;
            saveAccounts(accounts);
        }

        activeUser = updatedUser;
        renderAccountSummary(activeUser);
        flashStatus(status, 'Saved!', false);
    });
}

// ═══════════════════════════════════════════
// PASSWORD & SECURITY FORM
// ═══════════════════════════════════════════
function initPasswordForm(user) {
    const form = document.getElementById('passwordForm');
    const status = document.getElementById('passwordStatus');
    const errorBox = document.getElementById('passwordError');

    function showError(message) {
        errorBox.textContent = message;
        errorBox.style.display = 'block';
    }

    function clearError() {
        errorBox.style.display = 'none';
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearError();
        if (activeUser.isGuest) return;

        const current = document.getElementById('currentPassword').value;
        const next = document.getElementById('newPassword').value;
        const confirm = document.getElementById('confirmNewPassword').value;

        if (!current || !next || !confirm) {
            showError('Please fill in all password fields.');
            return;
        }
        if (next.length < 8) {
            showError('Your new password must be at least 8 characters.');
            return;
        }
        if (next !== confirm) {
            showError('New passwords do not match.');
            return;
        }

        const accounts = getAccounts();
        const key = (activeUser.email || '').toLowerCase();
        const account = accounts[key];

        if (!account) {
            showError('We couldn\'t find your account. Try signing in again.');
            return;
        }
        if (account.password !== current) {
            showError('Current password is incorrect.');
            return;
        }

        account.password = next;
        saveAccounts(accounts);
        form.reset();
        flashStatus(status, 'Password updated!', false);
    });
}

// ═══════════════════════════════════════════
// NOTIFICATIONS + APPEARANCE TOGGLES
// ═══════════════════════════════════════════
function initTogglePrefs() {
    const settings = getSiteSettings();
    const toggleIds = [
        'notifyComments', 'notifyGameStatus', 'notifyDigest', 'notifyAnnouncements',
        'prefReduceMotion', 'prefCompactCards'
    ];

    toggleIds.forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.checked = !!settings[id];
        el.addEventListener('change', function () {
            const current = getSiteSettings();
            current[id] = el.checked;
            saveSiteSettings(current);
        });
    });
}

// ═══════════════════════════════════════════
// DANGER ZONE
// ═══════════════════════════════════════════
function initDangerZone(user) {

    const settingsSignOutBtn = document.getElementById('settingsSignOutBtn');
    if (settingsSignOutBtn) {
        settingsSignOutBtn.addEventListener('click', function () {
            const navSignOutBtn = document.getElementById('signOutBtn');
            if (navSignOutBtn) navSignOutBtn.click();
        });
    }

    // Guest: end session directly, no account to delete
    const endGuestBtn = document.getElementById('endGuestBtn');
    if (endGuestBtn) {
        endGuestBtn.addEventListener('click', function () {
            clearSession();
            window.location.href = 'login.html';
        });
    }

    // Registered: delete account confirmation modal
    const deleteAccountBtn = document.getElementById('deleteAccountBtn');
    const deleteModal = document.getElementById('deleteAccountModal');
    const deleteOverlay = document.getElementById('deleteAccountOverlay');
    const deleteCancelBtn = document.getElementById('deleteAccountCancelBtn');
    const deleteConfirmBtn = document.getElementById('deleteAccountConfirmBtn');

    function openDeleteModal() {
        deleteModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeDeleteModal() {
        deleteModal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    if (deleteAccountBtn) {
        deleteAccountBtn.addEventListener('click', openDeleteModal);
        deleteCancelBtn.addEventListener('click', closeDeleteModal);
        deleteOverlay.addEventListener('click', closeDeleteModal);

        deleteConfirmBtn.addEventListener('click', function () {
            const accounts = getAccounts();
            const key = (user.email || '').toLowerCase();
            delete accounts[key];
            saveAccounts(accounts);

            try {
                localStorage.removeItem('yaw8_profileMeta');
                localStorage.removeItem('yaw8_myGames');
                localStorage.removeItem('yaw8_settings');
            } catch (e) {}

            clearSession();
            window.location.href = 'login.html';
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && deleteModal.classList.contains('active')) closeDeleteModal();
        });
    }
}

// ═══════════════════════════════════════════
// GUEST RESTRICTIONS
// Guests don't have a real password or saved
// account, so those sections are hidden/disabled
// rather than shown as if they'd persist.
// ═══════════════════════════════════════════
function applyGuestRestrictions(user) {
    if (!user.isGuest) return;

    document.getElementById('guestBanner').style.display = 'flex';

    // Account info: read-only
    document.getElementById('settingsName').disabled = true;
    document.getElementById('settingsUsername').disabled = true;
    document.getElementById('accountInfoSaveBtn').disabled = true;
    document.getElementById('accountInfoSaveBtn').style.opacity = '0.5';
    document.getElementById('accountInfoSaveBtn').style.cursor = 'not-allowed';

    // Password panel: not applicable for guests
    document.getElementById('password-security').style.display = 'none';

    // Danger zone: swap to the guest-only actions
    document.getElementById('dangerZoneRegistered').style.display = 'none';
    document.getElementById('dangerZoneGuest').style.display = 'block';
}

// ═══════════════════════════════════════════
// SMALL HELPER — flash a save-status message
// ═══════════════════════════════════════════
function flashStatus(el, message, isError) {
    el.textContent = message;
    el.classList.toggle('error', !!isError);
    el.classList.add('show');
    setTimeout(() => el.classList.remove('show'), 2200);
}