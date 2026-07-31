// ═══════════════════════════════════════════
// GAME PAGE — real backend version
// (script.js already handles the shared loading screen and nav active
// state on this page, so this file only covers player-specific logic:
// the game frame, the star rating widget, and the play counter.)
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
  // PLAYER CONTROLS (only present when the game is actually playable —
  // .jar / unsupported games render a download or notice instead)
  // ═══════════════════════════════════════════
  const screen = document.getElementById("gpScreen");
  const startCard = document.getElementById("gpStartCard");
  const playBtn = document.getElementById("gpPlayBtn");
  const frame = document.getElementById("gpFrame");
  const restartBtn = document.getElementById("gpRestartBtn");
  const muteBtn = document.getElementById("gpMuteBtn");
  const fullscreenBtn = document.getElementById("gpFullscreenBtn");
  const playsEl = document.getElementById("gpPlays");

  let hasCountedPlay = false;

  function loadGame() {
    if (playableUrl) {
      frame.src = playableUrl;
    }
  }

  // Tells the server a play just started (increments totalPlays). Only
  // counted once per page visit, and only for logged-in users — guests
  // can still play, it just won't add to the count.
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
    screen.classList.add("playing");
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

  if (fullscreenBtn) {
    fullscreenBtn.addEventListener("click", function () {
      if (screen.requestFullscreen) screen.requestFullscreen();
      else if (screen.webkitRequestFullscreen) screen.webkitRequestFullscreen();
    });
  }

  // ═══════════════════════════════════════════
  // STAR RATING (1-5, replaces the old Like button)
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
  // FAVORITE (front-end only for now, no backend endpoint yet)
  // ═══════════════════════════════════════════
  const favBtn = document.getElementById("gpFavBtn");
  if (favBtn) {
    favBtn.addEventListener("click", function () {
      this.classList.toggle("active");
    });
  }
})();
