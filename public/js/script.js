// ═══════════════════════════════════════════
// MODAL FUNCTIONALITY
// ═══════════════════════════════════════════
const modal = document.getElementById("game-modal");
const modalClose = document.querySelector(".modal-close");
const carouselPrev = document.querySelector(".carousel-prev");
const carouselNext = document.querySelector(".carousel-next");
const gamePagePath = "?url=games/player";
const downloaderPagePath = "?url=downloader";
const CAROUSEL_VISIBLE_ITEMS = 4; // number of cards shown at a time
let currentGameId = null;
let currentGameAccessType = "online";
let carouselIndex = 0; // current starting card index

// Feature-graphics carousel (modal's thumbnail area)
let modalCarouselItems = [];
let modalCarouselIndex = 0;

const THUMB_COLOR_CLASSES = ["gt-pixel-drift", "gt-tower-tactics", "gt-box-jumper", "gt-color-clash"];

function getThumbColorClass(key) {
  const str = String(key || "");
  let hash = 0;
  for (let i = 0; i < str.length; i++) {
    hash = (hash * 31 + str.charCodeAt(i)) >>> 0;
  }
  return THUMB_COLOR_CLASSES[hash % THUMB_COLOR_CLASSES.length];
}

function renderThumbInto(container, thumbnail, title, key) {
  if (!container) return;
  container.classList.remove(...THUMB_COLOR_CLASSES);

  if (thumbnail) {
    container.innerHTML = `<img src="${thumbnail}" alt="${title || ""}">`;
  } else {
    container.classList.add(getThumbColorClass(key));
    const label = (title || "").toUpperCase().split(" ").join("<br>");
    container.innerHTML = `<div class="game-thumb-title">${label}</div>`;
  }
}

// ═══════════════════════════════════════════
// MODAL FEATURE-GRAPHICS CAROUSEL
// Shows a game's feature graphics (screenshots/video clips), videos
// first. Falls back to a single item — the game's thumbnail — when it
// has no feature graphics at all, in which case the arrows are simply
// disabled (nothing to scroll to).
// ═══════════════════════════════════════════
function buildModalCarouselItems(fullGame) {
  const graphics = Array.isArray(fullGame.featureGraphics) ? fullGame.featureGraphics : [];

  if (graphics.length > 0) {
    return graphics.map((fg) => ({
      type: fg.mediaType === "video" ? "video" : "image",
      path: fg.filePath,
    }));
  }

  // No feature graphics — fall back to the thumbnail (or colored default).
  return [{ type: "thumbnail", path: fullGame.thumbnail, title: fullGame.title, key: fullGame.gameId }];
}

function renderModalCarouselItem() {
  const container = document.getElementById("modalThumbnail");
  if (!container) return;

  const item = modalCarouselItems[modalCarouselIndex];
  if (!item) return;

  if (item.type === "video") {
    container.classList.remove(...THUMB_COLOR_CLASSES);
    // Never autoplay — the person presses play themselves.
    container.innerHTML = `<video src="${item.path}" controls preload="metadata" playsinline></video>`;
  } else if (item.type === "image") {
    container.classList.remove(...THUMB_COLOR_CLASSES);
    container.innerHTML = `<img src="${item.path}" alt="">`;
  } else {
    renderThumbInto(container, item.path, item.title, item.key);
  }
}

function updateModalCarouselArrows() {
  const prevBtn = document.getElementById("modalThumbPrev");
  const nextBtn = document.getElementById("modalThumbNext");
  if (!prevBtn || !nextBtn) return;

  prevBtn.classList.toggle("disabled", modalCarouselIndex <= 0);
  nextBtn.classList.toggle("disabled", modalCarouselIndex >= modalCarouselItems.length - 1);
}

function renderModalCarousel() {
  renderModalCarouselItem();
  updateModalCarouselArrows();
}

// ═══════════════════════════════════════════
// SITE-WIDE TOAST (favorite / report / rating feedback)
// ═══════════════════════════════════════════
function showSiteToast(text) {
  let toast = document.getElementById("siteToast");
  if (!toast) {
    toast = document.createElement("div");
    toast.id = "siteToast";
    toast.className = "site-toast";
    document.body.appendChild(toast);
  }
  toast.textContent = text;
  toast.classList.add("show");
  clearTimeout(showSiteToast._t);
  showSiteToast._t = setTimeout(() => toast.classList.remove("show"), 2200);
}

