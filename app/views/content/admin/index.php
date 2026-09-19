<!-- ══════════════════════════════════════════
    PAGE BODY — ADMIN DASHBOARD
    Rendered by AdminController::index(); $stats holds the numbers for the
    stat cards. The games table is filled in by public/js/admin.js.
══════════════════════════════════════════ -->
<?php
$stats = $stats ?? [];
# Supreme Overlord-only controls are only written into the page for a Supreme Overlord
$isSupreme = Auth::isSupremeOverlord();
$statCards = [
    ['id' => 'statTotalUsers',  'key' => 'totalUsers',  'label' => 'Total Users',        'tone' => 'cyan',
     'icon' => 'M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z'],
    ['id' => 'statTotalGames',  'key' => 'totalGames',  'label' => 'Total Games',       'tone' => 'purple',
     'icon' => 'M15 7.5V2H9v5.5l3 3 3-3zM7.5 9H2v6h5.5l3-3-3-3zM9 16.5V22h6v-5.5l-3-3-3 3zM16.5 9l-3 3 3 3H22V9h-5.5z'],
    ['id' => 'statPublished',   'key' => 'published',   'label' => 'Published Games',   'tone' => 'green',
     'icon' => 'M9 16.17 4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z'],
    ['id' => 'statUnderReview', 'key' => 'underReview', 'label' => 'Under Review',      'tone' => 'amber',
     'icon' => 'M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm4.2 14.2L11 13V7h1.5v5.2l4.5 2.7-.8 1.3z'],
    ['id' => 'statUnlisted',    'key' => 'unlisted',    'label' => 'Unlisted Games',    'tone' => 'slate',
     'icon' => 'M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46A11.804 11.804 0 0 0 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2z'],
];
?>

<!-- ─────────────
    HEADER
───────────── -->
<section class="admin-header">
    <div>
        <h1>Admin <span class="accent">Dashboard</span></h1>
        <p>Keep YA!W8 tidy — review submitted games, check reports, and decide what goes live.</p>
    </div>
</section>

<!-- ─────────────
    STAT CARDS
───────────── -->
<section class="admin-stats" id="adminStats" aria-label="Platform statistics">
    <?php foreach ($statCards as $card): ?>
        <div class="stat-card tone-<?= $card['tone'] ?>">
            <div class="stat-card-icon">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="<?= $card['icon'] ?>"/></svg>
            </div>
            <div class="stat-card-body">
                <div class="stat-card-label"><?= htmlspecialchars($card['label']) ?></div>
                <div class="stat-card-value" id="<?= $card['id'] ?>"><?= (int) ($stats[$card['key']] ?? 0) ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</section>

<!-- ─────────────────────────────
    MANAGEMENT PANEL (tabs)
───────────────────────────── -->
<section class="panel admin-panel">
    <div class="admin-tabs" role="tablist" aria-label="Manage">
        <button type="button" class="admin-tab active" id="tab-btn-games" role="tab"
                aria-selected="true" aria-controls="tab-games" data-tab="games">Games</button>
        <button type="button" class="admin-tab" id="tab-btn-users" role="tab"
                aria-selected="false" aria-controls="tab-users" data-tab="users">Users</button>
    </div>

    <!-- GAMES TAB -->
    <div class="admin-tab-panel" id="tab-games" role="tabpanel" aria-labelledby="tab-btn-games">
        <div class="admin-toolbar">
            <label class="admin-field">
                <span class="admin-field-label">Sort by</span>
                <select id="sortSelect" class="admin-select">
                    <option value="newest">Upload date: newest first</option>
                    <option value="oldest">Upload date: oldest first</option>
                    <option value="name-asc">Name: A – Z</option>
                    <option value="name-desc">Name: Z – A</option>
                </select>
            </label>

            <label class="admin-field">
                <span class="admin-field-label">Show</span>
                <select id="filterSelect" class="admin-select">
                    <option value="all">All</option>
                    <option value="published">Published</option>
                    <option value="under_review">Under review</option>
                    <option value="unlisted">Unlisted</option>
                </select>
            </label>

            <div class="admin-search">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/></svg>
                <input type="text" id="gameSearch" placeholder="Search by title or developer…" autocomplete="off" aria-label="Search games" />
            </div>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table" id="gamesTable">
                <thead>
                    <tr>
                        <th class="col-num">#</th>
                        <th>Name</th>
                        <th>Developer</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th class="col-center">Reports</th>
                        <th class="col-action">Action</th>
                    </tr>
                </thead>
                <tbody id="gamesTableBody">
                    <tr><td colspan="7" class="admin-table-message">Loading games…</td></tr>
                </tbody>
            </table>
        </div>

        <p class="admin-count" id="gamesCount" aria-live="polite"></p>
    </div>

    <!-- USERS TAB -->
    <div class="admin-tab-panel" id="tab-users" role="tabpanel" aria-labelledby="tab-btn-users" hidden>
        <div class="admin-toolbar">
            <label class="admin-field">
                <span class="admin-field-label">Sort by</span>
                <select id="userSortSelect" class="admin-select">
                    <option value="id-asc">ID: lowest first</option>
                    <option value="id-desc">ID: highest first</option>
                    <option value="name-asc">Name: A – Z</option>
                    <option value="name-desc">Name: Z – A</option>
                </select>
            </label>

            <label class="admin-field">
                <span class="admin-field-label">Show</span>
                <select id="userFilterSelect" class="admin-select">
                    <option value="all">All</option>
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                </select>
            </label>

            <?php if ($isSupreme): ?>
            <label class="admin-field">
                <span class="admin-field-label">Role</span>
                <select id="userRoleSelect" class="admin-select">
                    <option value="all">All accounts</option>
                    <option value="members">Members only</option>
                    <option value="admins">Admins only</option>
                </select>
            </label>
            <?php endif; ?>

            <div class="admin-search">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/></svg>
                <input type="text" id="userSearch" placeholder="Search by name, username or email…" autocomplete="off" aria-label="Search users" />
            </div>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table" id="usersTable">
                <thead>
                    <tr>
                        <th class="col-num">ID</th>
                        <th>Name</th>
                        <?php if ($isSupreme): ?><th>Role</th><?php endif; ?>
                        <th>Rating</th>
                        <th>Status</th>
                        <th class="col-action">Action</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <tr><td colspan="<?= $isSupreme ? 6 : 5 ?>" class="admin-table-message">Loading users…</td></tr>
                </tbody>
            </table>
        </div>

        <p class="admin-count" id="usersCount" aria-live="polite"></p>
    </div>
