<?php
  $currentRoute = $_GET['url'] ?? 'home'; 
  $isLoggedIn = !empty($_SESSION['user_id']);
  # Admin / Supreme Overlord accounts get an extra "Admin" tab in the navbar
  $isAdmin = $isLoggedIn && Auth::isAdmin();
  $isAdminRoute = in_array($currentRoute, ['admin', 'admin/game']);

  # Name shown in the navbar's user button (falls back to "User" if it can't be loaded)
  $navUsername = 'User';
  if ($isLoggedIn) {
    try {
      $navUserRow = (new User())->getUserById($_SESSION['user_id']);
      if (!empty($navUserRow['username'])) {
        $navUsername = $navUserRow['username'];
      }
    } catch (Throwable $e) {
      error_log('Navbar username lookup failed: ' . $e->getMessage());
    }
  }

  # "Random Challenge" footer link. On a game's own page, skip that game so the
  # person never gets sent to the game they're already on.
  $randomHref = '?url=games/random';
  if (in_array($currentRoute, ['games/player', 'downloader'], true) && ctype_digit((string) ($_GET['gameId'] ?? ''))) {
    $randomHref .= '&exclude=' . (int) $_GET['gameId'];
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <?php if ($isLoggedIn): ?><meta name="yaw8-logged-in" content="1"/><?php endif; ?>
    <title>YAW8 - Play What Students Create</title>

    <!-- ICONS -->
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>

    <!-- CSS -->
    <link rel="stylesheet" href="css/style.css"/>
    <link rel="stylesheet" href="css/modal.css"/>
    <link rel="stylesheet" href="css/search.css"/>

    <?php if ($currentRoute === 'auth'): ?>
      <link rel="stylesheet" href="css/auth.css"/>
    <?php elseif ($currentRoute === 'games' || $currentRoute === 'games/player' || $currentRoute === 'downloader'): ?>
      <link rel="stylesheet" href="css/games.css"/>
      <link rel="stylesheet" href="css/gamepage.css"/>
      <?php if ($currentRoute === 'downloader'): ?>
      <link rel="stylesheet" href="css/downloader.css"/>
      <?php endif; ?>
    <?php elseif ($currentRoute === 'developers'): ?>
      <link rel="stylesheet" href="css/developers.css"/>
    <?php elseif ($currentRoute === 'about'): ?>
      <link rel="stylesheet" href="css/about.css"/>
    <?php elseif ($currentRoute === 'profile'): ?>
      <link rel="stylesheet" href="css/account.css"/>
    <?php elseif ($currentRoute === 'settings'): ?>
      <link rel="stylesheet" href="css/settings.css"/>
    <?php elseif ($isAdminRoute): ?>
      <link rel="stylesheet" href="css/admin.css"/>
    <?php endif; ?>
    
</head>
<body>
<!-- ══════════════════════════════════════════
    LOADING SCREEN
══════════════════════════════════════════ -->
<div id="loading-screen">
    <div class="loader-content">
    <div class="loader-logo">
        <img src="assets/images/Official_Logo.png" alt="YA!W8" class="loader-logo-img" />
    </div>
    <div class="loader-bar-track">
        <div class="loader-bar-fill" id="loaderBarFill"></div>
    </div>
    <p class="loader-text" id="loaderText">Loading…</p>
    </div>
</div>

<!-- ══════════════════════════════════════════
    NAVBAR
══════════════════════════════════════════ -->
<?php if ($currentRoute !== 'auth'): ?>
<nav>
<div class="nav-pill-wrap">
  <!-- Logo image -->
  <div class="nav-logo">
    <img src="assets/images/NavBar_Logo.png" alt="YA!W8 - Play What Students Create" />
  </div>

  <div class="nav-divider"></div>

  <!-- Navigation Links -->
  <div class="nav-pill">
    <ul class="nav-links">
      <li>
        <a href="?url=home" <?= $currentRoute === 'home' ? 'class="active"' : '' ?> data-nav="home">
          <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
          Home
        </a>
      </li>
      <li>
        <a href="?url=games" <?= in_array($currentRoute, ['games', 'games/player']) ? 'class="active"' : '' ?> data-nav="games">
          <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M15 7.5V2H9v5.5l3 3 3-3zM7.5 9H2v6h5.5l3-3-3-3zM9 16.5V22h6v-5.5l-3-3-3 3zM16.5 9l-3 3 3 3H22V9h-5.5z"/></svg>
          Games
        </a>
      </li>
      <li>
        <a href="?url=developers" <?= $currentRoute === 'developers' ? 'class="active"' : '' ?> data-nav="developers">
          <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
          Developers
        </a>
      </li>
      <li>
        <a href="?url=about" <?= $currentRoute === 'about' ? 'class="active"' : '' ?> data-nav="about">
          <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
          About
        </a>
      </li>
      <?php if ($isAdmin): ?>
      <li>
        <a href="?url=admin" <?= $isAdminRoute ? 'class="active"' : '' ?> data-nav="admin">
          <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 1 3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg>
          Admin
        </a>
      </li>
      <?php endif; ?>
    </ul>
  </div>

  <div class="nav-divider"></div>

  <!-- Right side controls -->
  <div class="nav-right">
    <!-- Search -->
    <div class="search-bar">
      <svg class="search-icon" viewBox="0 0 24 24"><path d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/></svg>
      <input type="text" placeholder="Search games and developers…" id="searchInput" />
      <button class="search-filter-btn" title="Filter" id="filterBtn">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M7 12h10M10 18h4"/></svg>
      </button>
    </div>

    <!-- Notification bell -->
    <button class="icon-btn" title="Notifications">
      <svg viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
    </button>

    <!-- Avatar / user menu -->
    <?php if ($isLoggedIn): ?>
    <div class="avatar-wrap">
      <button class="avatar-btn" id="avatarBtn">
        <div class="avatar-circle">
          <svg viewBox="0 0 24 24"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
        </div>
        <p title="<?= htmlspecialchars($navUsername) ?>"><?= htmlspecialchars($navUsername) ?></p>
        <svg class="chevron" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z"/></svg>
      </button>

      <div class="user-dropdown" id="userDropdown">
        <a href="?url=profile" class="dropdown-item" id="profileBtn" data-nav="profile"><svg viewBox="0 0 24 24"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg> My Profile</a>
        <a href="?url=settings" class="dropdown-item" id="settingsBtn" data-nav="settings"><svg viewBox="0 0 24 24"><path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/></svg> Settings</a>
        <div class="dropdown-divider"></div>
        <button class="dropdown-item danger" id="signOutBtn"><svg viewBox="0 0 24 24"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg> Sign Out</button>
      </div>
    </div>
    <?php else: ?>
    <a href="?url=auth" class="login-btn" id="loginNavBtn"><svg viewBox="0 0 24 24"><path d="M11 7L9.6 8.4l2.6 2.6H2v2h10.2l-2.6 2.6L11 17l5-5zM19 3H12v2h7v14h-7v2h7c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/></svg> Log In</a>
    <?php endif; ?>
  </div>
</div>
</nav>

<!-- ══════════════════════════════════════════
    CONTENT
══════════════════════════════════════════ -->
<div class="<?= (in_array($currentRoute, ['about', 'games', 'games/player']) || $isAdminRoute) ? 'page-wrap-single' : 'page-wrap' ?>" <?= in_array($currentRoute, ['developers', 'profile']) ? 'style="grid-template-columns: 1fr;"' : '' ?>>
    <?= $content ?? '' ?>
</div>

<!-- ══════════════════════════════════════════
    GAME DETAILS MODAL (shared across pages)
══════════════════════════════════════════ -->
<div id="game-modal" class="modal">
<div class="modal-overlay"></div>
<div class="modal-content modal-content-game-view">
<button class="modal-close">
    <svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
</button>

<?php require dirname(__DIR__) . "/content/games/gameDetails_modal.php" ?>

</div>
</div>

<?php require dirname(__DIR__) . "/content/games/reportGame-modal.php" ?>

<!-- ══════════════════════════════════════════
    FOOTER
══════════════════════════════════════════ -->
<footer>
    <div class="footer-inner">
        <div class="footer-brand">
            <img class="footer-logo" src="assets/images/Official_Logo.png" alt="YA!W8" />
            <p>YA!W8 is a platform for student-made games. Play, discover, and support creativity within the school community.</p>
        </div>
        <div class="footer-col">
            <h4>Explore</h4>
            <ul>
                <li><a href="?url=games#all-games">All Games</a></li>
                <li><a href="<?= htmlspecialchars($randomHref) ?>">Random Challenge</a></li>
                <li><a href="?url=games#community-spotlight">Spotlight</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Community</h4>
            <ul>
                <li><a href="?url=developers">Developers</a></li>
                <li><a href="?url=games#top-played">Leaderboard</a></li>
                <li><a href="?url=profile&amp;upload=1">Submit Your Game</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Support</h4>
            <ul>
                <li><a href="#">Feedback</a></li>
                <li><a href="?url=about">About Us</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <span>© 2026 YA!W8. All rights reserved.</span>
    </div>
</footer>

<!-- ══════════════════════════════════════════
    AUTH CONTENT
══════════════════════════════════════════ -->
<?php else: ?>
    <?= $content ?? '' ?>
<?php endif; ?>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="js/auth.js"></script>
<?php if ($currentRoute !== 'auth'): ?>
  <script src="js/script.js"></script>
  <script src="js/nav-tools.js"></script>
  <?php if ($currentRoute === 'games'): ?>
    <script src="js/games.js"></script>
  <?php elseif ($currentRoute === 'games/player'): ?>
    <script src="js/gamepage.js"></script>
  <?php elseif ($currentRoute === 'downloader'): ?>
    <script src="js/downloader.js"></script>
  <?php elseif ($currentRoute === 'developers'): ?>
    <script src="js/developers.js"></script>
  <?php elseif ($currentRoute === 'about'): ?>
    <script src="js/about.js"></script>
  <?php elseif ($currentRoute === 'profile'): ?>
    <script src="js/myprofile.js"></script>
  <?php elseif ($currentRoute === 'settings'): ?>
    <script src="js/settings.js"></script>
  <?php elseif ($currentRoute === 'admin'): ?>
    <script src="js/admin-common.js"></script>
    <script src="js/admin.js"></script>
    <script src="js/admin-users.js"></script>
  <?php elseif ($currentRoute === 'admin/game'): ?>
    <script src="js/admin-common.js"></script>
    <script src="js/admin-game.js"></script>
  <?php endif; ?>    
<?php endif; ?>

</body>
</html>