// ═══════════════════════════════════════════
// FAVORITES
// (Client-side only for now — there's no favorites table/endpoint yet,
// so this mirrors the existing gpFavBtn stub on the game player page:
// a per-browser toggle stored in localStorage. Swap this out for a real
// fetch() call if/when a backend favorites feature is added.)
// ═══════════════════════════════════════════
const FAVORITES_KEY = "yaw8_favorites";

function getFavoriteIds() {
  try {
    const raw = JSON.parse(localStorage.getItem(FAVORITES_KEY) || "[]");
    return Array.isArray(raw) ? raw : [];
  } catch (e) {
    return [];
  }
}

function isFavorited(gameId) {
  return getFavoriteIds().includes(String(gameId));
}

// Flips the favorite state for a game and returns the NEW state (true = now favorited).
function toggleFavoriteId(gameId) {
  const ids = getFavoriteIds();
  const key = String(gameId);
  const idx = ids.indexOf(key);

  if (idx === -1) {
    ids.push(key);
  } else {
    ids.splice(idx, 1);
  }

  localStorage.setItem(FAVORITES_KEY, JSON.stringify(ids));
  return idx === -1;
}

// ═══════════════════════════════════════════
// GAME CARD QUICK ACTIONS (Favorite + Report)
// Wires up the two icon buttons overlaid on a game card's thumbnail.
// Called once per card, whether it was rendered by PHP (games/index.php)
// or built client-side (games.js renderGrid()).
// ═══════════════════════════════════════════
function wireCardQuickActions(card) {
  if (!card || card.dataset.quickActionsWired) return;
  card.dataset.quickActionsWired = "1";

  const gameId = card.getAttribute("data-game-id");
  const title = card.getAttribute("data-title") || "";

  const favBtn = card.querySelector(".game-favorite-btn");
  if (favBtn) {
    favBtn.classList.toggle("active", isFavorited(gameId));
    favBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      e.preventDefault();
      const nowFavorited = toggleFavoriteId(gameId);
      favBtn.classList.toggle("active", nowFavorited);
      showSiteToast(nowFavorited ? "Added to Favorites" : "Removed from Favorites");
    });
  }

  const reportBtn = card.querySelector(".game-report-btn");
  if (reportBtn) {
    reportBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      e.preventDefault();
      openReportModal(gameId, title);
    });
  }
}

// Open modal only when clicking the card (not the play button)
document.querySelectorAll(".game-card").forEach((card) => {
  card.addEventListener("click", function (e) {
    if (e.target.closest(".btn-play") || e.target.closest(".dev-name-link")) {
      return;
    }

    openGameModal(this.dataset);
  });

  wireCardQuickActions(card);
});

async function openGameModal(gameData) {
  currentGameId = gameData.gameId;

  // Update modal content
  document.getElementById("modalGameName").textContent = gameData.title;

  try {
    const response = await fetch(`?url=games/details&gameId=${gameData.gameId}`, {
        headers: authHeaders(),
    });
    if (!response.ok) {
      throw new Error(`An error has occured. Status: ${response.status}`);
    }
    const fullGame = await response.json();

    document.getElementById("modalGameName").textContent = gameData.title;
    renderDevelopers(fullGame.devNames);
    document.getElementById("modalRating").textContent =
      `★ ${fullGame.avgRating}`;
    document.getElementById("modalPlays").textContent = fullGame.totalPlays;
    document.getElementById("modalGenre").textContent = fullGame.genreNames;
    document.getElementById("modalDescription").textContent =
      fullGame.description;
    document.getElementById("modalGameLastUpdated").textContent =
      fullGame.lastUpdated || "—";
    document.getElementById("modalGameReleased").textContent =
      fullGame.dateReleased || "—";

    // Feature-graphics carousel (falls back to the thumbnail alone)
    modalCarouselItems = buildModalCarouselItems(fullGame);
    modalCarouselIndex = 0;
    renderModalCarousel();

    // Sync the rate / favorite action row to this game
    if (modalStarWrap) {
      modalStarWrap.dataset.current = fullGame.userRating || 0;
      paintModalStars(Number(modalStarWrap.dataset.current), "filled");
    }
    if (modalFavBtn) {
      modalFavBtn.classList.toggle("active", isFavorited(fullGame.gameId));
    }

    // Availability: online (playable, maybe also downloadable) vs
    // download-only (no in-browser player — PLAY NOW leads to the
    // Downloader page instead).
    currentGameAccessType = fullGame.accessType || "online";
    const isDownloadOnly = currentGameAccessType === "download_only";

    if (modalPlayBtn) {
      modalPlayBtn.textContent = isDownloadOnly ? "VIEW & DOWNLOAD" : "PLAY NOW";
    }

    const modalActionRow = document.getElementById("modalActionRow");
    if (modalDownloadBtn) {
      const showDownload = !!fullGame.isDownloadable;
      modalDownloadBtn.style.display = showDownload ? "" : "none";
      if (modalActionRow) modalActionRow.classList.toggle("has-download", showDownload);
    }

    // Reset carousel
    carouselIndex = 0;
    populateCarousel(currentGameId);
  } catch(error) {
    console.error("Failed to load game details: ", error);
  }

  // Show modal
  modal.classList.add("active");
  modal.scrollTop = 0;
  document.body.style.overflow = "hidden";
}

