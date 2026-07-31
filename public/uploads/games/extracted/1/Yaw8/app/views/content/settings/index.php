<main class="main-col">
    <!-- ─────────────────
        PAGE HEADER
    ───────────────── -->
    <section class="page-header">
    <div class="page-header-content">
        <h1>ACCOUNT <span class="accent">SETTINGS</span></h1>
        <p>Manage your profile info, password, notifications, and account preferences.</p>
    </div>
    </section>

    <!-- ─────────────────
        GUEST BANNER (shown only for guest sessions)
    ───────────────── -->
    <div class="settings-guest-banner" id="guestBanner" style="display:none;">
    <svg viewBox="0 0 24 24"><path d="M12 2 1 21h22L12 2zm1 14h-2v2h2v-2zm0-6h-2v4h2v-4z"/></svg>
    <p>You're browsing as a guest, so account info and password can't be edited. <a href="login.html">Sign up</a> to save your settings for good.</p>
    </div>

    <!-- ─────────────────
        ACCOUNT INFORMATION
    ───────────────── -->
    <section class="panel settings-panel" id="account-info">
    <div class="section-header">
        <h2 class="section-title">
        <svg viewBox="0 0 24 24"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
        Account Information
        </h2>
    </div>
    <p class="settings-panel-desc">This is how your name and username appear across YA!W8.</p>

    <form id="accountInfoForm" novalidate>
        <div style="display: flex; flex-direction: column; gap: 16px;">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
            <label class="form-label">Full Name <span class="form-required">*</span></label>
            <input type="text" class="form-input" id="settingsName" placeholder="Your full name" />
            </div>
            <div class="form-group">
            <label class="form-label">Username <span class="form-required">*</span></label>
            <input type="text" class="form-input" id="settingsUsername" placeholder="yourusername" />
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" class="form-input" id="settingsEmail" disabled />
        </div>

        <div class="settings-save-row">
            <span class="settings-save-status" id="accountInfoStatus">Saved!</span>
            <button type="submit" class="form-btn-submit" id="accountInfoSaveBtn">
            <svg viewBox="0 0 24 24" style="width:16px;height:16px;fill:currentColor;flex-shrink:0;"><path d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z"/></svg>
            Save Changes
            </button>
        </div>

        </div>
    </form>
    </section>

    <!-- ─────────────────
        PASSWORD & SECURITY
    ───────────────── -->
    <section class="panel settings-panel" id="password-security">
    <div class="section-header">
        <h2 class="section-title">
        <svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM9 6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9V6z"/></svg>
        Password &amp; Security
        </h2>
    </div>
    <p class="settings-panel-desc">Choose a strong password you're not using anywhere else.</p>

    <form id="passwordForm" novalidate>
        <div class="auth-error" id="passwordError" style="display:none; margin-bottom: 4px;"></div>
        <div style="display: flex; flex-direction: column; gap: 16px;">

        <div class="form-group">
            <label class="form-label">Current Password <span class="form-required">*</span></label>
            <input type="password" class="form-input" id="currentPassword" placeholder="Enter your current password" autocomplete="current-password" />
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
            <label class="form-label">New Password <span class="form-required">*</span></label>
            <input type="password" class="form-input" id="newPassword" placeholder="At least 8 characters" autocomplete="new-password" />
            </div>
            <div class="form-group">
            <label class="form-label">Confirm New Password <span class="form-required">*</span></label>
            <input type="password" class="form-input" id="confirmNewPassword" placeholder="Re-enter new password" autocomplete="new-password" />
            </div>
        </div>

        <div class="settings-save-row">
            <span class="settings-save-status" id="passwordStatus">Password updated!</span>
            <button type="submit" class="form-btn-submit" id="passwordSaveBtn">
            <svg viewBox="0 0 24 24" style="width:16px;height:16px;fill:currentColor;flex-shrink:0;"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-1 15l-4-4 1.41-1.41L11 13.17l6.59-6.59L19 8l-8 8z"/></svg>
            Update Password
            </button>
        </div>

        </div>
    </form>
    </section>

    <!-- ─────────────────
        DANGER ZONE
    ───────────────── -->
    <section class="panel danger-zone settings-panel" id="danger-zone">
    <div class="section-header">
        <h2 class="section-title">
        <svg viewBox="0 0 24 24"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>
        Danger Zone
        </h2>
    </div>

    <div id="dangerZoneRegistered">
        <div class="danger-row">
        <div class="danger-row-text">
            <h4>Sign out everywhere</h4>
            <p>Sign out of YA!W8 on this device. You'll need to log back in to play, rate, and submit games.</p>
        </div>
        <button class="btn-danger-outline" id="settingsSignOutBtn">Sign Out</button>
        </div>
        <div class="danger-row">
        <div class="danger-row-text">
            <h4>Delete account</h4>
            <p>Permanently deletes your account, submitted games, and profile data. This can't be undone.</p>
        </div>
        <button class="btn-danger-outline" id="deleteAccountBtn">Delete Account</button>
        </div>
    </div>

    <div id="dangerZoneGuest" style="display:none;">
        <div class="danger-row">
        <div class="danger-row-text">
            <h4>End guest session</h4>
            <p>Guest sessions aren't saved. Ending it now will sign you out and clear any temporary data.</p>
        </div>
        <button class="btn-danger-outline" id="endGuestBtn">End Session</button>
        </div>
    </div>
    </section>

</main>
<!-- ─────────────────
    SIDE COLUMN
───────────────── -->
<aside class="side-col">

    <section class="panel">
    <div class="settings-account-card">
        <div class="settings-account-avatar" id="sideAvatar">S</div>
        <div class="settings-account-info">
        <div class="settings-account-name" id="sideName">Student</div>
        <div class="settings-account-email" id="sideEmail">—</div>
        </div>
    </div>
    <nav class="settings-quicknav">
        <a href="#account-info">
        <svg viewBox="0 0 24 24"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
        Account Information
        </a>
        <a href="#password-security">
        <svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM9 6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9V6z"/></svg>
        Password &amp; Security
        </a>
        <a href="#danger-zone" class="danger-link">
        <svg viewBox="0 0 24 24"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>
        Danger Zone
        </a>
    </nav>
    </section>

</aside>