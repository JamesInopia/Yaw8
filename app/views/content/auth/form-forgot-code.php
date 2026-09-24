<form class="auth-form" id="forgotCodeForm" data-auth-form="forgot-code" novalidate>
    <div class="auth-error"></div>
    <p class="auth-step-text">We sent a 6-digit code to <strong id="forgotCodeEmailDisplay"></strong>. Enter it below.</p>

    <div class="form-group">
        <label class="form-label">Verification Code <span class="form-required">*</span></label>
        <div class="otp-row">
            <input type="text" class="otp-box" inputmode="numeric" maxlength="1" autocomplete="one-time-code" />
            <input type="text" class="otp-box" inputmode="numeric" maxlength="1" />
            <input type="text" class="otp-box" inputmode="numeric" maxlength="1" />
            <input type="text" class="otp-box" inputmode="numeric" maxlength="1" />
            <input type="text" class="otp-box" inputmode="numeric" maxlength="1" />
            <input type="text" class="otp-box" inputmode="numeric" maxlength="1" />
        </div>
    </div>

    <button type="submit" class="form-btn-submit auth-submit-btn">Verify Code</button>

    <p class="auth-switch-text">
        Didn't get a code?
        <button type="button" id="resendCodeBtn">Resend</button>
    </p>
</form>