function closeModal() {
  modal.classList.remove("active");
  document.body.style.overflow = "auto";
}

// Close modal
if (modal) {
  if (modalClose) {
    modalClose.addEventListener("click", closeModal);
  }
  const modalOverlay = modal.querySelector(".modal-overlay");
  if (modalOverlay) {
    modalOverlay.addEventListener("click", closeModal);
  }
}

// Keyboard close
document.addEventListener("keydown", function (e) {
  if (e.key === "Escape" && modal.classList.contains("active")) {
    closeModal();
  }
});

// ═══════════════════════════════════════════
// PLAY BUTTON
// ═══════════════════════════════════════════
// Path detection + resolver
const pathname = window.location.pathname;
const inContentFolder = /\/app\/views\/content\//.test(pathname);

// Updated navigation helper
function goToGamePage(gameId) {
  window.location.href = `${gamePagePath}&gameId=${gameId}`;
}

function goToDownloaderPage(gameId) {
  window.location.href = `${downloaderPagePath}&gameId=${gameId}`;
}

document.addEventListener("click", function (e) {
  const playBtn = e.target.closest(".btn-play");
  if (!playBtn) return;

  const card = playBtn.closest("[data-game-id]");
  if (!card) return;

  const gameId = card.getAttribute("data-game-id");
  if (card.getAttribute("data-access-type") === "download_only") {
    goToDownloaderPage(gameId);
  } else {
    goToGamePage(gameId);
  }
});

// Feature-graphics carousel arrows (modal)
const modalThumbPrev = document.getElementById("modalThumbPrev");
const modalThumbNext = document.getElementById("modalThumbNext");

if (modalThumbPrev) {
  modalThumbPrev.addEventListener("click", function () {
    if (modalCarouselIndex <= 0) return;
    modalCarouselIndex -= 1;
    renderModalCarousel();
  });
}

if (modalThumbNext) {
  modalThumbNext.addEventListener("click", function () {
    if (modalCarouselIndex >= modalCarouselItems.length - 1) return;
    modalCarouselIndex += 1;
    renderModalCarousel();
  });
}

const modalPlayBtn = document.querySelector(".btn-play-modal");
if (modalPlayBtn) {
  modalPlayBtn.addEventListener("click", function () {
    if (!currentGameId) return;
    if (currentGameAccessType === "download_only") {
      goToDownloaderPage(currentGameId);
    } else {
      goToGamePage(currentGameId);
    }
  });
}

const modalDownloadBtn = document.getElementById("modalDownloadBtn");
if (modalDownloadBtn) {
  modalDownloadBtn.addEventListener("click", function () {
    if (!currentGameId) return;
    window.location.href = `?url=games/download&gameId=${currentGameId}`;
  });
}

// ═══════════════════════════════════════════
// MODAL ACTION ROW: RATE / FAVORITE / REPORT
// ═══════════════════════════════════════════
const modalStarWrap = document.getElementById("modalStarRating");
const modalFavBtn = document.getElementById("modalFavBtn");
const modalReportBtn = document.getElementById("modalReportBtn");