</section>

<!-- Floating "⋯" action menu (one shared menu, positioned next to the clicked row) -->
<div class="admin-action-menu" id="actionMenu" role="menu" aria-label="Game actions" hidden>
    <a href="#" class="admin-menu-item" role="menuitem" id="menuDetails">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
        Game details
    </a>
    <div class="admin-menu-divider" role="separator"></div>
    <button type="button" class="admin-menu-item" role="menuitem" data-set-status="published">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 16.17 4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
        Confirm publication
    </button>
    <button type="button" class="admin-menu-item" role="menuitem" data-set-status="under_review">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm4.2 14.2L11 13V7h1.5v5.2l4.5 2.7-.8 1.3z"/></svg>
        Put under review
    </button>
    <button type="button" class="admin-menu-item" role="menuitem" data-set-status="unlisted">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46A11.804 11.804 0 0 0 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27z"/></svg>
        Unlist
    </button>
</div>

<!-- Floating "⋯" action menu for the Users table -->
<div class="admin-action-menu" id="userActionMenu" role="menu" aria-label="Account actions" hidden>
    <button type="button" class="admin-menu-item" role="menuitem" id="menuSuspend">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zM4 12c0-4.42 3.58-8 8-8 1.85 0 3.55.63 4.9 1.69L5.69 16.9A7.902 7.902 0 0 1 4 12zm8 8c-1.85 0-3.55-.63-4.9-1.69L18.31 7.1A7.902 7.902 0 0 1 20 12c0 4.42-3.58 8-8 8z"/></svg>
        Suspend account
    </button>
    <button type="button" class="admin-menu-item" role="menuitem" id="menuUnsuspend">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
        Unsuspend account
    </button>
<?php if ($isSupreme): ?>
    <div class="admin-menu-divider" role="separator"></div>
    <button type="button" class="admin-menu-item" role="menuitem" id="menuMakeAdmin">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 1 3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-1 15-4-4 1.41-1.41L11 13.17l4.59-4.58L17 10l-6 6z"/></svg>
        Make admin
    </button>
    <button type="button" class="admin-menu-item" role="menuitem" id="menuRevokeAdmin">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 1 3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm4.3 12.9-1.4 1.4L12 12.4l-2.9 2.9-1.4-1.4 2.9-2.9-2.9-2.9 1.4-1.4 2.9 2.9 2.9-2.9 1.4 1.4-2.9 2.9 2.9 2.9z"/></svg>
        Revoke admin
    </button>
<?php endif; ?>
</div>

<!-- Suspend account modal (indefinite or timed) -->
<div class="admin-dialog" id="suspendDialog" role="dialog" aria-modal="true" aria-labelledby="suspendTitle" hidden>
    <div class="admin-dialog-overlay" data-close-dialog></div>
    <div class="admin-dialog-box">
        <h3 id="suspendTitle">Suspend account</h3>
        <p id="suspendTarget"></p>

        <fieldset class="suspend-options">
            <legend class="admin-field-label">Suspension length</legend>

            <label class="suspend-option">
                <input type="radio" name="suspendMode" value="indefinite" checked>
                <span>
                    <strong>Indefinite</strong>
                    <small>Stays suspended until an admin unsuspends the account.</small>
                </span>
            </label>

            <label class="suspend-option">
                <input type="radio" name="suspendMode" value="timed">
                <span>
                    <strong>Timed</strong>
                    <small>Ends automatically after a number of days.</small>
                    <span class="suspend-days">
                        <input type="number" id="suspendDays" min="1" max="365" step="1" value="7" inputmode="numeric" disabled aria-label="Number of days">
                        <span>day(s)</span>
                    </span>
                </span>
            </label>
        </fieldset>

        <p class="suspend-error" id="suspendError" role="alert" hidden></p>

        <div class="admin-dialog-actions">
            <button type="button" class="admin-btn admin-btn-ghost" data-close-dialog>Cancel</button>
            <button type="button" class="admin-btn admin-btn-danger" id="suspendConfirm">Suspend account</button>
        </div>
    </div>
</div>
<?php if ($isSupreme): ?>

<!-- Make / revoke admin confirmation (Supreme Overlord only) -->
<div class="admin-dialog" id="roleDialog" role="dialog" aria-modal="true" aria-labelledby="roleTitle" hidden>
    <div class="admin-dialog-overlay" data-close-dialog></div>
    <div class="admin-dialog-box">
        <h3 id="roleTitle"></h3>
        <p id="roleText"></p>
        <div class="admin-dialog-actions">
            <button type="button" class="admin-btn admin-btn-ghost" data-close-dialog>Cancel</button>
            <button type="button" class="admin-btn admin-btn-green" id="roleConfirm">Confirm</button>
        </div>
    </div>
</div>
<?php endif; ?>
