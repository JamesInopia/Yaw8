<form class="auth-form" id="forgotEmailForm" data-auth-form="forgot-email" novalidate>
    <div class="auth-error"></div>
    <p class="auth-step-text">Enter the email on your account and we'll send you a 6-digit code to reset your password.</p>

    <div class="form-group">
        <label class="form-label">Email Address <span class="form-required">*</span></label>
        <div class="form-input-wrap form-input-icon-left">
            <svg class="input-icon" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/></svg>
            <input type="email" class="form-input" placeholder="yourstudentid@iacademy.edu.ph" id="forgotEmail" autocomplete="email" />
        </div>
    </div>

    <button type="submit" class="form-btn-submit auth-submit-btn">Send Code</button>

    <p class="auth-switch-text">
        Remembered your password?
        <button type="button" data-auth-switch="login">Log In</button>
    </p>
</form>
