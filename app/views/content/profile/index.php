<main class="main-col">
    <!-- PROFILE HERO -->
    <section class="profile-hero">
        <div class="profile-hero-content">
            <div class="profile-avatar-xl" id="profileAvatar">S</div>

            <div class="profile-id">
                <div class="profile-name-row">
                    <h1 id="profileName">Student</h1>
                    <span class="profile-badge" id="profileBadge">
                        <svg viewBox="0 0 24 24" style="width:11px;height:11px;fill:currentColor;"><path d="M12 2 1 7l11 5 9-4.09V17h2V7L12 2zM5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82z"/></svg>
                        Student Developer
                    </span>
                </div>
                <p class="profile-username" id="profileUsername">@student</p>
                <p class="profile-bio" id="profileBio">Loading your profile…</p>
            </div>

            <div class="profile-actions">
                <button class="btn-edit-profile" id="editProfileBtn">
                    <svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                    Edit Profile
                </button>
            </div>
        </div>

        <div class="profile-stats-row" id="profileStatsRow">
            <div class="profile-stat"><span class="num" id="statGames">0</span><span class="lbl">Games Submitted</span></div>
            <div class="profile-stat"><span class="num" id="statPlays">0</span><span class="lbl">Total Plays</span></div>
            <div class="profile-stat"><span class="num" id="statRating">–</span><span class="lbl">Avg. Rating</span></div>
            <div class="profile-stat"><span class="num" id="statSince">–</span><span class="lbl">Member Since</span></div>
        </div>
    </section>

    <!-- ACCOUNT DETAILS -->
    <section class="panel" id="account-details">
        <div class="section-header">
            <h2 class="section-title">
                <svg viewBox="0 0 24 24"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
                Account Details
            </h2>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Full Name</div>
                <div class="info-value" id="infoName">—</div>
            </div>
            <div class="info-item">
                <div class="info-label">Username</div>
                <div class="info-value" id="infoUsername">—</div>
            </div>
            <div class="info-item">
                <div class="info-label">Email Address</div>
                <div class="info-value" id="infoEmail">—</div>
            </div>
        </div>
    </section>

    <!-- MY GAMES -->
    <section class="panel" id="my-games">
        <div class="section-header">
            <h2 class="section-title">
                <svg viewBox="0 0 24 24"><path d="M15 7.5V2H9v5.5l3 3 3-3zM7.5 9H2v6h5.5l3-3-3-3zM9 16.5V22h6v-5.5l-3-3-3 3zM16.5 9l-3 3 3 3H22V9h-5.5z"/></svg>
                My Games
            </h2>
            <div style="display:flex; align-items:center; gap:12px;">
                <div class="sort-dropdown-wrap" id="sortDropdownWrap">
                    <button class="sort-dropdown-btn" id="sortDropdownBtn" type="button">
                        <span id="sortDropdownLabel">Newest First</span>
                        <svg class="chevron" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z"/></svg>
                    </button>
                    <div class="sort-dropdown-menu" id="sortDropdownMenu">
                        <button class="dropdown-item sort-option active" type="button" data-value="newest">Newest First</button>
                        <button class="dropdown-item sort-option" type="button" data-value="oldest">Oldest First</button>
                        <button class="dropdown-item sort-option" type="button" data-value="rating">Highest Rated</button>
                        <button class="dropdown-item sort-option" type="button" data-value="plays">Most Played</button>
                    </div>
                </div>
                <button class="btn-add-game" id="addGameBtn">
                    <svg viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                    Add Game
                </button>
            </div>
        </div>

        <div class="profile-stats-row" style="margin-top:0; padding-top:0; border-top:none; margin-bottom:22px; justify-content:center;">
            <div class="profile-stat"><span class="num" id="mgStatTotal">0</span><span class="lbl">Total Games</span></div>
            <div class="profile-stat"><span class="num" id="mgStatPublished">0</span><span class="lbl">Published</span></div>
            <div class="profile-stat"><span class="num" id="mgStatPlays">0</span><span class="lbl">Total Plays</span></div>
            <div class="profile-stat"><span class="num" id="mgStatRating">–</span><span class="lbl">Avg. Rating</span></div>
        </div>

        <div class="games-toolbar" style="margin-bottom: 18px; justify-content:center;">
            <span class="count-text"><strong id="mgCount">0</strong> games submitted</span>
        </div>

        <div class="games-grid" id="myGamesGrid"></div>

        <div class="empty-state" id="myGamesEmpty" style="display:none;">
            <div class="empty-icon">
                <svg viewBox="0 0 24 24"><path d="M15 7.5V2H9v5.5l3 3 3-3zM7.5 9H2v6h5.5l3-3-3-3zM9 16.5V22h6v-5.5l-3-3-3 3zM16.5 9l-3 3 3 3H22V9h-5.5z"/></svg>
            </div>
            <h3>No games yet</h3>
            <p>You haven't submitted any games. Once you add one, it'll show up here for you to manage.</p>
            <button class="btn-add-game" id="emptyAddGameBtn">
                <svg viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                Add Your First Game
            </button>
        </div>
    </section>
</main>
<?php include __DIR__ . '/manageGame-modal.php'; ?>
<?php include __DIR__ . '/gameDetails-modal.php'; ?>
<?php include __DIR__ . '/editProfile-modal.php'; ?>
<?php include __DIR__ . '/deleteConfirmation-modal.php'; ?>
<?php include __DIR__ . '/upload-modal.php'; ?>