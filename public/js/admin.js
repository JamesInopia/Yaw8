// ═══════════════════════════════════════════════════
// ADMIN.JS — dashboard (?url=admin)
// Tabs (Games / Users) switch in place, so the admin never leaves this page.
// ═══════════════════════════════════════════════════
(function () {
  const UI = window.AdminUI;
  const tbody = document.getElementById("gamesTableBody");
  if (!UI || !tbody) return;

  const sortSelect = document.getElementById("sortSelect");
  const filterSelect = document.getElementById("filterSelect");
  const searchInput = document.getElementById("gameSearch");
  const countEl = document.getElementById("gamesCount");
  const menu = document.getElementById("actionMenu");
  const menuDetails = document.getElementById("menuDetails");
  const menuStatusButtons = menu.querySelectorAll("[data-set-status]");

  let games = [];
  let menuGameId = null;
  let menuAnchor = null;
  let busy = false;

  // ─────────────────────────────────────────
  // TABS  (Games | Users) — no page navigation
  // ─────────────────────────────────────────
  const tabs = document.querySelectorAll(".admin-tab");

  function activateTab(name) {
    tabs.forEach((tab) => {
      const active = tab.dataset.tab === name;
      tab.classList.toggle("active", active);
      tab.setAttribute("aria-selected", active ? "true" : "false");
      tab.tabIndex = active ? 0 : -1;
      document.getElementById("tab-" + tab.dataset.tab).hidden = !active;
    });
    closeMenu();
    // lets admin-users.js load the Users table the first time its tab is opened
    document.dispatchEvent(new CustomEvent("admin:tab", { detail: name }));
  }

  tabs.forEach((tab) => {
    tab.addEventListener("click", () => {
      activateTab(tab.dataset.tab);
      history.replaceState(null, "", "#" + tab.dataset.tab); // refresh keeps the same tab
    });

    // Left / Right arrow keys move between tabs
    tab.addEventListener("keydown", (e) => {
      if (e.key !== "ArrowLeft" && e.key !== "ArrowRight") return;
      const list = Array.from(tabs);
      const next = list[(list.indexOf(tab) + (e.key === "ArrowRight" ? 1 : list.length - 1)) % list.length];
      next.click();
      next.focus();
    });
  });

  activateTab(location.hash === "#users" ? "users" : "games");

  // ─────────────────────────────────────────
  // STAT CARDS
  // ─────────────────────────────────────────
  function renderStats(stats) {
    if (!stats) return;
    const map = {
      statTotalUsers: stats.totalUsers,
      statTotalGames: stats.totalGames,
      statPublished: stats.published,
      statUnderReview: stats.underReview,
      statUnlisted: stats.unlisted,
    };
    Object.keys(map).forEach((id) => {
      const el = document.getElementById(id);
      if (el && map[id] !== undefined) el.textContent = map[id];
    });
  }

  // ─────────────────────────────────────────
  // GAMES TABLE: filter + search + sort + render
  // ─────────────────────────────────────────
  function visibleGames() {
    const filter = filterSelect.value;
    const query = searchInput.value.trim().toLowerCase();

    let list = games.filter((g) => {
      if (filter !== "all" && g.status !== filter) return false;
      if (!query) return true;
      return (
        String(g.title || "").toLowerCase().includes(query) ||
        String(g.devNames || "").toLowerCase().includes(query)
      );
    });

    const byName = (a, b) => String(a.title).localeCompare(String(b.title), undefined, { sensitivity: "base" });
    const byDate = (a, b) => new Date(String(a.dateReleased).replace(" ", "T")) - new Date(String(b.dateReleased).replace(" ", "T"));

    switch (sortSelect.value) {
      case "name-asc":  list.sort(byName); break;
      case "name-desc": list.sort((a, b) => byName(b, a)); break;
      case "oldest":    list.sort(byDate); break;
      default:          list.sort((a, b) => byDate(b, a)); // newest first
    }

    return list;
  }

  function render() {
    const list = visibleGames();

    if (list.length === 0) {
      const msg = games.length === 0 ? "There are no games yet." : "No games match your filters.";
      tbody.innerHTML = '<tr><td colspan="7" class="admin-table-message">' + msg + "</td></tr>";
      countEl.textContent = "";
      return;
    }

    tbody.innerHTML = list.map((g, i) => {
      const info = UI.statusInfo(g.status);
      const title = UI.esc(g.title);
      return `
        <tr data-game-id="${g.gameId}">
          <td class="col-num">${i + 1}</td>
          <td class="col-name"><a href="?url=admin/game&gameId=${g.gameId}">${title}</a></td>
          <td class="col-dev" title="${UI.esc(g.devNames)}">${g.devNames ? UI.esc(g.devNames) : "—"}</td>
          <td class="col-rating">${UI.ratingHtml(g.avgRating, g.ratingCount)}</td>
          <td><span class="admin-status ${info.cls}">${info.label}</span></td>
          <td class="col-center"><span class="report-count ${g.openReports > 0 ? "has-reports" : ""}" title="Open reports">${g.openReports}</span></td>
          <td class="col-action">
            <button type="button" class="action-btn" data-game-id="${g.gameId}"
                    aria-haspopup="menu" aria-expanded="false" aria-label="Actions for ${title}">
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm12 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm-6 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
            </button>
          </td>
        </tr>`;
    }).join("");

    countEl.textContent = "Showing " + list.length + " of " + games.length + " game" + (games.length === 1 ? "" : "s");
  }

  [sortSelect, filterSelect].forEach((el) => el.addEventListener("change", () => { closeMenu(); render(); }));
  searchInput.addEventListener("input", () => { closeMenu(); render(); });

  // ─────────────────────────────────────────
  // "⋯" ACTION MENU
  // ─────────────────────────────────────────
  function openMenu(button) {
    const gameId = Number(button.dataset.gameId);
    const game = games.find((g) => g.gameId === gameId);
    if (!game) return;

    closeMenu();
    menuGameId = gameId;
    menuAnchor = button;
    button.setAttribute("aria-expanded", "true");

    menuDetails.href = "?url=admin/game&gameId=" + gameId;
    // The current status can't be "changed to" itself
    menuStatusButtons.forEach((b) => { b.disabled = b.dataset.setStatus === game.status; });

    UI.positionMenu(menu, button);

    const first = menu.querySelector(".admin-menu-item:not(:disabled)");
    if (first) first.focus();
  }

  function closeMenu() {
    if (menu.hidden) return;
    menu.hidden = true;
    if (menuAnchor) {
      menuAnchor.setAttribute("aria-expanded", "false");
      menuAnchor = null;
    }
    menuGameId = null;
  }

  tbody.addEventListener("click", (e) => {
    const button = e.target.closest(".action-btn");
    if (!button) return;
    e.stopPropagation();
    if (menuAnchor === button) { closeMenu(); return; }
    openMenu(button);
  });

  document.addEventListener("click", (e) => {
    if (!menu.hidden && !menu.contains(e.target)) closeMenu();
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && !menu.hidden) {
      const anchor = menuAnchor;
      closeMenu();
      if (anchor) anchor.focus();
    }
  });

  window.addEventListener("resize", closeMenu);
  window.addEventListener("scroll", closeMenu, true);

  // ─────────────────────────────────────────
  // CHANGE STATUS (confirm publication / under review / unlist)
  // ─────────────────────────────────────────
  menuStatusButtons.forEach((button) => {
    button.addEventListener("click", async () => {
      if (busy || menuGameId === null) return;
      const gameId = menuGameId;
      const status = button.dataset.setStatus;
      closeMenu();
      busy = true;

      try {
        const result = await UI.api("api/admin/game/status", {
          method: "POST",
          body: { gameId: gameId, status: status },
        });
        const game = games.find((g) => g.gameId === gameId);
        if (game) game.status = result.status;
        renderStats(result.stats);
        render();
        UI.toast(UI.STATUS_SUCCESS_TEXT[result.status] || "Status updated.");
      } catch (err) {
        UI.toast(err.message, true);
      } finally {
        busy = false;
      }
    });
  });

  // ─────────────────────────────────────────
  // LOAD
  // ─────────────────────────────────────────
  async function load() {
    try {
      const data = await UI.api("api/admin/games");
      games = data.games || [];
      renderStats(data.stats);
      render();
    } catch (err) {
      tbody.innerHTML = '<tr><td colspan="7" class="admin-table-message" style="color:var(--admin-danger)">' +
        UI.esc(err.message) + (err.status === 401 ? ' <a href="?url=auth" style="color:var(--cyan);text-decoration:underline">Log in</a>' : "") +
        "</td></tr>";
      console.error("Admin: could not load games -", err);
    }
  }

  load();
})();
