// ═══════════════════════════════════════════
// GLOBAL HELPERS
// ═══════════════════════════════════════════
function appUrl(path) {
  return "?url=" + path;
}

function authHeaders() {
  const token = localStorage.getItem("jwt_token");
  return {
    Authorization: "Bearer " + token,
    "Content-Type": "application/json",
    Accept: "application/json",
  };
}

// ═══════════════════════════════════════════
// LOADING SCREEN
// ═══════════════════════════════════════════
(function () {
  const loadingScreen = document.getElementById("loading-screen");
  const loaderBarFill = document.getElementById("loaderBarFill");
  const loaderText = document.getElementById("loaderText");
  if (!loadingScreen) return;

  let progress = 0;
  const interval = setInterval(function () {
    progress += Math.random() * 25;
    if (progress > 90) progress = 90;
    if (loaderBarFill) loaderBarFill.style.width = progress + "%";
    if (loaderText) loaderText.textContent = `Loading… ${Math.floor(progress)}`;
  }, 120);

  window.addEventListener("load", function () {
    clearInterval(interval);
    if (loaderBarFill) loaderBarFill.style.width = "100%";
    if (loaderText) loaderText.textContent = "Loading…";
    setTimeout(() => loadingScreen.classList.add("loaded"), 300);
  });
})();

