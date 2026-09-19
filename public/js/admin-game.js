// ═══════════════════════════════════════════════════
// ADMIN-GAME.JS — game details page (?url=admin/game&gameId=N)
// Read-only game details + reports the admin can resolve.
// ═══════════════════════════════════════════════════
(function () {
  const UI = window.AdminUI;
  const page = document.getElementById("adminGamePage");
  if (!UI || !page) return;

  const gameId = Number(page.dataset.gameId);
  let game = null;
  let reports = [];
  let busy = false;

  const $ = (id) => document.getElementById(id);
  const statusButtons = document.querySelectorAll("[data-set-status]");
  const resolveAllBtn = $("resolveAllBtn");
  const dialog = $("confirmDialog");

  // ─────────────────────────────────────────
  // GAME DETAILS
  // ─────────────────────────────────────────
  function renderThumb() {
    const box = $("detailThumb");
    const url = UI.thumbUrl(game.thumbnail);
    if (url) {
      box.innerHTML = '<img src="' + UI.esc(url) + '" alt="' + UI.esc(game.title) + ' thumbnail">';
      // Broken/missing file -> fall back to the title card
      box.querySelector("img").addEventListener("error", () => {
        box.innerHTML = '<div class="thumb-fallback">' + UI.esc(String(game.title).toUpperCase()) + "</div>";
      });
    } else {
      box.innerHTML = '<div class="thumb-fallback">' + UI.esc(String(game.title).toUpperCase()) + "</div>";
    }
  }

  function renderStatus() {
    const info = UI.statusInfo(game.status);
    const badge = $("detailStatusBadge");
    badge.className = "admin-status " + info.cls;
    badge.textContent = info.label;
    badge.hidden = false;

    // The status the game already has can't be "set" again
    statusButtons.forEach((b) => { b.disabled = busy || b.dataset.setStatus === game.status; });
  }

  function renderDetails() {
    document.title = game.title + " — Admin — YAW8";
    renderThumb();

    $("dTitle").textContent = game.title;
    $("dRating").innerHTML = UI.ratingHtml(game.avgRating, game.ratingCount) +
      (game.ratingCount ? ' <span class="rating-none">(' + game.ratingCount + ")</span>" : "");
    $("dDevs").textContent = game.devNames || "—";
    $("dPlays").textContent = Number(game.totalPlays || 0).toLocaleString();
    $("dProjectType").textContent = game.projectType;
    $("dUpdated").textContent = UI.formatDate(game.lastUpdated, true);
    $("dGenre").textContent = game.genreNames || "—";
    $("dReleased").textContent = UI.formatDate(game.dateReleased);
    $("dDescription").textContent = game.description || "No description provided.";
    $("dControls").textContent = game.controls || "No controls provided.";
    $("previewLink").href = "?url=games/player&gameId=" + game.gameId;

    renderStatus();
  }

  // ─────────────────────────────────────────
  // REPORTS
  // ─────────────────────────────────────────
  function openCount() {
    return reports.filter((r) => r.status === "open").length;
  }

  function renderReports() {
    const open = openCount();
    $("reportsOpenCount").textContent = "(" + open + " open" + (reports.length !== open ? " · " + reports.length + " total" : "") + ")";
    resolveAllBtn.disabled = busy || open === 0;

    const list = $("reportsList");

    if (reports.length === 0) {
      list.innerHTML = '<div class="reports-empty">No reports have been made for this game. 🎉</div>';
      return;
    }

    list.innerHTML = reports.map((r) => {
      const name = r.reporterUsername || "Deleted user";
      const label = r.reporterUsername ? "@" + name : name;
      const initial = UI.esc(name.charAt(0).toUpperCase());
      const isOpen = r.status === "open";
      const body = r.details && r.details.trim()
        ? '<p class="report-body">' + UI.esc(r.details) + "</p>"
        : '<p class="report-body empty">No details provided.</p>';

      const foot = isOpen
        ? '<button type="button" class="admin-btn admin-btn-green admin-btn-sm" data-resolve="' + r.reportId + '">Resolve</button>'
        : "<span>✓ Resolved " + UI.formatDate(r.resolvedAt, true) +
          (r.resolvedByUsername ? " by @" + UI.esc(r.resolvedByUsername) : "") + "</span>";

      return `
        <article class="report-item ${isOpen ? "" : "resolved"}">
          <div class="report-head">
            <span class="report-avatar" aria-hidden="true">${initial}</span>
            <span class="report-user">${UI.esc(label)}</span>
            <span class="report-reason">${UI.esc(r.reason || "Other")}</span>
            <span class="report-date">${UI.formatDate(r.createdAt, true)}</span>
          </div>
          ${body}
          <div class="report-foot">${foot}</div>
        </article>`;
    }).join("");
  }

  // Resolve one report
  $("reportsList").addEventListener("click", async (e) => {
    const button = e.target.closest("[data-resolve]");
    if (!button || busy) return;

    busy = true;
    button.disabled = true;
    try {
      const result = await UI.api("api/admin/report/resolve", {
        method: "POST",
        body: { reportId: Number(button.dataset.resolve) },
      });
      reports = result.reports;
      UI.toast("Report resolved.");
    } catch (err) {
      UI.toast(err.message, true);
    } finally {
      busy = false;
      renderReports();
    }
  });

  // ─────────────────────────────────────────
  // RESOLVE ALL (with confirmation)
  // ─────────────────────────────────────────
  let lastFocus = null;

  function openDialog() {
    const open = openCount();
    if (open === 0) return;
    $("confirmText").textContent =
      "This will mark all " + open + " open report" + (open === 1 ? "" : "s") +
      ' on "' + game.title + '" as resolved. Do you want to continue?';
    lastFocus = document.activeElement;
    dialog.hidden = false;
    $("confirmCancel").focus();
  }

  function closeDialog() {
    dialog.hidden = true;
    if (lastFocus && lastFocus.focus) lastFocus.focus();
  }

  resolveAllBtn.addEventListener("click", openDialog);
  dialog.querySelectorAll("[data-close-dialog]").forEach((el) => el.addEventListener("click", closeDialog));

  document.addEventListener("keydown", (e) => {
    if (dialog.hidden) return;
    if (e.key === "Escape") { closeDialog(); return; }
    // keep Tab inside the dialog
    if (e.key === "Tab") {
      const focusable = dialog.querySelectorAll("button:not(:disabled)");
      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    }
  });

  $("confirmOk").addEventListener("click", async () => {
    if (busy) return;
    busy = true;
    $("confirmOk").disabled = true;
    try {
      const result = await UI.api("api/admin/reports/resolve-all", {
        method: "POST",
        body: { gameId: gameId },
      });
      reports = result.reports;
      UI.toast("All reports resolved.");
    } catch (err) {
      UI.toast(err.message, true);
    } finally {
      busy = false;
      $("confirmOk").disabled = false;
      dialog.hidden = true;
      lastFocus = null;
      renderReports();
    }
  });

  // ─────────────────────────────────────────
  // STATUS BUTTONS (same actions as the dashboard's "⋯" menu)
  // ─────────────────────────────────────────
  statusButtons.forEach((button) => {
    button.addEventListener("click", async () => {
      if (busy || !game) return;
      busy = true;
      statusButtons.forEach((b) => { b.disabled = true; });
      try {
        const result = await UI.api("api/admin/game/status", {
          method: "POST",
          body: { gameId: gameId, status: button.dataset.setStatus },
        });
        game.status = result.status;
        UI.toast(UI.STATUS_SUCCESS_TEXT[result.status] || "Status updated.");
      } catch (err) {
        UI.toast(err.message, true);
      } finally {
        busy = false;
        renderStatus();
      }
    });
  });

  // ─────────────────────────────────────────
  // LOAD
  // ─────────────────────────────────────────
  async function load() {
    try {
      const data = await UI.api("api/admin/game&gameId=" + gameId);
      game = data.game;
      reports = data.reports || [];

      renderDetails();
      renderReports();

      $("detailLoading").hidden = true;
      $("detailBody").hidden = false;
      $("reportsPanel").hidden = false;
    } catch (err) {
      $("detailLoading").hidden = true;
      const box = $("detailError");
      box.innerHTML = UI.esc(err.message) +
        (err.status === 401 ? ' <a href="?url=auth">Log in</a>' : ' <a href="?url=admin#games">Back to dashboard</a>');
      box.hidden = false;
      console.error("Admin: could not load game -", err);
    }
  }

  load();
})();
