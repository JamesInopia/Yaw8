// ═══════════════════════════════════════════
// (Asked AI regarding the flow of this)
// ═══════════════════════════════════════════
// BACKEND TODO (1/4): Replace this whole object with calls to your
// API, e.g.:
//   findByEmail(email)   → GET  /api/users?email=...
//   usernameTaken(name)  → GET  /api/users?username=...
//   create({...})        → POST /api/signup   (server hashes password)
// Everywhere below that calls YAW8Accounts.findByEmail/create/etc.
// should become an `await fetch(...)` call instead. Keep the same
// function names if possible so the rest of the file barely changes.
const YAW8Accounts = {
    _read() {
        try {
            return JSON.parse(localStorage.getItem('yaw8_accounts')) || {};
        } catch (e) {
            return {};
        }
    },
    _write(accounts) {
        try { localStorage.setItem('yaw8_accounts', JSON.stringify(accounts)); } catch (e) {}
    },
    findByEmail(email) {
        const accounts = this._read();
        return accounts[email.toLowerCase()] || null;
    },
    usernameTaken(username) {
        const accounts = this._read();
        return Object.values(accounts).some(
            acc => acc.username.toLowerCase() === username.toLowerCase()
        );
    },
    create({ name, username, email, password }) {
        const accounts = this._read();
        accounts[email.toLowerCase()] = { name, username, email, password };
        this._write(accounts);
    }
};

// ═══════════════════════════════════════════
// DEV/TEST SEED ACCOUNT
// Just for testing
// ═══════════════════════════════════════════
(function seedTestAccount() {
    if (!YAW8Accounts.findByEmail('test@iacademy.edu.ph')) {
        YAW8Accounts.create({
            name: 'Test Student',
            username: 'teststudent',
            email: 'test@iacademy.edu.ph',
            password: '000000'
        });
    }
})();

// BACKEND TODO (2/4): Right now "being logged in" just means these
// two localStorage keys are set. When the backend exists, this
// should instead store whatever the server gives back after a
// successful login/signup — e.g. a session cookie (often handled
// automatically by the browser) or a token (e.g. JWT) that you
// save here and attach to future API requests. The gate-check
// scripts at the top of every page's <head> would then check for
// that session instead of `yaw8_loggedIn`.
function logInAsUser(user) {
    try {
        localStorage.setItem('yaw8_loggedIn', 'true');
        localStorage.setItem('yaw8_user', JSON.stringify({
            name: user.name,
            username: user.username,
            email: user.email,
            isGuest: !!user.isGuest
        }));
    } catch (err) {}
}

// ═══════════════════════════════════════════
// ALREADY LOGGED IN? Skip the gate.
// ═══════════════════════════════════════════
(function () {
    let loggedIn = false;
    try { loggedIn = localStorage.getItem('yaw8_loggedIn') === 'true'; } catch (e) {}
    if (!loggedIn) return;

    const params = new URLSearchParams(window.location.search);
    const redirect = params.get('redirect');
    window.location.replace(redirect ? decodeURIComponent(redirect) : '../index.html');
})();

// ═══════════════════════════════════════════
// LOADING SCREEN
// ═══════════════════════════════════════════
(function () {
    const loadingScreen = document.getElementById('loading-screen');
    const loaderBarFill  = document.getElementById('loaderBarFill');
    const loaderText     = document.getElementById('loaderText');
    if (!loadingScreen) return;

    let progress = 0;
    const interval = setInterval(function () {
        progress += Math.random() * 25;
        if (progress > 90) progress = 90;
        if (loaderBarFill) loaderBarFill.style.width = progress + '%';
        if (loaderText) loaderText.textContent = `Loading… ${Math.floor(progress)}%`;
    }, 120);

    window.addEventListener('load', function () {
        clearInterval(interval);
        if (loaderBarFill) loaderBarFill.style.width = '100%';
        if (loaderText) loaderText.textContent = 'Loading…';
        setTimeout(function () {
            loadingScreen.classList.add('loaded');
        }, 300);
    });
})();

