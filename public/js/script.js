// ═══════════════════════════════════════════
// MODAL FUNCTIONALITY
// ═══════════════════════════════════════════
const modal = document.getElementById("game-modal");
const modalClose = document.querySelector(".modal-close");
const carouselPrev = document.querySelector(".carousel-prev");
const carouselNext = document.querySelector(".carousel-next");
const gamePagePath = "?url=games/player";
const CAROUSEL_VISIBLE_ITEMS = 4; // number of cards shown at a time
let currentGameId = null;
let carouselIndex = 0; // current starting card index

// Open modal only when clicking the card (not the play button)
document.querySelectorAll(".game-card").forEach((card) => {
  card.addEventListener("click", function (e) {
    if (e.target.closest(".btn-play") || e.target.closest(".dev-name-link")) {
      return;
    }

    openGameModal(this.dataset);
  });
});

function openGameModal(gameData) {
  currentGameId = gameData.gameId;

  // Update modal content
  document.getElementById("modalGameName").textContent = gameData.title;

  try {
    const response = await fetch(`?url=games/details&gameId=${gameData.gameId}`);
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
    const modalThumb = document.getElementById("modalThumbnail");
    modalThumb.innerHTML = `<img src="${fullGame.thumbnail}" alt="${fullGame.title}">`;

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
// PLAY BUTTON — send the user to the game page
// ═══════════════════════════════════════════
// Path detection + resolver
const pathname = window.location.pathname;
const inContentFolder = /\/app\/views\/content\//.test(pathname);

// Updated navigation helper
function goToGamePage(gameId) {
  window.location.href = `${gamePagePath}?game=${gameId}`;
}

document.addEventListener("click", function (e) {
  const playBtn = e.target.closest(".btn-play");
  if (!playBtn) return;

  const card = playBtn.closest("[data-game-id]");
  if (!card) return;

  goToGamePage(card.getAttribute("data-game-id"));
});

const modalPlayBtn = document.querySelector(".btn-play-modal");
if (modalPlayBtn) {
  modalPlayBtn.addEventListener("click", function () {
    if (!currentGameId) return;
    goToGamePage(currentGameId);
  });
}

// ─────────────────────────────────────────────
// DEVELOPER DISPLAY
// Always shows first 2 devs. The 3rd dev and beyond
// are hidden behind "+N more" and revealed on click.
// Each dev name is a hoverable link to their profile.
// ─────────────────────────────────────────────
function renderDevelopers(developerString) {
  const wrap = document.getElementById("modalDeveloperWrap");
  wrap.innerHTML = "";

  // Strip leading "by " and split on comma
  const cleaned = developerString.replace(/^by\s+/i, "");
  const devs = cleaned
    .split(",")
    .map((d) => d.trim())
    .filter(Boolean);

  const VISIBLE = 2; // always-visible dev count
  const visibleDevs = devs.slice(0, VISIBLE);
  const hiddenDevs = devs.slice(VISIBLE); // 3rd dev onward is hidden

  // Helper: build a hoverable dev link
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

  // "by " prefix
  const byText = document.createElement("span");
  byText.className = "dev-inline";
  byText.textContent = "by ";
  wrap.appendChild(byText);

  // Visible dev links (first 2), comma-separated
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
// CAROUSEL FUNCTIONALITY (RECOMMENDED GAMES)
// Shows exactly 4 cards at a time. Prev/Next move
// one full page (4 cards) at a time.
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
                <div class="carousel-thumb"><img src="${game.thumbnail}" alt=${game.title}></div>
                <div class="carousel-info">
                <div class="carousel-name">${game.title}</div>
                <div class="carousel-by">${game.devNames}</div>
                <div class="carousel-desc">${game.description}</div>
                <div class="carousel-rating">★ ${game.avgRating}</div>
                </div>
            </div>
            `;
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
// Clicking a nav link marks it active and removes
// the active class from the previously active link.
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
// NAVBAR — User dropdown toggle
// Clicking the avatar button opens/closes the
// dropdown. Clicking anywhere else closes it.
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
// LOADING SCREEN (Triggers only on Auth transitions)
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
// EXPLORE NOW — smooth scroll to Featured Games
// Scrolls a bit short of the very top of the
// section so the floating navbar doesn't cover it.
// ═══════════════════════════════════════════
const exploreLink = document.querySelector(
  '.btn-primary a[href="#featured-games"]',
);
const featuredSection = document.getElementById("featured-games");

if (exploreLink && featuredSection) {
  exploreLink.addEventListener("click", function (e) {
    e.preventDefault();
    const offset = 100; // breathing room below the floating navbar
    const targetY =
      featuredSection.getBoundingClientRect().top + window.scrollY - offset;
    window.scrollTo({ top: targetY, behavior: "smooth" });
  });
}
