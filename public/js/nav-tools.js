// ═══════════════════════════════════════════
// NAV TOOLS
//   1. Navbar search  — live results for games AND developers
//   2. [data-go] buttons — "Submit Game" / "Surprise Me!" on the home sidebar
// ═══════════════════════════════════════════

// ── 2. Buttons that just go somewhere ──────────────────────────────
document.querySelectorAll("[data-go]").forEach(function (btn) {
  btn.addEventListener("click", function () {
    window.location.href = btn.getAttribute("data-go");
  });
});

// ── 1. Navbar search ───────────────────────────────────────────────
(function () {
  const input = document.getElementById("searchInput");
  const bar = input ? input.closest(".search-bar") : null;
  if (!input || !bar) return;

  const panel = document.createElement("div");
  panel.className = "search-results";
  panel.id = "searchResults";
  panel.setAttribute("role", "listbox");
  panel.hidden = true;
  bar.appendChild(panel);

  input.setAttribute("autocomplete", "off");
  input.setAttribute("aria-controls", "searchResults");

  let timer = null;
  let controller = null;
  let lastQuery = "";

  function esc(value) {
    return String(value == null ? "" : value)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  // Thumbnails are stored as "uploads/thumbnails/<file>" (relative to /public)
  function thumbUrl(path) {
    if (!path) return "";
    return "uploads/thumbnails/" + String(path).split("/").pop();
  }

  function initials(name) {
    const parts = String(name || "?").trim().split(/\s+/);
    return parts.map((p) => p[0]).join("").slice(0, 2).toUpperCase();
  }

  function gameRow(g) {
    const img = g.thumbnail
      ? '<img src="' + esc(thumbUrl(g.thumbnail)) + '" alt="" loading="lazy">'
      : esc((g.title || "?").charAt(0).toUpperCase());
    const bits = [];
    if (g.genreNames) bits.push(g.genreNames);
    if (g.devNames) bits.push("by " + g.devNames);
    if (g.downloadOnly) bits.push("download");

    return (
      '<a class="sr-item" role="option" href="?url=games/player&gameId=' + encodeURIComponent(g.gameId) + '">' +
      '<span class="sr-thumb">' + img + "</span>" +
      '<span class="sr-text"><span class="sr-title">' + esc(g.title) + "</span>" +
      '<span class="sr-sub">' + esc(bits.join(" · ")) + "</span></span></a>"
    );
  }

  function devRow(d) {
    const label = d.name || d.username;
    const sub = "@" + d.username + " · " + d.games + (d.games === 1 ? " game" : " games");

    return (
      '<a class="sr-item" role="option" href="?url=developers#dev-' + encodeURIComponent(d.id) + '">' +
      '<span class="sr-avatar">' + esc(initials(label)) + "</span>" +
      '<span class="sr-text"><span class="sr-title">' + esc(label) + "</span>" +
      '<span class="sr-sub">' + esc(sub) + "</span></span></a>"
    );
  }

  function render(data, query) {
    const games = data.games || [];
    const devs = data.developers || [];

    if (!games.length && !devs.length) {
      panel.innerHTML = '<div class="sr-empty">No games or developers found for “' + esc(query) + "”.</div>";
    } else {
      let html = "";
      if (games.length) html += '<div class="sr-label">Games</div>' + games.map(gameRow).join("");
      if (devs.length) html += '<div class="sr-label">Developers</div>' + devs.map(devRow).join("");
      panel.innerHTML = html;
    }
    panel.hidden = false;
  }

  function close() {
    panel.hidden = true;
    clearTimeout(timer);
    if (controller) controller.abort();
  }

  function runSearch(query) {
    if (controller) controller.abort();
    controller = new AbortController();
    lastQuery = query;

    fetch("?url=api/search&q=" + encodeURIComponent(query), {
      headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" },
      signal: controller.signal,
    })
      .then(function (res) {
        return res.json().then(function (data) {
          if (!res.ok || !data || data.success === false) {
            throw new Error((data && data.message) || "Search failed.");
          }
          return data;
        });
      })
      .then(function (data) {
        // ignore answers for something the person has already typed over
        if (input.value.trim() === query) render(data, query);
      })
      .catch(function (err) {
        if (err.name === "AbortError") return;
        panel.innerHTML = '<div class="sr-empty">Couldn’t search right now. Please try again.</div>';
        panel.hidden = false;
      });
  }

  input.addEventListener("input", function () {
    const query = input.value.trim();
    clearTimeout(timer);

    if (!query) {
      close();
      return;
    }
    timer = setTimeout(function () { runSearch(query); }, 220);
  });

  input.addEventListener("focus", function () {
    if (panel.innerHTML && input.value.trim()) panel.hidden = false;
  });

  // ↑ ↓ move through results, Enter opens the highlighted one (or the first), Esc closes
  input.addEventListener("keydown", function (e) {
    const items = Array.from(panel.querySelectorAll(".sr-item"));

    if (e.key === "Escape") {
      close();
      input.blur();
      return;
    }

    if (e.key === "ArrowDown" || e.key === "ArrowUp") {
      if (!items.length || panel.hidden) return;
      e.preventDefault();
      const current = items.findIndex(function (el) { return el.classList.contains("active"); });
      const step = e.key === "ArrowDown" ? 1 : -1;
      const next = current === -1 ? (step === 1 ? 0 : items.length - 1) : (current + step + items.length) % items.length;
      items.forEach(function (el) { el.classList.remove("active"); });
      items[next].classList.add("active");
      items[next].scrollIntoView({ block: "nearest" });
      return;
    }

    if (e.key === "Enter") {
      e.preventDefault();
      const target = panel.querySelector(".sr-item.active") || items[0];
      if (target && !panel.hidden && input.value.trim() === lastQuery) {
        window.location.href = target.getAttribute("href");
      } else if (input.value.trim()) {
        // results aren't in yet — run the search now instead of waiting for the debounce
        clearTimeout(timer);
        runSearch(input.value.trim());
      }
    }
  });

  document.addEventListener("click", function (e) {
    if (!bar.contains(e.target)) panel.hidden = true;
  });
})();
