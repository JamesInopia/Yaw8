// ═══════════════════════════════════════════════════
// ADMIN-USERS.JS — the "Users" tab on the admin dashboard (?url=admin#users)
// Same page, same table format as the Games tab. Suspending happens in a modal.
//
// Supreme Overlord-only pieces (Role column, Role filter, Make/Revoke admin,
// the role dialog) are only written into the page by PHP for a Supreme
// Overlord, and every API call is re-checked on the server anyway. The code
// below just skips them when their elements aren't there.
// ═══════════════════════════════════════════════════
(function () {
  const UI = window.AdminUI;
  const tbody = document.getElementById("usersTableBody");
  if (!UI || !tbody) return;

  const $ = (id) => document.getElementById(id);
  const sortSelect = $("userSortSelect");
  const filterSelect = $("userFilterSelect");
  const roleSelect = $("userRoleSelect");          // Supreme Overlord only
  const searchInput = $("userSearch");
  const countEl = $("usersCount");
  const colCount = document.querySelectorAll("#usersTable thead th").length;

  const menu = $("userActionMenu");
  const menuSuspend = $("menuSuspend");
  const menuUnsuspend = $("menuUnsuspend");
  const menuMakeAdmin = $("menuMakeAdmin");        // Supreme Overlord only
  const menuRevokeAdmin = $("menuRevokeAdmin");    // Supreme Overlord only

  let users = [];
  let isSupreme = false;
  let loaded = false;
  let loading = false;
  let busy = false;
  let menuUserId = null;
  let menuAnchor = null;

  const findUser = (id) => users.find((u) => u.userId === id);

  // ─────────────────────────────────────────
  // LOAD (first time the Users tab is opened)
  // ─────────────────────────────────────────
  async function load() {
    if (loaded || loading) return;
    loading = true;
    try {
      const data = await UI.api("api/admin/users");
      users = data.users || [];
      isSupreme = !!(data.viewer && data.viewer.isSupremeOverlord);
      loaded = true;
      render();
    } catch (err) {
      tbody.innerHTML = '<tr><td colspan="' + colCount + '" class="admin-table-message" style="color:var(--admin-danger)">' +
        UI.esc(err.message) +
        (err.status === 401 ? ' <a href="?url=auth" style="color:var(--cyan);text-decoration:underline">Log in</a>' : "") +
        "</td></tr>";
      console.error("Admin: could not load users -", err);
    } finally {
      loading = false;
    }
  }

  document.addEventListener("admin:tab", (e) => {
    closeMenu();
    if (e.detail === "users") load();
  });

  // The page can be opened straight on #users (admin.js activates that tab before this file runs)
  if (!$("tab-users").hidden) load();

  // ─────────────────────────────────────────
  // FILTER + SEARCH + SORT + RENDER
  // ─────────────────────────────────────────
  function visibleUsers() {
    const status = filterSelect.value;
    const role = roleSelect ? roleSelect.value : "all";
    const query = searchInput.value.trim().toLowerCase();

    const list = users.filter((u) => {
      if (status === "active" && u.suspended) return false;
      if (status === "suspended" && !u.suspended) return false;
      if (role === "members" && u.isAdmin) return false;
      if (role === "admins" && !u.isAdmin) return false;
      if (!query) return true;
      return (
        String(u.name || "").toLowerCase().includes(query) ||
        String(u.username || "").toLowerCase().includes(query) ||
        String(u.email || "").toLowerCase().includes(query)
      );
    });

    const byName = (a, b) => String(a.name).localeCompare(String(b.name), undefined, { sensitivity: "base" });

    switch (sortSelect.value) {
      case "id-desc":   list.sort((a, b) => b.userId - a.userId); break;
      case "name-asc":  list.sort(byName); break;
      case "name-desc": list.sort((a, b) => byName(b, a)); break;
      default:          list.sort((a, b) => a.userId - b.userId); // id-asc
    }

    return list;
  }

  function statusHtml(u) {
    if (!u.suspended) return '<span class="admin-status active">Active</span>';
    const sub = u.indefinite ? "Indefinite" : "Until " + UI.formatDate(u.suspendedUntil, true);
    return '<span class="admin-status suspended">Suspended</span><span class="status-sub">' + sub + "</span>";
  }

  function render() {
    const list = visibleUsers();

    if (list.length === 0) {
      const msg = users.length === 0 ? "There are no accounts to show." : "No accounts match your filters.";
      tbody.innerHTML = '<tr><td colspan="' + colCount + '" class="admin-table-message">' + msg + "</td></tr>";
      countEl.textContent = "";
      return;
    }

    tbody.innerHTML = list.map((u) => {
      const label = UI.esc(u.name);
      const roleCell = isSupreme
        ? '<td><span class="role-pill ' + (u.isAdmin ? "admin" : "member") + '">' + (u.isAdmin ? "Admin" : "Member") + "</span></td>"
        : "";

      return `
        <tr data-user-id="${u.userId}">
          <td class="col-num">${u.userId}</td>
          <td class="col-user">
            <span class="user-name">${label}</span>
            <span class="user-sub">@${UI.esc(u.username)} · ${UI.esc(u.email)}</span>
          </td>
          ${roleCell}
          <td class="col-rating">${UI.ratingHtml(u.avgRating, u.ratingCount)}</td>
          <td>${statusHtml(u)}</td>
          <td class="col-action">
            <button type="button" class="action-btn" data-user-id="${u.userId}"
                    aria-haspopup="menu" aria-expanded="false" aria-label="Actions for ${label}">
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm12 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm-6 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
            </button>
          </td>
        </tr>`;
    }).join("");

    countEl.textContent = "Showing " + list.length + " of " + users.length + " account" + (users.length === 1 ? "" : "s");
  }

  [sortSelect, filterSelect, roleSelect].forEach((el) => {
    if (el) el.addEventListener("change", () => { closeMenu(); render(); });
  });
  searchInput.addEventListener("input", () => { closeMenu(); render(); });

  // Swap one account in the list with the fresh copy the server sent back
  function replaceUser(fresh) {
    const i = users.findIndex((u) => u.userId === fresh.userId);
    if (i === -1) users.push(fresh); else users[i] = fresh;
    render();
  }

  // ─────────────────────────────────────────
  // "⋯" ACTION MENU
  // ─────────────────────────────────────────
  function openMenu(button) {
    const user = findUser(Number(button.dataset.userId));
    if (!user) return;

    closeMenu();
    menuUserId = user.userId;
    menuAnchor = button;
    button.setAttribute("aria-expanded", "true");

    // Only the action that makes sense for this account's current state is shown
    menuSuspend.hidden = user.suspended;
    menuUnsuspend.hidden = !user.suspended;
    if (menuMakeAdmin) menuMakeAdmin.hidden = !!user.isAdmin;
    if (menuRevokeAdmin) menuRevokeAdmin.hidden = !user.isAdmin;

    UI.positionMenu(menu, button);

    const first = menu.querySelector(".admin-menu-item:not([hidden])");
    if (first) first.focus();
  }

  function closeMenu() {
    if (menu.hidden) return;
    menu.hidden = true;
    if (menuAnchor) {
      menuAnchor.setAttribute("aria-expanded", "false");
      menuAnchor = null;
    }
    menuUserId = null;
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
  // SMALL DIALOG HELPER (Esc closes, Tab stays inside, focus returns)
  // ─────────────────────────────────────────
  function makeDialog(el, initialFocusSelector) {
    let lastFocus = null;

    function open() {
      lastFocus = document.activeElement;
      el.hidden = false;
      const target = el.querySelector(initialFocusSelector) || el.querySelector("button");
      if (target) target.focus();
    }

    function close() {
      el.hidden = true;
      if (lastFocus && lastFocus.focus && document.contains(lastFocus)) lastFocus.focus();
      lastFocus = null;
    }

    el.querySelectorAll("[data-close-dialog]").forEach((x) => x.addEventListener("click", close));

    document.addEventListener("keydown", (e) => {
      if (el.hidden) return;
      if (e.key === "Escape") { close(); return; }
      if (e.key === "Tab") {
        const focusable = el.querySelectorAll("button:not(:disabled), input:not(:disabled)");
        if (!focusable.length) return;
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
        else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
      }
    });

    return { open, close };
  }

  // ─────────────────────────────────────────
  // SUSPEND (modal: indefinite or timed)
  // ─────────────────────────────────────────
  const suspendEl = $("suspendDialog");
  const suspendDialog = makeDialog(suspendEl, 'input[name="suspendMode"]:checked');
  const daysInput = $("suspendDays");
  const suspendError = $("suspendError");
  const suspendConfirm = $("suspendConfirm");
  let suspendUserId = null;

  const selectedMode = () => suspendEl.querySelector('input[name="suspendMode"]:checked').value;

  suspendEl.querySelectorAll('input[name="suspendMode"]').forEach((radio) => {
    radio.addEventListener("change", () => {
      const timed = selectedMode() === "timed";
      daysInput.disabled = !timed;
      suspendError.hidden = true;
      if (timed) { daysInput.focus(); daysInput.select(); }
    });
  });

  menuSuspend.addEventListener("click", () => {
    const user = findUser(menuUserId);
    closeMenu();
    if (!user) return;

    suspendUserId = user.userId;
    $("suspendTarget").textContent =
      "You're about to suspend " + user.name + " (@" + user.username + "). " +
      "They'll be signed out right away and won't be able to log in while suspended.";

    suspendEl.querySelector('input[value="indefinite"]').checked = true;
    daysInput.value = 7;
    daysInput.disabled = true;
    suspendError.hidden = true;
    suspendConfirm.disabled = false;
    suspendDialog.open();
  });

  suspendConfirm.addEventListener("click", async () => {
    if (busy || suspendUserId === null) return;

    const mode = selectedMode();
    const body = { userId: suspendUserId, mode: mode };

    if (mode === "timed") {
      const days = Number(daysInput.value);
      if (!Number.isInteger(days) || days < 1 || days > 365) {
        suspendError.textContent = "Enter a whole number of days from 1 to 365.";
        suspendError.hidden = false;
        daysInput.focus();
        return;
      }
      body.days = days;
    }

    busy = true;
    suspendConfirm.disabled = true;
    try {
      const result = await UI.api("api/admin/user/suspend", { method: "POST", body: body });
      replaceUser(result.user);
      suspendDialog.close();
      UI.toast(result.user.name + (result.user.indefinite ? " suspended indefinitely." : " suspended."));
    } catch (err) {
      suspendError.textContent = err.message;
      suspendError.hidden = false;
    } finally {
      busy = false;
      suspendConfirm.disabled = false;
    }
  });

  // ─────────────────────────────────────────
  // UNSUSPEND (one click, no modal)
  // ─────────────────────────────────────────
  menuUnsuspend.addEventListener("click", async () => {
    const user = findUser(menuUserId);
    closeMenu();
    if (!user || busy) return;

    busy = true;
    try {
      const result = await UI.api("api/admin/user/unsuspend", { method: "POST", body: { userId: user.userId } });
      replaceUser(result.user);
      UI.toast(result.user.name + " has been unsuspended.");
    } catch (err) {
      UI.toast(err.message, true);
    } finally {
      busy = false;
    }
  });

  // ─────────────────────────────────────────
  // MAKE / REVOKE ADMIN (Supreme Overlord only)
  // ─────────────────────────────────────────
  const roleEl = $("roleDialog");
  if (roleEl && menuMakeAdmin && menuRevokeAdmin) {
    const roleDialog = makeDialog(roleEl, "#roleConfirm");
    const roleConfirm = $("roleConfirm");
    let roleUserId = null;
    let roleMakeAdmin = true;

    function askRole(makeAdmin) {
      const user = findUser(menuUserId);
      closeMenu();
      if (!user) return;

      roleUserId = user.userId;
      roleMakeAdmin = makeAdmin;
      $("roleTitle").textContent = makeAdmin ? "Make " + user.name + " an admin?" : "Revoke admin from " + user.name + "?";
      $("roleText").textContent = makeAdmin
        ? "They'll get access to the admin dashboard: reviewing games, resolving reports and suspending regular accounts."
        : "They'll go back to a regular member account and lose access to the admin dashboard straight away.";
      roleConfirm.textContent = makeAdmin ? "Yes, make admin" : "Yes, revoke admin";
      roleConfirm.className = "admin-btn " + (makeAdmin ? "admin-btn-green" : "admin-btn-danger");
      roleDialog.open();
    }

    menuMakeAdmin.addEventListener("click", () => askRole(true));
    menuRevokeAdmin.addEventListener("click", () => askRole(false));

    roleConfirm.addEventListener("click", async () => {
      if (busy || roleUserId === null) return;
      busy = true;
      roleConfirm.disabled = true;
      try {
        const result = await UI.api("api/admin/user/admin-access", {
          method: "POST",
          body: { userId: roleUserId, makeAdmin: roleMakeAdmin },
        });
        replaceUser(result.user);
        roleDialog.close();
        UI.toast(result.user.name + (roleMakeAdmin ? " is now an admin." : " is no longer an admin."));
      } catch (err) {
        roleDialog.close();
        UI.toast(err.message, true);
      } finally {
        busy = false;
        roleConfirm.disabled = false;
      }
    });
  }
})();
