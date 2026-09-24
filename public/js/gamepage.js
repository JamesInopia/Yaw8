// ═══════════════════════════════════════════
// GAME PAGE — real backend version
// ═══════════════════════════════════════════
(function () {
  const cabinet = document.getElementById("gpCabinet");
  if (!cabinet) return;

  const gameId = cabinet.dataset.gameId;
  const playableType = cabinet.dataset.playableType;
  let playableUrl = cabinet.dataset.playableUrl || "";

  // isLoggedIn() comes from auth.js (session meta tag OR stored JWT)

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

  // ── Auto-centering ("crop to where the game actually draws") ───────────
  // The iframe is always at least NATIVE_W x START_H, but plenty of games
  // draw a smaller, fixed-size canvas in the top-left corner of their page
  // (Cut the Rope style). `crop` is the rectangle, in iframe pixels, that
  // the game really occupies; the player shows just that rectangle, centred,
  // instead of the whole (mostly empty) page. null = show the whole page.
  let crop = null;
  let drawnBounds = null; // union of everything seen drawn so far (grows only)

  const CROP_MIN_W = 200; // ignore "content" smaller than this (spinners, "loading...")
  const CROP_MIN_H = 150;
  const CROP_SLACK = 8;   // already within this many px of full size = don't bother
  const CROP_MAX_SCAN = 1000; // DOM-heavy pages: skip rather than slow the game down
  const BACKDROP_FRACTION = 0.85;
  const NON_VISUAL_TAGS = new Set([
    "SCRIPT", "STYLE", "LINK", "META", "NOSCRIPT", "TEMPLATE", "TITLE",
    "BR", "HEAD", "BASE", "SOURCE", "TRACK", "PARAM", "OPTION", "DATALIST",
  ]);
  // Things that draw pixels by themselves, however big they are
  const SURFACE_TAGS = new Set([
    "CANVAS", "IMG", "VIDEO", "SVG", "IFRAME", "EMBED", "OBJECT", "PICTURE",
    "INPUT", "BUTTON", "SELECT", "TEXTAREA",
  ]);

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

    // The part of the iframe that gets shown: just the area the game draws
    // in (see crop above), or the whole page when there's nothing to crop.
    const view = crop || { x: 0, y: 0, w: NATIVE_W, h: contentH };

    if (full) {
      const availW = window.innerWidth;
      const availH = window.innerHeight;

      scale = Math.min(availW / view.w, availH / view.h);
      screenH = availH;
      scrollEl.classList.remove("gp-scrollable");
    } else {
      const availW = screenEl.clientWidth || cabinet.clientWidth || NATIVE_W;
      const maxH = Math.max(MIN_SCREEN_H, window.innerHeight - CHROME_H);

      // Fit width, but don't blow a small game up past its native size.
      scale = Math.min(1, availW / view.w);

      const scaledH = view.h * scale;
      screenH = Math.min(scaledH, maxH);

      // Taller than the box? Then scroll instead of shrinking further.
      scrollEl.classList.toggle("gp-scrollable", scaledH > screenH + 1);
    }

    screenEl.style.setProperty("--gp-scale", String(scale));
    screenEl.style.setProperty("--gp-native-w", NATIVE_W + "px");
    screenEl.style.setProperty("--gp-native-h", contentH + "px");
    screenEl.style.setProperty("--gp-vw", view.w * scale + "px");
    screenEl.style.setProperty("--gp-vh", view.h * scale + "px");
    // Slide the stage so the cropped area lines up with the viewport's corner
    screenEl.style.setProperty("--gp-off-x", -view.x * scale + "px");
    screenEl.style.setProperty("--gp-off-y", -view.y * scale + "px");
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
      let changed = false;

      if (Math.abs(next - contentH) > 4) {
        contentH = next;
        changed = true;
      }

      if (updateCrop(doc)) changed = true;

      if (changed) fitStage();
    } catch (err) {
      /* cross-origin game — keep the fallback height */
    }
  }

  // ── Find the rectangle the game actually draws in ───────────────────────
  // Walks the game's DOM and unions the boxes of everything that paints
  // pixels: canvases/images/video, elements with a background or border, and
  // text. Deliberately errs on the side of showing MORE: anything unusual
  // (huge DOM, cross-origin, a full-screen HUD) simply means no cropping,
  // which is exactly how the player behaved before.
  function measureDrawnBounds(doc) {
    const win = doc.defaultView;
    if (!win || !doc.body) return null;

    const all = doc.body.getElementsByTagName("*");
    if (all.length > CROP_MAX_SCAN) return null;

    const docW = NATIVE_W;
    const docH = Math.max(win.innerHeight || 0, contentH);
    let x1 = Infinity, y1 = Infinity, x2 = -Infinity, y2 = -Infinity;

    // Adds a rect (viewport coords) to the union, clipped to the page area
    function add(r) {
      const l = Math.max(0, r.left), t = Math.max(0, r.top);
      const rr = Math.min(docW, r.right), bb = Math.min(docH, r.bottom);
      if (rr - l < 1 || bb - t < 1) return; // empty, or entirely off-screen
      if (l < x1) x1 = l;
      if (t < y1) y1 = t;
      if (rr > x2) x2 = rr;
      if (bb > y2) y2 = bb;
    }

    const range = doc.createRange();

    for (let i = 0; i < all.length; i++) {
      const el = all[i];
      const tag = el.tagName.toUpperCase();
      if (NON_VISUAL_TAGS.has(tag)) continue;
      if (el.ownerSVGElement) continue; // parts inside an <svg>: the <svg> covers them

      const cs = win.getComputedStyle(el);
      if (cs.display === "none" || cs.visibility === "hidden") continue;
      if (parseFloat(cs.opacity) === 0) continue;

      const r = el.getBoundingClientRect();

      // Text: use the text's own extent, not its (often full-width) container
      for (let n = el.firstChild; n; n = n.nextSibling) {
        if (n.nodeType === 3 && n.nodeValue.trim() !== "") {
          range.selectNodeContents(n);
          add(range.getBoundingClientRect());
        }
      }

      if (r.width < 1 || r.height < 1) continue;

      if (SURFACE_TAGS.has(tag)) {
        add(r);
        continue;
      }

      // A plain box only counts if it paints something (background/border)...
      const bg = cs.backgroundColor;
      const paintsBox =
        (bg && bg !== "transparent" && bg !== "rgba(0, 0, 0, 0)") ||
        cs.backgroundImage !== "none" ||
        ["Top", "Right", "Bottom", "Left"].some(function (side) {
          return cs["border" + side + "Style"] !== "none" && parseFloat(cs["border" + side + "Width"]) > 0;
        });
      if (!paintsBox) continue;

      // ...and a box covering (nearly) the whole page is a backdrop / dimmer /
      // page background, not "the game". Its children still count on their own.
      if (r.width >= docW * BACKDROP_FRACTION && r.height >= docH * BACKDROP_FRACTION) continue;

      add(r);
    }

    return x2 > x1 && y2 > y1 ? { x1: x1, y1: y1, x2: x2, y2: y2 } : null;
  }

  // Recomputes `crop`. Returns true if it changed.
  function updateCrop(doc) {
    const seen = measureDrawnBounds(doc);

    // Grow-only: once something has been seen drawn, its area stays visible.
    // Keeps the player from jumping around when a game shows and hides menus,
    // and guarantees a late-appearing overlay is never clipped away.
    if (seen) {
      drawnBounds = drawnBounds
        ? {
            x1: Math.min(drawnBounds.x1, seen.x1),
            y1: Math.min(drawnBounds.y1, seen.y1),
            x2: Math.max(drawnBounds.x2, seen.x2),
            y2: Math.max(drawnBounds.y2, seen.y2),
          }
        : seen;
    }

    let next = null;
    if (drawnBounds) {
      const x = Math.max(0, Math.floor(drawnBounds.x1));
      const y = Math.max(0, Math.floor(drawnBounds.y1));
      const w = Math.min(NATIVE_W, Math.ceil(drawnBounds.x2)) - x;
      const h = Math.min(contentH, Math.ceil(drawnBounds.y2)) - y;

      const bigEnough = w >= CROP_MIN_W && h >= CROP_MIN_H;
      const fillsPage = w >= NATIVE_W - CROP_SLACK && h >= contentH - CROP_SLACK;
      if (bigEnough && !fillsPage) next = { x: x, y: y, w: w, h: h };
    }

    const same =
      (!crop && !next) ||
      (crop && next && crop.x === next.x && crop.y === next.y && crop.w === next.w && crop.h === next.h);
    if (same) return false;

    crop = next;
    return true;
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

    // Fresh page (start or restart): forget what the previous run drew
    crop = null;
    drawnBounds = null;

    frame.src = playableUrl;
    frame.addEventListener(
      "load",
      function () {
        remeasureSoon();
        // The shim resets to full volume/unmuted on every fresh page load
        // (restart included) — reapply whatever the user had set. Sent a few
        // times because some engines only set up their audio after load.
        sendAudioState();
        [150, 600, 1800].forEach((ms) => setTimeout(sendAudioState, ms));

        // Some games grow/shrink as you play (score panels, game-over
        // overlays). Watch the document and re-fit when it changes.
        try {
          const doc = frame.contentDocument;
          if (doc && doc.body && typeof ResizeObserver !== "undefined") {
            new ResizeObserver(measureContent).observe(doc.body);
          }

          // Menus, overlays and canvases that appear or resize without the
          // page height changing. Throttled so animated pages stay cheap.
          if (doc && doc.body && typeof MutationObserver !== "undefined") {
            let queued = null;
            new MutationObserver(function () {
              if (queued) return;
              queued = setTimeout(function () {
                queued = null;
                measureContent();
              }, 250);
            }).observe(doc.body, {
              subtree: true,
              childList: true,
              characterData: true,
              attributes: true,
              attributeFilter: ["class", "style", "hidden", "width", "height"],
            });
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

  // ═══════════════════════════════════════════
  // VOLUME / MUTE
  // Talks to the audio shim injected into the extracted game's index.html
  // (see GameService::injectAudioShim) — postMessage alone does nothing
  // unless something on the other end is listening for it.
  // ═══════════════════════════════════════════
  const volumeControl = document.getElementById("gpVolumeControl");
  const volumeSlider = document.getElementById("gpVolumeSlider");
  const muteIcon = document.getElementById("gpMuteIcon");

  const UNMUTED_ICON = '<path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/>';
  const MUTED_ICON = '<path d="M16.5 12A4.5 4.5 0 0014 7.97v1.79l2.48 2.48c.01-.08.02-.16.02-.24zm2.5 0c0 .94-.2 1.82-.54 2.64l1.51 1.51C20.63 14.91 21 13.5 21 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3L3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06a8.99 8.99 0 003.69-1.81L18.73 21 20 19.73l-9-9L4.27 3zM12 4L9.91 6.09 12 8.18V4z"/>';

  function isMutedOrSilent() {
    return mutedByUser || volume === 0;
  }

  function updateMuteUi() {
    if (!muteBtn) return;
    const silent = isMutedOrSilent();
    muteBtn.classList.toggle("active", silent);
    muteBtn.setAttribute("aria-pressed", String(silent));
    muteBtn.title = silent ? "Unmute" : "Mute";
    if (muteIcon) muteIcon.innerHTML = silent ? MUTED_ICON : UNMUTED_ICON;
  }

  function sendAudioState() {
    if (!frame || !frame.contentWindow) return;
    frame.contentWindow.postMessage({ type: "setVolume", volume }, "*");
    frame.contentWindow.postMessage({ type: "setMuted", muted: mutedByUser }, "*");
  }

  let volume = 1; // 0–1
  let mutedByUser = false;

  if (muteBtn) {
    muteBtn.addEventListener("click", function () {
      mutedByUser = !mutedByUser;
      updateMuteUi();
      sendAudioState();
    });
  }

  if (volumeSlider) {
    volumeSlider.addEventListener("input", function () {
      volume = Number(volumeSlider.value) / 100;
      // Dragging the slider back up should un-mute, matching how every
      // other volume slider (YouTube, Spotify, etc.) behaves.
      if (volume > 0 && mutedByUser) mutedByUser = false;
      updateMuteUi();
      sendAudioState();
    });
  }

  updateMuteUi();

  // ── Fullscreen: toggle in/out, then re-fit ──
  // Cross-browser fullscreen helpers (Safari still needs the webkit-
  // prefixed versions; everything else supports the standard API).
  function fsElement() {
    return document.fullscreenElement || document.webkitFullscreenElement || null;
  }

  function requestFs(el) {
    const request = el.requestFullscreen || el.webkitRequestFullscreen;
    if (!request) return Promise.reject(new Error("Fullscreen API unavailable"));
    return Promise.resolve(request.call(el));
  }

  function exitFs() {
    const exit = document.exitFullscreen || document.webkitExitFullscreen;
    if (!exit) return Promise.reject(new Error("Fullscreen API unavailable"));
    return Promise.resolve(exit.call(document));
  }

  function updateFullscreenUi() {
    if (!fullscreenBtn) return;
    const active = isFullscreen();
    fullscreenBtn.classList.toggle("active", active);
    fullscreenBtn.setAttribute("aria-pressed", String(active));
    fullscreenBtn.title = active ? "Exit fullscreen" : "Fullscreen";
    const icon = document.getElementById("gpFullscreenIcon");
    if (icon) {
      icon.innerHTML = active
        ? '<path d="M5 16h3v3h2v-5H5v2zm3-8H5v2h5V5H8v3zm6 11h2v-3h3v-2h-5v5zm2-11V5h-2v5h5V8h-3z"/>'
        : '<path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"/>';
    }
  }

  if (fullscreenBtn) {
    fullscreenBtn.addEventListener("click", function () {
      if (fsElement()) {
        exitFs().catch((err) => console.error("Failed to exit fullscreen:", err));
      } else if (screenEl) {
        requestFs(screenEl).catch((err) => console.error("Failed to enter fullscreen:", err));
      }
    });
  }

  updateFullscreenUi();

  // The layout hasn't actually changed yet when fullscreenchange fires on
  // some browsers, so re-fit over the next few frames as well.
  ["fullscreenchange", "webkitfullscreenchange"].forEach((evt) => {
    document.addEventListener(evt, function () {
      updateFullscreenUi();
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
    if (typeof isFavorited === "function") {
      favBtn.classList.toggle("active", isFavorited(gameId));
    }
    favBtn.addEventListener("click", function () {
      if (typeof toggleFavoriteId === "function") {
        const nowFavorited = toggleFavoriteId(gameId);
        this.classList.toggle("active", nowFavorited);
        if (typeof showSiteToast === "function") {
          showSiteToast(nowFavorited ? "Added to Favorites" : "Removed from Favorites");
        }
      } else {
        this.classList.toggle("active");
      }
    });
  }

  // ═══════════════════════════════════════════
  // REPORT
  // ═══════════════════════════════════════════
  const reportBtn = document.getElementById("gpReportBtn");
  if (reportBtn) {
    reportBtn.addEventListener("click", function () {
      if (typeof openReportModal === "function") {
        const title = document.getElementById("gpTitle");
        openReportModal(gameId, title ? title.textContent : "");
      }
    });
  }

  // ═══════════════════════════════════════════
  // DOWNLOAD
  // ═══════════════════════════════════════════
  const downloadBtn = document.getElementById("gpDownloadBtn");
  if (downloadBtn) {
    downloadBtn.addEventListener("click", function () {
      window.location.href = `?url=games/download&gameId=${gameId}`;
    });
  }
})();