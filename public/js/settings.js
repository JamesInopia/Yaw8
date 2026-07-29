// ═══════════════════════════════════════════════════
// SETTINGS.JS
// ═══════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', () => {
    fetchUserData();
    initAccountInfoForm();
    initPasswordForm();
    initDangerZone();
});

// ═══════════════════════════════════════════
// FETCH & RENDER USER DATA
// ═══════════════════════════════════════════
async function fetchUserData() {
    try {
        // Session-based endpoint (matches how the rest of the app routes: ?url=...)
        const response = await fetch('?url=api/settings/me');

        // Handle cases where the endpoint might redirect or fail
        if (!response.ok) return;

        const result = await response.json();

        if (result.success && result.user) {
            renderAccountSummary(result.user.fullname, result.user.username, result.user.email);
        }
    } catch (error) {
        console.error('Error fetching user data:', error);
    }
}

function renderAccountSummary(fullname, username, email) {
    if (fullname) {
        const initial = fullname.trim().charAt(0).toUpperCase();
        document.getElementById('sideAvatar').textContent = initial;
        document.getElementById('sideFullname').textContent = fullname;
    }

    if (username) {
        document.getElementById('sideUsername').textContent = '@' + username;
    }

    if (email) {
        const emailInput = document.getElementById('settingsEmail');
        // Show the current email as placeholder text rather than pre-filling the field
        emailInput.placeholder = email;
        emailInput.dataset.currentEmail = email;
    }
}

// ═══════════════════════════════════════════
// ACCOUNT INFO FORM (Change Email)
// ═══════════════════════════════════════════
function initAccountInfoForm() {
    const form = document.getElementById('accountInfoForm');
    if (!form) return;

    const statusEl = document.getElementById('accountInfoStatus');
    const errorBox = document.getElementById('accountInfoError');
    const emailInput = document.getElementById('settingsEmail');

    const confirmModal = document.getElementById('confirmEmailChangeModal');
    const confirmOverlay = document.getElementById('confirmEmailChangeOverlay');
    const confirmCancelBtn = document.getElementById('confirmEmailChangeCancelBtn');
    const confirmBtn = document.getElementById('confirmEmailChangeConfirmBtn');
    const confirmAddressEl = document.getElementById('confirmEmailChangeAddress');

    function openConfirmModal(email) {
        confirmAddressEl.textContent = email;
        confirmModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeConfirmModal() {
        confirmModal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    if (confirmCancelBtn) confirmCancelBtn.addEventListener('click', closeConfirmModal);
    if (confirmOverlay) confirmOverlay.addEventListener('click', closeConfirmModal);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && confirmModal && confirmModal.classList.contains('active')) {
            closeConfirmModal();
        }
    });

    const submitBtn = document.getElementById('accountInfoSaveBtn');
    const REQUIRED_EMAIL_DOMAIN = '@iacademy.edu.ph';

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        clearError(errorBox);

        const emailVal = emailInput.value.trim();
        const passwordVal = document.getElementById('currentPasswordForEmail').value;

        // 1. Password must be entered first, before anything else happens
        if (!passwordVal) {
            showError(errorBox, 'Please enter your current password to confirm this change.');
            return;
        }

        // 2. The field is now just a placeholder-driven "new email" box — an empty
        //    field means there's nothing to change.
        if (!emailVal) {
            showError(errorBox, 'Please enter a new email address.');
            return;
        }

        // 3. Quick client-side domain check (cheap, no need to hit the server for this)
        if (!emailVal.toLowerCase().endsWith(REQUIRED_EMAIL_DOMAIN)) {
            showError(errorBox, `Email must be a valid ${REQUIRED_EMAIL_DOMAIN} address.`);
            return;
        }

        if (emailVal === emailInput.dataset.currentEmail) {
            showError(errorBox, 'That\'s already your current email address.');
            return;
        }

        // 4. Ask the server to verify the password and re-check the email
        //    (format, domain, and uniqueness) BEFORE the confirmation modal ever shows.
        submitBtn.disabled = true;
        try {
            const formData = new FormData();
            formData.append('email', emailVal);
            formData.append('currentPassword', passwordVal);

            const response = await fetch('?url=api/settings/verify-email-change', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (!result.success) {
                showError(errorBox, result.message || 'Please check your details and try again.');
                return;
            }

            // 5. Everything checks out — now, and only now, ask the user one last time
            openConfirmModal(emailVal);
        } catch (error) {
            showError(errorBox, 'An error occurred connecting to the server.');
        } finally {
            submitBtn.disabled = false;
        }
    });

    // 4. Only actually change the email once the user confirms in the modal
    confirmBtn.addEventListener('click', async function () {
        const emailVal = emailInput.value.trim();
        const passwordVal = document.getElementById('currentPasswordForEmail').value;

        confirmBtn.disabled = true;

        const formData = new FormData();
        formData.append('email', emailVal);
        formData.append('currentPassword', passwordVal);

        try {
            const response = await fetch('?url=api/settings/email', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                closeConfirmModal();
                flashStatus(statusEl, result.message || 'Email updated!', false);

                // 5. Email changed — force the user to log back in with the new address
                if (result.forceLogout) {
                    setTimeout(() => {
                        localStorage.clear();
                        window.location.href = '?url=logout';
                    }, 1200);
                }
            } else {
                closeConfirmModal();
                showError(errorBox, result.message || 'Failed to update email.');
            }
        } catch (error) {
            closeConfirmModal();
            showError(errorBox, 'An error occurred connecting to the server.');
        } finally {
            confirmBtn.disabled = false;
        }
    });
}

