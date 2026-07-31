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

      const hasThumbnail = Boolean(thumbnail);
      const colorClass = colorClasses[index % colorClasses.length];
      const thumbClass = hasThumbnail ? "sp-forest" : colorClass;
      card.innerHTML = `
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