function paintModalStars(upTo, className) {
  if (!modalStarWrap) return;
  modalStarWrap.querySelectorAll(".modal-star").forEach((star) => {
    const value = Number(star.dataset.value);
    star.classList.toggle(className, value <= upTo);
  });
}

if (modalStarWrap) {
  modalStarWrap.addEventListener("mouseover", function (e) {
    const star = e.target.closest(".modal-star");
    if (!star) return;
    paintModalStars(Number(star.dataset.value), "hover-preview");
  });

  modalStarWrap.addEventListener("mouseleave", function () {
    paintModalStars(0, "hover-preview");
  });

  modalStarWrap.addEventListener("click", function (e) {
    const star = e.target.closest(".modal-star");
    if (!star || !currentGameId) return;

    if (!isLoggedIn()) {
      showSiteToast("Log in to rate this game");
      setTimeout(() => {
        window.location.href = "?url=auth";
      }, 900);
      return;
    }

    const value = Number(star.dataset.value);
    modalStarWrap.classList.add("submitting");

    fetch(`?url=games/rate&gameId=${currentGameId}`, {
      method: "POST",
      headers: authHeaders(),
      body: JSON.stringify({ rating: value }),
    })
      .then((res) => {
        if (!res.ok) throw new Error(`Status ${res.status}`);
        return res.json();
      })
      .then((data) => {
        modalStarWrap.dataset.current = data.userRating ?? value;
        paintModalStars(Number(modalStarWrap.dataset.current), "filled");
        const ratingStat = document.getElementById("modalRating");
        if (ratingStat && typeof data.avgRating !== "undefined") {
          ratingStat.textContent = `★ ${data.avgRating}`;
        }
      })
      .catch((err) => {
        console.error("Failed to submit rating:", err);
        showSiteToast("Couldn't save your rating — try again");
      })
      .finally(() => {
        modalStarWrap.classList.remove("submitting");
      });
  });
}

if (modalFavBtn) {
  modalFavBtn.addEventListener("click", function () {
    if (!currentGameId) return;
    const nowFavorited = toggleFavoriteId(currentGameId);
    modalFavBtn.classList.toggle("active", nowFavorited);

    // Keep any matching card on the page (grid, carousel) in sync
    document
      .querySelectorAll(`.game-favorite-btn`)
      .forEach((btn) => {
        const card = btn.closest("[data-game-id]");
        if (card && card.getAttribute("data-game-id") === String(currentGameId)) {
          btn.classList.toggle("active", nowFavorited);
        }
      });

    showSiteToast(nowFavorited ? "Added to Favorites" : "Removed from Favorites");
  });
}

if (modalReportBtn) {
  modalReportBtn.addEventListener("click", function () {
    if (!currentGameId) return;
    openReportModal(currentGameId, document.getElementById("modalGameName").textContent);
  });
}

// ═══════════════════════════════════════════
// REPORT GAME MODAL
// ═══════════════════════════════════════════
const reportModal = document.getElementById("report-game-modal");
const reportForm = document.getElementById("reportGameForm");

function openReportModal(gameId, title) {
  if (!reportModal || !reportForm) return;

  if (!isLoggedIn()) {
    showSiteToast("Log in to report a game");
    setTimeout(() => {
      window.location.href = "?url=auth";
    }, 900);
    return;
  }

  reportForm.reset();
  const otherGroup = document.getElementById("reportOtherGroup");
  const formError = document.getElementById("reportFormError");
  if (otherGroup) otherGroup.style.display = "none";
  if (formError) formError.style.display = "none";

  document.getElementById("reportGameId").value = gameId || "";
  document.getElementById("reportGameName").textContent = title || "this game";

  reportModal.classList.add("active");
  reportModal.scrollTop = 0;
  document.body.style.overflow = "hidden";
}

function closeReportModal() {
  if (!reportModal) return;
  reportModal.classList.remove("active");
  document.body.style.overflow = "auto";
}

