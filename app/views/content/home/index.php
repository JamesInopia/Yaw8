<!-- ══════════════════════════════════════════
    PAGE BODY (HOME VIEW)
══════════════════════════════════════════ -->
<?php
if (!function_exists('yaw8_thumbnail_html')) {
    // Renders a game's thumbnail: the real image if one exists,
    // otherwise a colored placeholder with the title as text
    // (cycles through 4 preset color classes so cards don't all look the same).
    function yaw8_thumbnail_html($thumbnail, $title, $index) {
        $colorClasses = ['gt-pixel-drift', 'gt-tower-tactics', 'gt-box-jumper', 'gt-color-clash'];

        if (!empty($thumbnail)) {
            return '<div class="game-thumb sp-forest"><img src="' . htmlspecialchars($thumbnail) . '" alt="' . htmlspecialchars($title) . '"></div>';
        }

        $colorClass = $colorClasses[$index % count($colorClasses)];
        $titleText = str_replace(' ', '<br>', strtoupper(htmlspecialchars($title)));

        return '<div class="game-thumb ' . $colorClass . '"><div class="game-thumb-title">' . $titleText . '</div></div>';
    }
}
?>
  <!-- ── MAIN COLUMN ── -->
  <main class="main-col">

    <!-- ─────────────
        HERO SECTION
    ───────────── -->
    <section class="hero" style="background-image: url('assets/images/Front_BG.png'); background-size: cover; background-position: center; background-repeat: no-repeat;">

      <!-- Floating decorative sprites (Will do later)-->

      <div class="hero-content">
        <!-- Press Start 2P game font headline -->
        <h1>Play Games Made <br> by <span class="accent">Game Changers</span></h1>
        <p class="hero-sub">Discover fun, creative, and lightweight games right in your browser.</p>
        <div class="hero-btns">
          <button class="btn-primary">
              <a href="#featured-games">
                Explore Now
              </a>
          </button>
        </div>
      </div>

      <!-- Glowing "8" art (Will do later) -->

    </section>


    <!-- ─────────────────
        FEATURED GAMES
    ───────────────── -->
    <section class="panel" id="featured-games">
      <div class="section-header">
        <h2 class="section-title">
          <!-- Gamepad icon -->
          <svg viewBox="0 0 24 24"><path d="M15 7.5V2H9v5.5l3 3 3-3zM7.5 9H2v6h5.5l3-3-3-3zM9 16.5V22h6v-5.5l-3-3-3 3zM16.5 9l-3 3 3 3H22V9h-5.5z"/></svg>
          Featured Games
        </h2>
        <a class="view-all" href="?url=games#featured-games">View All →</a>
      </div>

      <div class="games-grid">

        <?php if(isset($featuredGames)): ?>
          <section class="top-played-games">
              <?php foreach($featuredGames as $index => $featuredGame): ?>
                  <div class="game-card"
                      data-game-id="<?= htmlspecialchars($featuredGame->getGameId()) ?>"
                      data-title="<?= htmlspecialchars($featuredGame->getTitle()) ?>"
                      data-description="<?= htmlspecialchars($featuredGame->getDescription()) ?>"
                      data-controls="<?= htmlspecialchars($featuredGame->getControls()) ?>"
                      data-total-plays="<?= htmlspecialchars($featuredGame->getTotalPlays()) ?>"
                      data-date-released="<?= htmlspecialchars($featuredGame->getDateReleased()) ?>"
                      data-last-updated="<?= htmlspecialchars($featuredGame->getLastUpdated()) ?>"
                      data-thumbnail="<?= htmlspecialchars($featuredGame->getThumbnail()) ?>"
                      data-genre-names="<?= htmlspecialchars($featuredGame->getGenreNames()) ?>"
                      data-avg-rating="<?= htmlspecialchars($featuredGame->getAvgRating()) ?>"
                      data-dev-names="<?= htmlspecialchars($featuredGame->getDevNames()) ?>"
                  >
                      <?= yaw8_thumbnail_html($featuredGame->getThumbnail(), $featuredGame->getTitle(), $index) ?>
                      <div class="game-info">
                          <div class="game-meta">
                              <span class="game-name"><?= htmlspecialchars($featuredGame->getTitle()) ?></span>
                              <span class="game-more">···</span>
                          </div>
                          <div class="game-genre"><?= htmlspecialchars($featuredGame->getGenreNames()) ?></div>
                          <div class="game-footer">
                              <span class="stars"><?= htmlspecialchars($featuredGame->getAvgRating()) ?></span>
                              <button class="btn-play">PLAY</button>
                          </div>
                      </div>
                  </div>
              <?php endforeach; ?>
          </section>
        <?php endif; ?>

      </div>
    </section>

  </main><!-- end .main-col -->


  <!-- ── SIDEBAR ── -->
  <aside class="side-col">

      <!-- GOT A GAME? -->
      <div class="got-game">
        <div class="got-game-row">
          <div class="got-game-text">
            <h3>Got a Game?</h3>
            <p>Share your game with everyone!</p>
          </div>
          <div class="got-game-sprite">👾</div>
        </div>
        <button class="btn-submit-game">Submit Game</button>
      </div>

    <!-- SURPRISE CHALLENGE -->
    <div class="widget">
      <div class="surprise-row">
        <div class="surprise-icon-box">
          <!-- Dice / shuffle icon -->
          <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM7.5 18c-.83 0-1.5-.67-1.5-1.5S6.67 15 7.5 15s1.5.67 1.5 1.5S8.33 18 7.5 18zm0-9C6.67 9 6 8.33 6 7.5S6.67 6 7.5 6 9 6.67 9 7.5 8.33 9 7.5 9zm4.5 4.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm4.5 4.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm0-9c-.83 0-1.5-.67-1.5-1.5S15.67 6 16.5 6s1.5.67 1.5 1.5S17.33 9 16.5 9z"/></svg>
        </div>
        <div>
          <strong>Surprise Challenge</strong>
          <p>Press the button for a random game and challenge!</p>
        </div>
      </div>
      <button class="btn-surprise">Surprise Me!</button>
    </div>
  </aside><!-- end .side-col -->