// ═══════════════════════════════════════════
// AVATAR / USER DROPDOWN (nav)
// ═══════════════════════════════════════════
(function () {
    const avatarBtn    = document.getElementById('avatarBtn');
    const userDropdown = document.getElementById('userDropdown');
    if (!avatarBtn || !userDropdown) return;

    const avatarWrap = avatarBtn.closest('.avatar-wrap');

    avatarBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        const isOpen = userDropdown.classList.contains('open');
        userDropdown.classList.toggle('open', !isOpen);
        avatarBtn.classList.toggle('open', !isOpen);
    });

    document.addEventListener('click', function (e) {
        if (avatarWrap && !avatarWrap.contains(e.target)) {
            userDropdown.classList.remove('open');
            avatarBtn.classList.remove('open');
        }
    });
})();

// ═══════════════════════════════════════════
// AUTH TABS (Log In / Sign Up)
// ═══════════════════════════════════════════
(function () {
    const tabBtns    = document.querySelectorAll('.auth-tab-btn');
    const forms      = document.querySelectorAll('.auth-form');
    const switchers  = document.querySelectorAll('[data-auth-switch]');
    const headingTitle = document.getElementById('authHeadingTitle');
    const headingSub   = document.getElementById('authHeadingSub');

    const copy = {
        login:  { title: 'Welcome Back', sub: 'Log in to play, rate, and submit games<br>from the YA!W8 community.' },
        signup: { title: 'Create Your Account', sub: 'Join YA!W8 to play, rate, and share games<br>made by students like you.' }
    };

    function setActiveTab(target) {
        tabBtns.forEach(btn => btn.classList.toggle('active', btn.dataset.authTab === target));
        forms.forEach(form => form.classList.toggle('active', form.dataset.authForm === target));

        if (headingTitle && headingSub && copy[target]) {
            headingTitle.textContent = copy[target].title;
            headingSub.innerHTML = copy[target].sub;
        }

        // Reset inline errors when switching
        document.querySelectorAll('.auth-error').forEach(el => el.classList.remove('active'));

        // Keep the URL hash in sync so the tab is linkable/bookmarkable
        history.replaceState(null, '', target === 'signup' ? '#signup' : '#login');
    }

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => setActiveTab(btn.dataset.authTab));
    });

    switchers.forEach(el => {
        el.addEventListener('click', () => setActiveTab(el.dataset.authSwitch));
    });

    // Open the correct tab based on URL hash (e.g. login.html#signup)
    const initial = window.location.hash === '#signup' ? 'signup' : 'login';
    setActiveTab(initial);
})();

// ═══════════════════════════════════════════
// CONTINUE AS GUEST
// Creates a temporary, unsaved guest identity so
// visitors can browse/play without creating a real
// account. Nothing here is written to YAW8Accounts,
// so a guest session doesn't persist as a real user.
// ═══════════════════════════════════════════
(function () {
    const guestBtns = document.querySelectorAll('[data-guest-btn]');
    if (!guestBtns.length) return;

    guestBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const guestId = Math.floor(1000 + Math.random() * 9000);
            const guest = {
                name: `Guest ${guestId}`,
                username: `guest${guestId}`,
                email: `guest${guestId}@yaw8.local`,
                isGuest: true
            };

            logInAsUser(guest);

            const params = new URLSearchParams(window.location.search);
            const redirect = params.get('redirect');
            window.location.href = redirect ? decodeURIComponent(redirect) : '../index.html';
        });
    });
})();

// ═══════════════════════════════════════════
// PASSWORD VISIBILITY TOGGLE
// ═══════════════════════════════════════════
(function () {
    document.querySelectorAll('.pw-toggle-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const wrap = btn.closest('.form-input-wrap');
            const input = wrap ? wrap.querySelector('input') : null;
            if (!input) return;

            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            btn.classList.toggle('showing', !showing);
        });
    });
})();

