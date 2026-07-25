<main class="auth-main">
    <!-- ── LEFT: BRAND ── -->
    <div class="auth-brand">
        <img src="assets/images/Official_Logo.png" alt="YA!W8" class="auth-brand-logo" />
        <h2 class="auth-brand-title">Play Games Made by <span class="accent"><br>Game Changers</span></h2>
        <p class="auth-brand-sub">Discover fun, creative, and lightweight games built by students — right in your browser.</p>

        <div class="auth-features">
            <div class="auth-feature">
                <svg viewBox="0 0 24 24"><path d="M15 7.5V2H9v5.5l3 3 3-3zM7.5 9H2v6h5.5l3-3-3-3zM9 16.5V22h6v-5.5l-3-3-3 3zM16.5 9l-3 3 3 3H22V9h-5.5z"/></svg>
                <h4>Play</h4>
                <p>Try amazing student-made games.</p>
            </div>
            <div class="auth-feature">
                <svg viewBox="0 0 24 24"><path d="M12 21s-6.7-4.35-9.3-8.1C1 10.1 1.6 6.6 4.6 5.1c2.4-1.2 4.9-.3 6.4 1.5.4.5 1.2.5 1.6 0 1.5-1.8 4-2.7 6.4-1.5 3 1.5 3.6 5 1.9 7.8C18.7 16.65 12 21 12 21z"/></svg>
                <h4>Rate</h4>
                <p>Support your favorites and help them grow.</p>
            </div>
            <div class="auth-feature">
                <svg viewBox="0 0 24 24"><path d="M5 20h14v-2H5v2zM12 2 5.33 8.67h4v6.66h5.34V8.67h4L12 2z"/></svg>
                <h4>Submit</h4>
                <p>Share your game and inspire the community.</p>
            </div>
        </div>
    </div>

    <!-- ── RIGHT: FORM CARD ── -->
    <div class="auth-card">
        <div class="auth-card-inner">
            <div class="auth-heading">
                <div class="auth-heading-icon">
                    <svg viewBox="0 0 24 24"><path d="M15 7.5V2H9v5.5l3 3 3-3zM7.5 9H2v6h5.5l3-3-3-3zM9 16.5V22h6v-5.5l-3-3-3 3zM16.5 9l-3 3 3 3H22V9h-5.5z"/></svg>
                </div>
                <h1 id="authHeadingTitle">Welcome Back</h1>
                <p id="authHeadingSub">Log in to play, rate, and submit games <br> from the YA!W8 community.</p>
            </div>

            <!-- Tabs -->
            <div class="auth-tabs">
                <button class="auth-tab-btn active" data-auth-tab="login">
                    <svg viewBox="0 0 24 24"><path d="M11 7L9.6 8.4l2.6 2.6H2v2h10.2l-2.6 2.6L11 17l5-5zM20 19h-8v2h8c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-8v2h8v14z"/></svg>
                    Log In
                </button>
                <button class="auth-tab-btn" data-auth-tab="signup">
                    <svg viewBox="0 0 24 24"><path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    Sign Up
                </button>
            </div>

            <!-- ── EMPTY FORM CONTAINER ── -->
            <div class="form-container" id="formContainer">
                <!-- The PHP output will be injected right here -->
            </div>
        </div>
    </div>
</main>