if (reportModal && reportForm) {
  const reportClose = document.getElementById("reportModalClose");
  const reportCancel = document.getElementById("reportCancelBtn");
  const reportOverlay = reportModal.querySelector(".modal-overlay");
  const reportReasonSelect = document.getElementById("reportReason");
  const reportOtherGroup = document.getElementById("reportOtherGroup");
  const reportOtherInput = document.getElementById("reportOtherInput");
  const reportFormError = document.getElementById("reportFormError");
  const reportSubmitBtn = document.getElementById("reportSubmitBtn");

  if (reportClose) reportClose.addEventListener("click", closeReportModal);
  if (reportCancel) reportCancel.addEventListener("click", closeReportModal);
  if (reportOverlay) reportOverlay.addEventListener("click", closeReportModal);

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && reportModal.classList.contains("active")) {
      closeReportModal();
    }
  });

  if (reportReasonSelect) {
    reportReasonSelect.addEventListener("change", function () {
      reportOtherGroup.style.display = this.value === "other" ? "flex" : "none";
    });
  }

  reportForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const gameId = document.getElementById("reportGameId").value;
    const reasonValue = reportReasonSelect.value;
    const isOther = reasonValue === "other";
    const reason = isOther ? reportOtherInput.value.trim() : reasonValue;
    const details = document.getElementById("reportDescription").value.trim();

    reportFormError.style.display = "none";

    if (!reasonValue) {
      reportFormError.textContent = "Please choose a reason for this report.";
      reportFormError.style.display = "block";
      return;
    }

    if (isOther && !reason) {
      reportFormError.textContent = "Please specify a reason.";
      reportFormError.style.display = "block";
      return;
    }

    if (!gameId) {
      reportFormError.textContent = "Something went wrong — please reopen the report form.";
      reportFormError.style.display = "block";
      return;
    }

    reportSubmitBtn.disabled = true;

    fetch(`?url=games/report&gameId=${gameId}`, {
      method: "POST",
      headers: authHeaders(),
      body: JSON.stringify({ reason, details }),
    })
      .then(async (res) => {
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.error || `Status ${res.status}`);
        return data;
      })
      .then(() => {
        showSiteToast("Thanks — your report has been submitted.");
        closeReportModal();
      })
      .catch((err) => {
        reportFormError.textContent = err.message || "Couldn't submit your report — try again.";
        reportFormError.style.display = "block";
      })
      .finally(() => {
        reportSubmitBtn.disabled = false;
      });
  });
}

// ─────────────────────────────────────────────
// DEVELOPER DISPLAY
// ─────────────────────────────────────────────
function renderDevelopers(developerString) {
  const wrap = document.getElementById("modalDeveloperWrap");
  wrap.innerHTML = "";

  developerString = developerString || "";

  // Strip leading "by " and split on comma
  const cleaned = developerString.replace(/^by\s+/i, "");
  const devs = cleaned
    .split(",")
    .map((d) => d.trim())
    .filter(Boolean);

  const VISIBLE = 2;
  const visibleDevs = devs.slice(0, VISIBLE);
  const hiddenDevs = devs.slice(VISIBLE);

  function makeDevLink(name) {
    const slug = name.toLowerCase().replace(/\s+/g, "-");
    const a = document.createElement("a");
    // Points to the developer's profile card/modal on the Developers page
    a.href = inContentFolder
      ? `developers/developers.html#dev-${slug}`
      : `app/views/content/developers/developers.html#dev-${slug}`;
    a.className = "dev-name-link";
    a.textContent = name;
    return a;
  }

  if (devs.length === 0) return;

  const byText = document.createElement("span");
  byText.className = "dev-inline";
  byText.textContent = "by ";
  wrap.appendChild(byText);

  visibleDevs.forEach((name, i) => {
    wrap.appendChild(makeDevLink(name));
    if (i < visibleDevs.length - 1) {
      const sep = document.createElement("span");
      sep.className = "dev-sep";
      sep.textContent = ", ";
      wrap.appendChild(sep);
    }
  });

  if (hiddenDevs.length === 0) return;

  // Hidden span: 3rd dev onward — starts hidden
  const hiddenSpan = document.createElement("span");
  hiddenSpan.className = "dev-hidden-names";
  hiddenSpan.style.display = "none";

  hiddenDevs.forEach((name, i) => {
    const sep = document.createElement("span");
    sep.className = "dev-sep";
    sep.textContent = ", ";
    hiddenSpan.appendChild(sep);
    hiddenSpan.appendChild(makeDevLink(name));
  });

  wrap.appendChild(hiddenSpan);

  // Space before toggle so it never butts up against the last dev name
  const toggleSpace = document.createElement("span");
  toggleSpace.className = "dev-sep";
  toggleSpace.textContent = " ";
  wrap.appendChild(toggleSpace);

  // "+N more" / "less" toggle
  const toggle = document.createElement("button");
  toggle.className = "dev-more-btn";
  toggle.textContent = `+${hiddenDevs.length} more`;
  wrap.appendChild(toggle);

  toggle.addEventListener("click", function (e) {
    e.stopPropagation();
    const expanded = hiddenSpan.style.display !== "none";
    if (expanded) {
      hiddenSpan.style.display = "none";
      toggle.textContent = `+${hiddenDevs.length} more`;
    } else {
      hiddenSpan.style.display = "inline";
      toggle.textContent = "less";
    }
  });
}

