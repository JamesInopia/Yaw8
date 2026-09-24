// ═══════════════════════════════════════════
// DOWNLOADER PAGE
// Steam-style media header carousel, plus the same Favorite/Report/Rate
// wiring used on the game player page (gamepage.js), with Download added.
// ═══════════════════════════════════════════
(function () {
  const cabinet = document.getElementById("dlCabinet");
  if (!cabinet) return;

  const gameId = cabinet.dataset.gameId;

  // ═══════════════════════════════════════════
  // MEDIA CAROUSEL (big preview + thumbnail filmstrip)
  // ═══════════════════════════════════════════
  const mediaMain = document.getElementById("dlMediaMain");
  const stripItems = Array.from(document.querySelectorAll(".dl-media-strip-item"));
  const prevBtn = document.getElementById("dlMediaPrev");
  const nextBtn = document.getElementById("dlMediaNext");

  const items = stripItems.map(function (btn) {
    return { type: btn.dataset.type, path: btn.dataset.path };
  });

  let index = 0;

  function renderMain() {
    // No feature graphics — the fallback thumbnail/color already rendered
    // server-side is all there is, nothing to swap between.
    if (items.length === 0 || !mediaMain) return;

    const item = items[index];
    mediaMain.innerHTML = item.type === "video"
      ? `<video src="${item.path}" controls preload="metadata" playsinline></video>`
      : `<img src="${item.path}" alt="">`;

    stripItems.forEach(function (btn, i) {
      btn.classList.toggle("active", i === index);
    });
  }

  function updateArrows() {
    if (!prevBtn || !nextBtn) return;
    const disablePrev = items.length <= 1 || index <= 0;
    const disableNext = items.length <= 1 || index >= items.length - 1;
    prevBtn.classList.toggle("disabled", disablePrev);
    nextBtn.classList.toggle("disabled", disableNext);
  }

  if (prevBtn) {
    prevBtn.addEventListener("click", function () {
      if (index <= 0) return;
      index -= 1;
      renderMain();
      updateArrows();
    });
  }

  if (nextBtn) {
    nextBtn.addEventListener("click", function () {
      if (index >= items.length - 1) return;
      index += 1;
      renderMain();
      updateArrows();
    });
  }

  stripItems.forEach(function (btn, i) {
    btn.addEventListener("click", function () {
      index = i;
      renderMain();
      updateArrows();
    });
  });

  updateArrows();

  // ═══════════════════════════════════════════
  // FAVORITE (shared localStorage helpers from script.js)
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
  // DOWNLOAD
  // ═══════════════════════════════════════════
  const downloadBtn = document.getElementById("gpDownloadBtn");
  if (downloadBtn) {
    downloadBtn.addEventListener("click", function () {
      window.location.href = `?url=games/download&gameId=${gameId}`;
    });
  }

  // ═══════════════════════════════════════════
  // REPORT
  // ═══════════════════════════════════════════
  const reportBtn = document.getElementById("gpReportBtn");
  if (reportBtn) {
    reportBtn.addEventListener("click", function () {
      if (typeof openReportModal === "function") {
        const title = document.getElementById("dlTitle");
        openReportModal(gameId, title ? title.textContent : "");
      }
    });
  }

  // ═══════════════════════════════════════════
  // RATING (1-5 stars, same endpoint as the player page)
  // ═══════════════════════════════════════════
  const starWrap = document.getElementById("dlStarRating");
  if (starWrap) {
    const currentRating = () => Number(starWrap.dataset.current) || 0;

    function paintStars(upTo, className) {
      starWrap.querySelectorAll(".gp-star").forEach(function (star) {
        const value = Number(star.dataset.value);
        star.classList.toggle(className, value <= upTo);
      });
    }

    starWrap.addEventListener("mouseover", function (e) {
      const star = e.target.closest(".gp-star");
      if (!star) return;
      paintStars(Number(star.dataset.value), "hover-preview");
    });

    starWrap.addEventListener("mouseleave", function () {
      paintStars(currentRating(), "filled");
    });

    starWrap.addEventListener("click", function (e) {
      const star = e.target.closest(".gp-star");
      if (!star) return;

      if (typeof isLoggedIn === "function" && !isLoggedIn()) {
        if (typeof showSiteToast === "function") showSiteToast("Log in to rate this game");
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
          const ratingDisplay = document.getElementById("dlRating");
          if (ratingDisplay && typeof data.avgRating !== "undefined") {
            ratingDisplay.textContent = `★ ${data.avgRating}`;
          }
        })
        .catch((err) => {
          console.error("Failed to submit rating:", err);
          if (typeof showSiteToast === "function") showSiteToast("Couldn't save your rating — try again");
        })
        .finally(() => {
          starWrap.classList.remove("submitting");
        });
    });
  }
})();
