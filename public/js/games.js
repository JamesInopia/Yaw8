// ═══════════════════════════════════════════════════
// GAMES.JS
// ═══════════════════════════════════════════════════
// ═══════════════════════════════════════════
// ALL GAMES GRID + GENRE FILTER
// ═══════════════════════════════════════════
(async function initAllGamesGrid() {
  const grid = document.getElementById("allGamesGrid");
  const chipsWrap = document.getElementById("genreFilters");
  if (!grid) return;

  let allGames = [];

  try {
    const response = await fetch("?url=games/all", { headers: authHeaders() });
    if (!response.ok) {
      throw new Error(`An error has occured. Status: ${response.status}`);
    }
    allGames = await response.json();
  } catch (error) {
    console.error(
      "Loading error: Games cannot be loaded from the database - ",
      error,
    );
    grid.innerHTML = `<p class="error-msg">Unable to load games at the moment. Please try again later...</p>`;
    return;
  }

  const genres = [
    "All",
    ...new Set(allGames.map((g) => g.genreNames).filter(Boolean)),
  ];
  let activeGenre = "All";

  const colorClasses = ["gt-pixel-drift", "gt-tower-tactics", "gt-box-jumper", "gt-color-clash"];

  function renderGrid() {
    grid.innerHTML = "";

    const filteredGames = allGames.filter((g) => {
      const genre = g.genreNames;
      return activeGenre === "All" || genre === activeGenre;
    });

    filteredGames.forEach((game, index) => {
      const card = document.createElement("div");
      card.className = "game-card";

      const gameId = game.gameId || "";
      const title = game.title || "";
      const description = game.description || "";
      const controls = game.controls || "";
      const totalPlays = game.totalPlays || 0;
      const dateReleased = game.dateReleased || "";
      const lastUpdated = game.lastUpdated || "";
      const thumbnail = game.thumbnail || "";
      const genre = game.genreNames || "";
      const rating = game.avgRating || "0.0";
      const devNames = game.devNames || "";

      card.setAttribute("data-game-id", gameId);
      card.setAttribute("data-title", title);
      card.setAttribute("data-description", description);
      card.setAttribute("data-controls", controls);
      card.setAttribute("data-total-plays", totalPlays);
      card.setAttribute("data-date-released", dateReleased);
      card.setAttribute("data-last-updated", lastUpdated);
      card.setAttribute("data-thumbnail", thumbnail);
      card.setAttribute("data-genre-names", genre);
      card.setAttribute("data-avg-rating", rating);
      card.setAttribute("data-dev-names", devNames);
      card.setAttribute("data-access-type", game.accessType || "online");

      const hasThumbnail = Boolean(thumbnail);
      const colorClass = colorClasses[index % colorClasses.length];
      const thumbClass = hasThumbnail ? "sp-forest" : colorClass;
      card.innerHTML = `
            <div class="game-quick-actions">
                <button type="button" class="game-quick-btn game-favorite-btn" title="Favorite" aria-label="Favorite">
                    <svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                </button>
                <button type="button" class="game-quick-btn game-report-btn" title="Report" aria-label="Report">
                    <svg viewBox="0 0 24 24"><path d="M14.4 6L14 4H5v17h2v-7h5.6l.4 2h7V6z"/></svg>
                </button>
            </div>
            <div class="game-thumb ${thumbClass}">
                ${
                  hasThumbnail
                    ? `<img src="${thumbnail}" alt="${title}">`
                    : `<div class="game-thumb-title">${title.toUpperCase().split(" ").join("<br>")}</div>`
                }
            </div>
            <div class="game-info">
                <div class="game-meta">
                <span class="game-name">${title}</span>
                <span class="game-more">···</span>
                </div>
                <div class="game-genre">${genre}</div>
                <div class="game-footer">
                <span class="stars">★ ${rating}</span>
                <button class="btn-play">PLAY</button>
                </div>
            </div>
            `;
      card.addEventListener("click", function (e) {
        if (e.target.closest(".btn-play") || e.target.closest(".dev-name-link"))
          return;
        if (typeof openGameModal === "function") {
          openGameModal(this.dataset);
        }
      });

      if (typeof wireCardQuickActions === "function") {
        wireCardQuickActions(card);
      }

      grid.appendChild(card);
    });
  }

  function renderFilterChips() {
    if (!chipsWrap) return;
    chipsWrap.innerHTML = "";

    genres.forEach((genre) => {
      const chip = document.createElement("button");
      chip.className = "filter-chip" + (genre === activeGenre ? " active" : "");
      chip.textContent = genre;
      chip.addEventListener("click", function () {
        activeGenre = genre;
        chipsWrap
          .querySelectorAll(".filter-chip")
          .forEach((c) => c.classList.remove("active"));
        chip.classList.add("active");
        renderGrid();
      });
      chipsWrap.appendChild(chip);
    });
  }

  renderGrid();
  renderFilterChips();
})();