// ═══════════════════════════════════════════
// CAROUSEL FUNCTIONALITY
// ═══════════════════════════════════════════
function populateCarousel(excludeGameId) {
  const carouselTrack = document.getElementById("carouselTrack");

  // SAFETY CHECK: Exit if the track doesn't exist on this page
  if (!carouselTrack) return;

  carouselTrack.innerHTML = "";
  carouselIndex = 0;

  // Get all games except the currently open one, then shuffle randomly
  const allCards = Array.from(document.querySelectorAll(".game-card"));
  const availableGames = allCards
    .map((card) => card.dataset)
    .filter((data) => data.gameId && data.gameId !== excludeGameId)
    .sort(() => Math.random() - 0.5);

  availableGames.forEach((game) => {
    const carouselItem = document.createElement("div");
    carouselItem.className = "carousel-item";
    carouselItem.innerHTML = `
            <div class="carousel-card">
                <div class="carousel-thumb"></div>
                <div class="carousel-info">
                <div class="carousel-name">${game.title}</div>
                <div class="carousel-by">${game.devNames}</div>
                <div class="carousel-desc">${game.description}</div>
                <div class="carousel-rating">★ ${game.avgRating}</div>
                </div>
            </div>
            `;
    renderThumbInto(
      carouselItem.querySelector(".carousel-thumb"),
      game.thumbnail,
      game.title,
      game.gameId,
    );
    carouselItem.addEventListener("click", function () {
      openGameModal(game);
    });
    carouselTrack.appendChild(carouselItem);
  });

  updateCarouselPosition();
}

function getMaxIndex() {
  const totalItems = document.querySelectorAll(".carousel-item").length;
  return Math.max(0, totalItems - CAROUSEL_VISIBLE_ITEMS);
}

function updateCarouselPosition() {
  const carouselTrack = document.getElementById("carouselTrack");
  const container = document.querySelector(".carousel-container");

  // SAFETY CHECK: Exit if these elements are missing
  if (!carouselTrack || !container) return;

  const containerWidth = container.offsetWidth;
  const gap = 12;
  const itemWidth =
    (containerWidth - gap * (CAROUSEL_VISIBLE_ITEMS - 1)) /
    CAROUSEL_VISIBLE_ITEMS;
  const totalShift = carouselIndex * (itemWidth + gap);
  carouselTrack.style.transform = `translateX(-${totalShift}px)`;

  // Enable/disable buttons at the edges (SAFETY CHECK APPLIED)
  const maxIndex = getMaxIndex();
  if (carouselPrev) {
    carouselPrev.classList.toggle("disabled", carouselIndex <= 0);
  }
  if (carouselNext) {
    carouselNext.classList.toggle("disabled", carouselIndex >= maxIndex);
  }
}

// SAFETY CHECK: Only attach listeners if the buttons exist
if (carouselPrev) {
  carouselPrev.addEventListener("click", function () {
    if (carouselIndex > 0) {
      carouselIndex--;
      updateCarouselPosition();
    }
  });
}

if (carouselNext) {
  carouselNext.addEventListener("click", function () {
    const maxIndex = getMaxIndex();
    if (carouselIndex < maxIndex) {
      carouselIndex++;
      updateCarouselPosition();
    }
  });
}

