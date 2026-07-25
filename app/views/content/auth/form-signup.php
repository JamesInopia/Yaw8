<form class="auth-form" id="signupForm" data-auth-form="signup" novalidate>
    <div class="auth-error"></div>

    <div class="auth-row">
        <div class="form-group">
            <label class="form-label">Full Name <span class="form-required">*</span></label>
            <input type="text" class="form-input" placeholder="James" id="signupName" autocomplete="name" />
        </div>
        <div class="form-group">
            <label class="form-label">Username <span class="form-required">*</span></label>
            <input type="text" class="form-input" placeholder="iforgotmyname" id="signupUsername" autocomplete="username" />
        </div>
    </div>

    <div class="form-group">
        <label class="form-label">Email Address <span class="form-required">*</span></label>
        <div class="form-input-wrap form-input-icon-left">
            <svg class="input-icon" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/></svg>
            <input type="email" class="form-input" placeholder="yourstudentid@iacademy.edu.ph" id="signupEmail" autocomplete="email" />
        </div>
    </div>

    <div class="auth-row">
        <div class="form-group">
            <label class="form-label">Password <span class="form-required">*</span></label>
            <div class="form-input-wrap form-input-icon-left">
                <svg class="input-icon" viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM9 6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9V6z"/></svg>
                <input type="password" class="form-input" placeholder="Enter password" id="signupPassword" autocomplete="new-password" />
                <button type="button" class="pw-toggle-btn" title="Show password" aria-label="Show password">
                    <svg class="icon-eye" viewBox="0 0 24 24"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg class="icon-eye-off" viewBox="0 0 24 24"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a18.6 18.6 0 0 1 5.06-5.94M9.9 4.24A10.94 10.94 0 0 1 12 4c7 0 11 7 11 7a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Confirm <span class="form-required">*</span></label>
            <div class="form-input-wrap form-input-icon-left">
                <svg class="input-icon" viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM9 6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9V6z"/></svg>
                <input type="password" class="form-input" placeholder="Confirm password" id="signupConfirmPassword" autocomplete="new-password" />
                <button type="button" class="pw-toggle-btn" title="Show password" aria-label="Show password">
                    <svg class="icon-eye" viewBox="0 0 24 24"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg class="icon-eye-off" viewBox="0 0 24 24"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a18.6 18.6 0 0 1 5.06-5.94M9.9 4.24A10.94 10.94 0 0 1 12 4c7 0 11 7 11 7a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
            </div>
        </div>
    </div>

    <label class="auth-checkbox-label auth-terms-label">
        <input type="checkbox" id="signupTerms" />
        <span>I agree to the <a href="../about/about.html">Terms of Service</a> and <a href="../about/about.html">Privacy Policy</a></span>
    </label>

    <button type="submit" class="form-btn-submit auth-submit-btn">
        Create Account
    </button>

    <p class="auth-switch-text">
        Already have an account?
        <button type="button" data-auth-switch="login">Log In</button>
    </p>
</form>