// ═══════════════════════════════════════════
// FORM SUBMISSION (front-end only — swap in real auth calls)
// ═══════════════════════════════════════════
(function () {
    const loginForm  = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');

    function redirectAfterAuth() {
        const params = new URLSearchParams(window.location.search);
        const redirect = params.get('redirect');
        window.location.href = redirect ? decodeURIComponent(redirect) : '../index.html';
    }

    function showError(form, message) {
        const errorEl = form.querySelector('.auth-error');
        if (!errorEl) return;
        errorEl.textContent = message;
        errorEl.classList.add('active');
    }

    function clearError(form) {
        const errorEl = form.querySelector('.auth-error');
        if (errorEl) errorEl.classList.remove('active');
    }

    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            e.preventDefault();
            clearError(loginForm);

            const email    = document.getElementById('loginEmail').value.trim();
            const password = document.getElementById('loginPassword').value;

            if (!email || !password) {
                showError(loginForm, 'Please fill in both your email and password.');
                return;
            }

            // ─────────────────────────────────────────────────────
            // BACKEND TODO (3/4): Replace the block below with a
            // real request, e.g.:
            //
            //   fetch('/api/login', {
            //     method: 'POST',
            //     headers: { 'Content-Type': 'application/json' },
            //     body: JSON.stringify({ email, password })
            //   })
            //   .then(res => {
            //     if (!res.ok) throw new Error('Invalid email or password.');
            //     return res.json();
            //   })
            //   .then(user => { logInAsUser(user); redirectAfterAuth(); })
            //   .catch(err => showError(loginForm, err.message));
            //
            // Everything from here down to redirectAfterAuth() is the
            // local-storage stand-in and can be deleted once the
            // fetch() call above is in place.
            const account = YAW8Accounts.findByEmail(email);
            if (!account) {
                showError(loginForm, 'No account found with that email. Try signing up instead.');
                return;
            }
            if (account.password !== password) {
                showError(loginForm, 'Incorrect password. Please try again.');
                return;
            }

            logInAsUser(account);
            redirectAfterAuth();
            // ─────────────────────────────────────────────────────
        });
    }

    if (signupForm) {
        signupForm.addEventListener('submit', function (e) {
            e.preventDefault();
            clearError(signupForm);

            const name     = document.getElementById('signupName').value.trim();
            const username = document.getElementById('signupUsername').value.trim();
            const email    = document.getElementById('signupEmail').value.trim();
            const password = document.getElementById('signupPassword').value;
            const confirm  = document.getElementById('signupConfirmPassword').value;
            const agreed   = document.getElementById('signupTerms').checked;

            if (!name || !username || !email || !password || !confirm) {
                showError(signupForm, 'Please fill in all required fields.');
                return;
            }
            if (password.length < 8) {
                showError(signupForm, 'Your password must be at least 8 characters.');
                return;
            }
            if (password !== confirm) {
                showError(signupForm, 'Passwords do not match.');
                return;
            }
            if (!agreed) {
                showError(signupForm, 'Please agree to the Terms of Service and Privacy Policy.');
                return;
            }

            // ─────────────────────────────────────────────────────
            // BACKEND TODO (4/4): Replace the block below with a
            // real request, e.g.:
            //
            //   fetch('/api/signup', {
            //     method: 'POST',
            //     headers: { 'Content-Type': 'application/json' },
            //     body: JSON.stringify({ name, username, email, password })
            //   })
            //   .then(res => {
            //     if (res.status === 409) throw new Error('Email or username already taken.');
            //     if (!res.ok) throw new Error('Something went wrong. Please try again.');
            //     return res.json();
            //   })
            //   .then(user => { logInAsUser(user); redirectAfterAuth(); })
            //   .catch(err => showError(signupForm, err.message));
            //
            // The server should hash the password before storing it
            // (e.g. bcrypt) and be the one enforcing "email/username
            // must be unique" — the checks below are just a stand-in
            // for that until the backend exists.
            if (YAW8Accounts.findByEmail(email)) {
                showError(signupForm, 'An account with that email already exists. Try logging in instead.');
                return;
            }
            if (YAW8Accounts.usernameTaken(username)) {
                showError(signupForm, 'That username is already taken. Please choose another.');
                return;
            }

            const account = { name, username, email, password };
            YAW8Accounts.create(account);
            logInAsUser(account);
            redirectAfterAuth();
            // ─────────────────────────────────────────────────────
        });
    }
})();