// ═══════════════════════════════════════════
// NAVBAR — Active link toggling
// ═══════════════════════════════════════════
document.querySelectorAll(".nav-links a").forEach((link) => {
  link.addEventListener("click", function (e) {
    const href = this.getAttribute("href");
    if (href === "#") {
      e.preventDefault();
    }
    // Remove active from all links
    document
      .querySelectorAll(".nav-links a")
      .forEach((l) => l.classList.remove("active"));
    // Mark this one active
    this.classList.add("active");
  });
});

// ═══════════════════════════════════════════
// NAVBAR
// ═══════════════════════════════════════════
const avatarBtn = document.getElementById("avatarBtn");
const userDropdown = document.getElementById("userDropdown");
// The wrapper contains both the button and the dropdown
const avatarWrap = avatarBtn && avatarBtn.closest(".avatar-wrap");

// Prevents double-wiring if another script (ex. auth-guard.js) on
// this same page also sets up the dropdown toggle.
if (avatarBtn && userDropdown && avatarWrap && !window.__yaw8DropdownWired) {
  window.__yaw8DropdownWired = true;

  avatarBtn.addEventListener("click", function (e) {
    e.stopPropagation();
    const isOpen = userDropdown.classList.contains("open");
    userDropdown.classList.toggle("open", !isOpen);
    avatarBtn.classList.toggle("open", !isOpen);
  });

  // Close dropdown when clicking anywhere outside the avatar wrapper
  document.addEventListener("click", function (e) {
    if (!avatarWrap.contains(e.target)) {
      userDropdown.classList.remove("open");
      avatarBtn.classList.remove("open");
    }
  });

  // Close after dropdown item is clicked
  userDropdown.addEventListener("click", function (e) {
    e.stopPropagation();
    setTimeout(() => {
      userDropdown.classList.remove("open");
      avatarBtn.classList.remove("open");
    }, 120);
  });
}

// ═══════════════════════════════════════════
// SIGN OUT CONFIRMATION MODAL
// ═══════════════════════════════════════════
(function () {
  const signOutBtn = document.getElementById("signOutBtn");
  if (!signOutBtn) return;

  // Build the modal markup (reuses your existing modal.css classes)
  const confirmModal = document.createElement("div");
  confirmModal.className = "modal";
  confirmModal.id = "signOutConfirmModal";
  confirmModal.innerHTML = `
            <div class="modal-overlay"></div>
            <div class="modal-content" style="max-width: 380px; margin-top: 120px;">
                <div class="modal-inner" style="padding: 32px;">
                    <h1 style="font-size: 19px; font-weight: 800; color: #F5F7FA; margin-bottom: 8px;">Sign out?</h1>
                    <p style="font-size: 13px; color: #8892a4; line-height: 1.6; margin-bottom: 24px;">
                        You'll need to log back in to play, rate, and submit games.
                    </p>
                    <div style="display: flex; gap: 12px; justify-content: flex-end;">
                        <button class="form-btn-cancel" id="signOutCancelBtn">Cancel</button>
                        <button class="form-btn-submit" id="signOutConfirmBtn" style="background: #FF4FD8; box-shadow: 0 0 22px rgba(255, 79, 216, 0.3);">
                            <svg viewBox="0 0 24 24" style="width:16px;height:16px;fill:currentColor;flex-shrink:0;">
                                <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                            </svg>
                            Sign Out
                        </button>
                    </div>
                </div>
            </div>
        `;
  document.body.appendChild(confirmModal);

  const cancelBtn = confirmModal.querySelector("#signOutCancelBtn");
  const confirmBtn = confirmModal.querySelector("#signOutConfirmBtn");

  function openConfirmModal() {
    confirmModal.classList.add("active");
    document.body.style.overflow = "hidden";
  }

  function closeConfirmModal() {
    confirmModal.classList.remove("active");
    document.body.style.overflow = "auto";
  }

  // a. Open modal when "Sign Out" is pressed in the dropdown
  signOutBtn.addEventListener("click", function (e) {
    e.preventDefault();
    openConfirmModal();
  });

  cancelBtn.addEventListener("click", closeConfirmModal);
  confirmModal
    .querySelector(".modal-overlay")
    .addEventListener("click", closeConfirmModal);

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && confirmModal.classList.contains("active")) {
      closeConfirmModal();
    }
  });

  // b. Confirm button in modal calls AuthController::logout()
  confirmBtn.addEventListener("click", function () {
    localStorage.clear();
    sessionStorage.setItem("show_auth_loader", "true");
    window.location.href = "?url=logout";
  });
})();