// ═══════════════════════════════════════════
// PASSWORD & SECURITY FORM
// ═══════════════════════════════════════════
function initPasswordForm() {
    const form = document.getElementById('passwordForm');
    if (!form) return;

    const errorBox = document.getElementById('passwordError');
    const statusBox = document.getElementById('passwordStatus');

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        clearError(errorBox);

        const current = document.getElementById('currentPassword').value;
        const next = document.getElementById('newPassword').value;
        const confirm = document.getElementById('confirmNewPassword').value;

        if (!current || !next || !confirm) {
            showError(errorBox, 'Please fill in all password fields.');
            return;
        }

        const formData = new FormData();
        formData.append('currentPassword', current);
        formData.append('newPassword', next);
        formData.append('confirmNewPassword', confirm);

        try {
            const response = await fetch('?url=api/settings/password', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                form.reset(); // Clear passwords on success
                flashStatus(statusBox, result.message, false);
            } else {
                showError(errorBox, result.message);
            }
        } catch (error) {
            showError(errorBox, 'An error occurred connecting to the server.');
        }
    });
}

// ═══════════════════════════════════════════
// DANGER ZONE
// ═══════════════════════════════════════════
function initDangerZone() {
    const deleteAccountBtn = document.getElementById('deleteAccountBtn');

    if (deleteAccountBtn) {
        deleteAccountBtn.addEventListener('click', function (e) {
            e.preventDefault();
            // Delete action is currently put on hold per instructions
            alert('Account deletion API endpoint is pending implementation.');
        });
    }
}

// ═══════════════════════════════════════════
// HELPERS
// ═══════════════════════════════════════════
// Uses the same "active" class toggle as the login page's .auth-error,
// so the pink smooth fade-in look matches across the app.
function showError(el, message) {
    if (!el) return;
    el.textContent = message;
    el.classList.add('active');
}

function clearError(el) {
    if (!el) return;
    el.classList.remove('active');
}

function flashStatus(el, message, isError) {
    if (!el) return;
    el.textContent = message;
    el.classList.toggle('error', !!isError);
    el.classList.add('show');

    // Auto-hide the status message after a couple of seconds
    setTimeout(() => el.classList.remove('show'), 2200);
}