// ═══════════════════════════════════════════
// MAIN AUTH FUNCTIONALITY
// ═══════════════════════════════════════════
document.addEventListener("DOMContentLoaded", () => {
  // ── DOM ELEMENTS ──
  const formContainer = document.getElementById("formContainer");
  const tabs = document.querySelectorAll("[data-auth-tab]");
  const headingTitle = document.getElementById("authHeadingTitle");
  const headingSub = document.getElementById("authHeadingSub");

  // auth.js now loads on every page (it defines the global authHeaders()/
  // appUrl() helpers), but the form-loading logic below only applies to
  // the auth page itself, where #formContainer actually exists.
  if (!formContainer) return;

  // ── CORE FUNCTION: LOAD FORM VIA FETCH ──
  function loadForm(formType) {
    formContainer.style.opacity = "0.5";
    formContainer.style.transition = "opacity 0.15s ease-in-out";

    fetch(`?url=auth/form&type=${formType}`)
      .then((response) => {
        if (!response.ok) throw new Error("Network response was not ok");
        return response.text();
      })
      .then((html) => {
        formContainer.innerHTML = html;
        formContainer.style.opacity = "1";

        if (formType === "login") {
          if (headingTitle) headingTitle.textContent = "Welcome Back";
          if (headingSub)
            headingSub.innerHTML =
              "Log in to play, rate, and submit games <br> from the YA!W8 community.";
        } else if (formType === "signup") {
          if (headingTitle) headingTitle.textContent = "Join YA!W8";
          if (headingSub)
            headingSub.innerHTML =
              "Create an account to start playing and <br> sharing your own games.";
        }
      })
      .catch((error) => {
        console.error("Error loading form:", error);
        formContainer.innerHTML =
          '<div style="color: red; padding: 1rem;">Error loading form.</div>';
        formContainer.style.opacity = "1";
      });
  }

  // ── INITIAL LOAD: DEFAULT TO LOGIN ──
  tabs.forEach((t) => t.classList.remove("active"));
  const defaultLoginTab = document.querySelector('[data-auth-tab="login"]');
  if (defaultLoginTab) defaultLoginTab.classList.add("active");
  loadForm("login");

  // ── EVENT LISTENERS: TOP TABS ──
  tabs.forEach((tab) => {
    tab.addEventListener("click", (e) => {
      const button = e.target.closest("button");
      if (!button) return;

      const targetType = button.getAttribute("data-auth-tab");
      tabs.forEach((t) => t.classList.remove("active"));
      button.classList.add("active");
      loadForm(targetType);
    });
  });

  // ── EVENT DELEGATION: DYNAMIC LINKS & PASSWORD TOGGLES ──
  formContainer.addEventListener("click", (e) => {
    // Handle Switch links (e.g., "Already have an account? Log In")
    const switchBtn = e.target.closest("[data-auth-switch]");
    if (switchBtn) {
      const targetType = switchBtn.getAttribute("data-auth-switch");
      tabs.forEach((t) => t.classList.remove("active"));
      const matchingTab = document.querySelector(
        `[data-auth-tab="${targetType}"]`,
      );
      if (matchingTab) matchingTab.classList.add("active");
      loadForm(targetType);
    }

    // Handle Password Visibility Toggles
    const pwToggle = e.target.closest(".pw-toggle-btn");
    if (pwToggle) {
      const wrap = pwToggle.closest(".form-input-wrap");
      const input = wrap.querySelector("input");
      const showing = input.type === "text";
      input.type = showing ? "password" : "text";
      pwToggle.classList.toggle("showing", !showing);
    }
  });

  // ── EVENT DELEGATION: FORM SUBMISSIONS ──
  document.addEventListener("submit", function (e) {
    const form = e.target.closest("form");
    if (!form) return;

    // Prevent standard page refresh
    e.preventDefault();

    // ───────────────────────────────────────
    // LOGIN LOGIC
    // ───────────────────────────────────────
    if (
      form.id === "loginForm" ||
      form.getAttribute("data-auth-form") === "login"
    ) {
      const errorAlert = form.querySelector(".auth-error");
      if (errorAlert) errorAlert.classList.remove("active");

      const emailInput = document.getElementById("loginEmail")?.value || "";
      const passwordInput =
        document.getElementById("loginPassword")?.value || "";

      fetch(appUrl("login"), {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({ email: emailInput, password: passwordInput }),
      })
        .then(async (response) => {
          const text = await response.text();
          try {
            return { status: response.status, body: JSON.parse(text) };
          } catch (err) {
            console.error("Server output was not valid JSON:\n", text);
            throw new Error("Server returned non-JSON response.");
          }
        })
        .then((res) => {
          if ((res.status === 200 || res.status === 201) && res.body.success) {
            if (res.body.token) {
              localStorage.setItem("jwt_token", res.body.token);
            }

            // Save user data for profile page and storage sync
            const newUserData = {
              id: res.body.user?.id,
              name: res.body.user?.name || payload.fullname,
              username: res.body.user?.username || payload.username,
              email: res.body.user?.email || payload.email,
              isGuest: false,
            };
            localStorage.setItem("yaw8_user", JSON.stringify(newUserData));
            localStorage.setItem("yaw8_account", JSON.stringify(newUserData));
            sessionStorage.setItem("show_auth_loader", "true");
            window.location.href = appUrl("home");
          } else if (errorAlert) {
            errorAlert.textContent = res.body.message || "Login failed";
            errorAlert.classList.add("active");
          }
        })
        .catch((err) => {
          console.error("AJAX Error: ", err);
          if (errorAlert) {
            errorAlert.textContent = "An unexpected error occurred.";
            errorAlert.classList.add("active");
          }
        });
    }

    // ───────────────────────────────────────
    // SIGNUP LOGIC
    // ───────────────────────────────────────
    if (
      form.id === "signupForm" ||
      form.getAttribute("data-auth-form") === "signup"
    ) {
      const errorAlert = form.querySelector(".auth-error");
      if (errorAlert) errorAlert.classList.remove("active");

      const termsCheckbox = form.querySelector('input[type="checkbox"]');

      const payload = {
        fullname: document.getElementById("signupName")?.value || "",
        username: document.getElementById("signupUsername")?.value || "",
        email: document.getElementById("signupEmail")?.value || "",
        password: document.getElementById("signupPassword")?.value || "",
        confirmPassword:
          document.getElementById("signupConfirmPassword")?.value || "",
        agreeTerms: termsCheckbox ? termsCheckbox.checked : false,
      };

      fetch(appUrl("signup"), {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify(payload),
      })
        .then(async (response) => {
          const text = await response.text();
          try {
            return { status: response.status, body: JSON.parse(text) };
          } catch (err) {
            console.error("Server output was not valid JSON:\n", text);
            throw new Error("Server returned non-JSON response.");
          }
        })
        .then((res) => {
          if ((res.status === 201 || res.status === 200) && res.body.success) {
            if (res.body.token) {
              localStorage.setItem("jwt_token", res.body.token);
            }

            // Save registered user data for profile page and storage sync
            const newUserData = {
              name: payload.fullname,
              username: payload.username,
              email: payload.email,
              isGuest: false,
            };
            localStorage.setItem("yaw8_user", JSON.stringify(newUserData));
            localStorage.setItem("yaw8_account", JSON.stringify(newUserData));

            // Flag loading screen to display on target page
            sessionStorage.setItem("show_auth_loader", "true");

            window.location.href = appUrl("home");
          } else if (errorAlert) {
            errorAlert.textContent = res.body.message || "Registration failed";
            errorAlert.classList.add("active");
          }
        })
        .catch((err) => {
          console.error("AJAX Error: ", err);
          if (errorAlert) {
            errorAlert.textContent = "An unexpected error occurred.";
            errorAlert.classList.add("active");
          }
        });
    }
  });
});