// ═══════════════════════════════════════════════════
// ADMIN-COMMON.JS
// Helpers shared by the admin dashboard (admin.js) and the
// admin game-details page (admin-game.js).
// Relies on authHeaders() from auth.js.
// ═══════════════════════════════════════════════════
window.AdminUI = (function () {
  const STATUS = {
    published:    { label: "Published",    cls: "published" },
    under_review: { label: "Under Review", cls: "review" },
    unlisted:     { label: "Unlisted",     cls: "unlisted" },
  };

  const STATUS_SUCCESS_TEXT = {
    published:    "Game published.",
    under_review: "Game put under review.",
    unlisted:     "Game unlisted.",
  };

  function statusInfo(status) {
    return STATUS[status] || STATUS.under_review;
  }

  // Everything that comes from the database is escaped before it goes into innerHTML
  function esc(value) {
    return String(value == null ? "" : value)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  // MySQL "YYYY-MM-DD HH:MM:SS" -> "Sep 19, 2026"
  function formatDate(value, withTime) {
    if (!value) return "—";
    const d = new Date(String(value).replace(" ", "T"));
    if (isNaN(d)) return esc(value);
    const opts = { year: "numeric", month: "short", day: "numeric" };
    if (withTime) {
      opts.hour = "numeric";
      opts.minute = "2-digit";
    }
    return d.toLocaleString(undefined, opts);
  }

  // Thumbnails are stored as "uploads/thumbnails/<file>" (relative to /public)
  function thumbUrl(path) {
    if (!path) return "";
    return "uploads/thumbnails/" + String(path).split("/").pop();
  }

  // Calls the admin JSON API. Throws an Error (with .status) on any failure.
  async function api(path, options) {
    const opts = options || {};
    const init = { method: opts.method || "GET", headers: authHeaders() };
    if (opts.body !== undefined) init.body = JSON.stringify(opts.body);

    let res;
    try {
      res = await fetch("?url=" + path, init);
    } catch (networkError) {
      const err = new Error("Network error. Check your connection and try again.");
      err.status = 0;
      throw err;
    }

    let data = null;
    try {
      data = await res.json();
    } catch (e) { /* non-JSON response */ }

    if (!res.ok || !data || data.success === false) {
      let message = (data && (data.message || data.error)) || "Something went wrong (" + res.status + ").";
      if (res.status === 401) {
        message = "Your session has expired. Please log in again.";
      } else if (res.status === 403) {
        message = "You don't have permission to do that.";
      }
      const err = new Error(message);
      err.status = res.status;
      throw err;
    }

    return data;
  }

  let toastTimer = null;
  function toast(message, isError) {
    let el = document.getElementById("adminToast");
    if (!el) {
      el = document.createElement("div");
      el.id = "adminToast";
      el.className = "admin-toast";
      el.setAttribute("role", "status");
      document.body.appendChild(el);
    }
    el.textContent = message;
    el.classList.toggle("error", !!isError);
    // restart the transition
    el.classList.remove("show");
    void el.offsetWidth;
    el.classList.add("show");
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => el.classList.remove("show"), 3200);
  }

  // Shows a floating (position: fixed) menu next to the button that opened it,
  // right-aligned to the button and kept inside the viewport.
  function positionMenu(menu, anchor) {
    menu.hidden = false;

    const rect = anchor.getBoundingClientRect();
    const w = menu.offsetWidth;
    const h = menu.offsetHeight;
    const left = Math.max(8, Math.min(rect.right - w, window.innerWidth - w - 8));
    let top = rect.bottom + 6;
    if (top + h > window.innerHeight - 8) top = Math.max(8, rect.top - h - 6);

    menu.style.left = left + "px";
    menu.style.top = top + "px";
  }

  function ratingHtml(avgRating, ratingCount) {
    if (!ratingCount) return '<span class="rating-none">No ratings</span>';
    return '<span class="rating-star">★</span> ' + Number(avgRating).toFixed(1);
  }

  return { STATUS, STATUS_SUCCESS_TEXT, statusInfo, esc, formatDate, thumbUrl, api, toast, ratingHtml, positionMenu };
})();
