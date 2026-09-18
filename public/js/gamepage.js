// ═══════════════════════════════════════════
// GAME PAGE — real backend version
// ═══════════════════════════════════════════
(function () {
  const cabinet = document.getElementById("gpCabinet");
  if (!cabinet) return;

  const gameId = cabinet.dataset.gameId;
  const playableType = cabinet.dataset.playableType;
  let playableUrl = cabinet.dataset.playableUrl || "";

  function isLoggedIn() {
    return Boolean(localStorage.getItem("jwt_token"));
  }

  // ── Lightweight toast (used for "log in to rate" nudges) ──
  function showToast(text) {
    let toast = document.getElementById("gpToast");
    if (!toast) {
      toast = document.createElement("div");
      toast.id = "gpToast";
      toast.className = "gp-toast";
      document.body.appendChild(toast);
    }
    toast.textContent = text;
    toast.classList.add("show");
    clearTimeout(showToast._t);
    showToast._t = setTimeout(() => toast.classList.remove("show"), 2200);
  }

  // ═══════════════════════════════════════════
  // PLAYER CONTROLS + RESOLUTION FITTING
  // ═══════════════════════════════════════════
  const screenEl = document.getElementById("gpScreen");
  const scrollEl = document.getElementById("gpScroll");
  const viewport = document.getElementById("gpViewport");
  const stage = document.getElementById("gpStage");
  const startCard = document.getElementById("gpStartCard");
  const playBtn = document.getElementById("gpPlayBtn");
  const frame = document.getElementById("gpFrame");
  const restartBtn = document.getElementById("gpRestartBtn");
  const muteBtn = document.getElementById("gpMuteBtn");
  const fullscreenBtn = document.getElementById("gpFullscreenBtn");
  const playsEl = document.getElementById("gpPlays");

  // Logical width the iframe always renders at. The game sees this as its
  // viewport width, so it lays out exactly once and never reflows.
  const NATIVE_W = Number(cabinet.dataset.nativeWidth) || 1280;
  // Starting height guess — replaced by the game's real content height as
  // soon as it has loaded (see measureContent()).
  const START_H = Number(cabinet.dataset.nativeHeight) || 720;
  // Chrome/vertical room reserved for the navbar + toolbar, windowed only.
  const CHROME_H = 220;
  const MIN_SCREEN_H = 320;

  let contentH = START_H;

  function isFullscreen() {
    return Boolean(document.fullscreenElement || document.webkitFullscreenElement);
  }

  // ── Lay the stage out for the current mode ──────────────────────────────
  //
  // Fullscreen  → CONTAIN fit: scale = min(fitW, fitH), so the whole page is
  //               visible at once. Nothing is cropped, nothing scrolls, the
  //               leftover space is just letterboxing.
  //
  // Windowed    → fit the WIDTH (never upscale past 1:1). If the game is then
  //               taller than the box, the box scrolls. That's the honest
  //               answer for a page-shaped game like 2048 — better a scrollbar
  //               than shrinking it to a postage stamp.
  function fitStage() {
    if (!screenEl || !stage || !viewport || !scrollEl) return;

    const full = isFullscreen();
    let scale, screenH;

    if (full) {
      const availW = window.innerWidth;
      const availH = window.innerHeight;

      scale = Math.min(availW / NATIVE_W, availH / contentH);
      screenH = availH;
      scrollEl.classList.remove("gp-scrollable");
    } else {
      const availW = screenEl.clientWidth || cabinet.clientWidth || NATIVE_W;
      const maxH = Math.max(MIN_SCREEN_H, window.innerHeight - CHROME_H);

      // Fit width, but don't blow a small game up past its native size.
      scale = Math.min(1, availW / NATIVE_W);

      const scaledH = contentH * scale;
      screenH = Math.min(scaledH, maxH);

      // Taller than the box? Then scroll instead of shrinking further.
      scrollEl.classList.toggle("gp-scrollable", scaledH > screenH + 1);
    }

    screenEl.style.setProperty("--gp-scale", String(scale));
    screenEl.style.setProperty("--gp-native-w", NATIVE_W + "px");
    screenEl.style.setProperty("--gp-native-h", contentH + "px");
    screenEl.style.setProperty("--gp-vw", NATIVE_W * scale + "px");
    screenEl.style.setProperty("--gp-vh", contentH * scale + "px");
    screenEl.style.setProperty("--gp-screen-h", Math.round(screenH) + "px");
  }

  // ── Measure the game's real content height ──────────────────────────────
  // Games are served from our own /public/uploads, so the iframe is
  // same-origin and we can read its document. If that ever fails (a game
  // pointing at an external URL) we silently keep the fallback height.
  function measureContent() {
    if (!frame) return;

    try {
      const doc = frame.contentDocument;
      if (!doc || !doc.body) return;

      const measured = Math.max(
        doc.documentElement.scrollHeight,
        doc.body.scrollHeight,
        doc.documentElement.offsetHeight,
        START_H
      );

      // Clamp so one badly-built game can't produce a 40,000px stage.
      const next = Math.min(measured, 6000);

      if (Math.abs(next - contentH) > 4) {
        contentH = next;
        fitStage();
      }
    } catch (err) {
      /* cross-origin game — keep the fallback height */
    }
  }

  function remeasureSoon() {
    measureContent();
    // Web fonts, images and late scripts can change the page height a beat
    // after load, so take a few more readings.
    [100, 400, 1200].forEach((ms) => setTimeout(measureContent, ms));
  }

  if (screenEl && stage) {
    fitStage();

    window.addEventListener("resize", fitStage);
    window.addEventListener("orientationchange", fitStage);
    window.addEventListener("load", fitStage);

    if (typeof ResizeObserver !== "undefined") {
      new ResizeObserver(fitStage).observe(screenEl);
    }

    setTimeout(fitStage, 150);
  }

  let hasCountedPlay = false;

  function loadGame() {
    if (!playableUrl) return;

    frame.src = playableUrl;
    frame.addEventListener(
      "load",
      function () {
        remeasureSoon();

        // Some games grow/shrink as you play (score panels, game-over
        // overlays). Watch the document and re-fit when it changes.
        try {
          const doc = frame.contentDocument;
          if (doc && doc.body && typeof ResizeObserver !== "undefined") {
            new ResizeObserver(measureContent).observe(doc.body);
          }
        } catch (err) {
          /* cross-origin — nothing to observe */
        }
      },
      { once: true }
    );
  }

  // Tells the server a play just started (increments totalPlays).
  function registerPlay() {
    if (hasCountedPlay || !gameId || !isLoggedIn()) return;
    hasCountedPlay = true;

    fetch(`?url=games/play&gameId=${gameId}`, {
      method: "POST",
      headers: authHeaders(),
    })
      .then((res) => (res.ok ? res.json() : null))
      .then((data) => {
        if (!data) return;
        if (typeof data.totalPlays !== "undefined" && playsEl) {
          playsEl.textContent = `${data.totalPlays} plays`;
        }
        if (data.playable && data.playable.url) {
          playableUrl = data.playable.url;
        }
      })
      .catch((err) => console.error("Failed to register play:", err));
  }

  function startGame() {
    loadGame();
    registerPlay();
    startCard.classList.add("hidden");
    screenEl.classList.add("playing");
    fitStage();
  }

  if (playBtn) {
    playBtn.addEventListener("click", startGame);
  }

  if (restartBtn) {
    restartBtn.addEventListener("click", function () {
      startCard.classList.add("hidden");
      loadGame();
    });
  }

  let muted = false;
  if (muteBtn) {
    muteBtn.addEventListener("click", function () {
      muted = !muted;
      muteBtn.classList.toggle("active", muted);
      frame.contentWindow &&
        frame.contentWindow.postMessage({ type: "setMuted", muted }, "*");
    });
  }

  // ── Fullscreen: toggle in/out, then re-fit ──
  if (fullscreenBtn) {
    fullscreenBtn.addEventListener("click", function () {
      if (isFullscreen()) {
        if (document.exitFullscreen) document.exitFullscreen();
        else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
        return;
      }

      const request =
        screenEl.requestFullscreen ||
        screenEl.webkitRequestFullscreen ||
        screenEl.msRequestFullscreen;

      if (request) {
        Promise.resolve(request.call(screenEl)).catch(() => {});
      }
    });
  }

  // The layout hasn't actually changed yet when fullscreenchange fires on
  // some browsers, so re-fit over the next few frames as well.
  ["fullscreenchange", "webkitfullscreenchange"].forEach((evt) => {
    document.addEventListener(evt, function () {
      fitStage();
      requestAnimationFrame(fitStage);
      setTimeout(fitStage, 120);
      setTimeout(measureContent, 200);
    });
  });

  // ═══════════════════════════════════════════
  // STAR RATING
  // ═══════════════════════════════════════════
  const starWrap = document.getElementById("gpStarRating");
  const ratingDisplay = document.getElementById("gpRating");

  if (starWrap) {
    const stars = Array.from(starWrap.querySelectorAll(".gp-star"));

    function paintStars(upTo, className) {
      stars.forEach((star) => {
        const value = Number(star.dataset.value);
        star.classList.toggle(className, value <= upTo);
      });
    }

    function currentRating() {
      return Number(starWrap.dataset.current || 0);
    }

    starWrap.addEventListener("mouseover", function (e) {
      const star = e.target.closest(".gp-star");
      if (!star) return;
      paintStars(Number(star.dataset.value), "hover-preview");
    });

    starWrap.addEventListener("mouseleave", function () {
      paintStars(0, "hover-preview");
    });

    starWrap.addEventListener("click", function (e) {
      const star = e.target.closest(".gp-star");
      if (!star || !gameId) return;

      if (!isLoggedIn()) {
        showToast("Log in to rate this game");
        setTimeout(() => {
          window.location.href = "?url=auth";
        }, 900);
        return;
      }

      const value = Number(star.dataset.value);
      starWrap.classList.add("submitting");

      fetch(`?url=games/rate&gameId=${gameId}`, {
        method: "POST",
        headers: authHeaders(),
        body: JSON.stringify({ rating: value }),
      })
        .then((res) => {
          if (!res.ok) throw new Error(`Status ${res.status}`);
          return res.json();
        })
        .then((data) => {
          starWrap.dataset.current = data.userRating ?? value;
          paintStars(currentRating(), "filled");
          if (ratingDisplay && typeof data.avgRating !== "undefined") {
            ratingDisplay.textContent = `★ ${data.avgRating}`;
          }
        })
        .catch((err) => {
          console.error("Failed to submit rating:", err);
          showToast("Couldn't save your rating — try again");
        })
        .finally(() => {
          starWrap.classList.remove("submitting");
        });
    });
  }

  // ═══════════════════════════════════════════
  // FAVORITE
  // ═══════════════════════════════════════════
  const favBtn = document.getElementById("gpFavBtn");
  if (favBtn) {
    favBtn.addEventListener("click", function () {
      this.classList.toggle("active");
    });
  }
})();