// ═══════════════════════════════════════════
// SUBMIT GAME MODAL
// ═══════════════════════════════════════════
const submitGameModal = document.getElementById("submit-game-modal");
const submitModalClose = document.getElementById("submitModalClose");
const submitCancelBtn = document.getElementById("submitCancelBtn");
const btnSubmitGame = document.querySelector(".btn-submit-game");

function openSubmitModal() {
  submitGameModal.classList.add("active");
  submitGameModal.scrollTop = 0;
  document.body.style.overflow = "hidden";
}

function closeSubmitModal() {
  submitGameModal.classList.remove("active");
  document.body.style.overflow = "auto";
}

if (btnSubmitGame) btnSubmitGame.addEventListener("click", openSubmitModal);
if (submitGameModal) {
  if (submitModalClose) {
    submitModalClose.addEventListener("click", closeSubmitModal);
  }
  if (submitCancelBtn) {
    submitCancelBtn.addEventListener("click", closeSubmitModal);
  }
  const submitOverlay = submitGameModal.querySelector(".modal-overlay");
  if (submitOverlay) {
    submitOverlay.addEventListener("click", closeSubmitModal);
  }
}

document.addEventListener("keydown", function (e) {
  if (e.key === "Escape" && submitGameModal.classList.contains("active")) {
    closeSubmitModal();
  }
});

// Also wire up footer "Submit Your Game" link if present
document.querySelectorAll("a").forEach((link) => {
  if (link.textContent.trim() === "Submit Your Game") {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      openSubmitModal();
    });
  }
});

// ═══════════════════════════════════════════
// LOADING SCREEN
// ═══════════════════════════════════════════
(function () {
  const loadingScreen = document.getElementById("loading-screen");
  const loaderBarFill = document.getElementById("loaderBarFill");
  const loaderText = document.getElementById("loaderText");

  if (!loadingScreen) return;

  // Check if an auth action (login, signup, or signout) flagged a loading screen request
  const showLoader = sessionStorage.getItem("show_auth_loader");

  if (!showLoader) {
    // If no auth action happened, hide the loader screen immediately
    loadingScreen.style.display = "none";
    document.body.style.overflow = "auto";
    return;
  }

  // Clear the flag so it doesn't show again on simple page reloads
  sessionStorage.removeItem("show_auth_loader");

  let progress = 0;
  let isLoaded = false;

  const interval = setInterval(function () {
    progress += Math.random() * 7;
    if (progress > 90) progress = 90;
    if (loaderBarFill) loaderBarFill.style.width = progress + "%";
    if (loaderText)
      loaderText.textContent = `Loading… ${Math.floor(progress)}%`;
  }, 400);

  function hideLoadingScreen() {
    if (isLoaded) return;
    isLoaded = true;

    clearInterval(interval);
    if (loaderBarFill) loaderBarFill.style.width = "100%";
    if (loaderText) loaderText.textContent = "Loading...";

    setTimeout(function () {
      loadingScreen.classList.add("loaded");
      document.body.style.overflow = "auto";
    }, 500);
  }

  if (document.readyState === "complete") {
    hideLoadingScreen();
  } else {
    window.addEventListener("load", hideLoadingScreen);
    setTimeout(hideLoadingScreen, 3000);
  }

  document.body.style.overflow = "hidden";
})();

// ═══════════════════════════════════════════
// EXPLORE NOW.
// ═══════════════════════════════════════════
const exploreLink = document.querySelector(
  '.btn-primary a[href="#featured-games"]',
);
const featuredSection = document.getElementById("featured-games");

if (exploreLink && featuredSection) {
  exploreLink.addEventListener("click", function (e) {
    e.preventDefault();
    const offset = 100;
    const targetY =
      featuredSection.getBoundingClientRect().top + window.scrollY - offset;
    window.scrollTo({ top: targetY, behavior: "smooth" });
  });
}