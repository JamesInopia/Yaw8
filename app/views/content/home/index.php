<!-- ══════════════════════════════════════════
    PAGE BODY (HOME VIEW)
══════════════════════════════════════════ -->
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
              <?php foreach($featuredGames as $featuredGame): ?>
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
                      <div class="game-thumb sp-forest">
                          <img src="<?= htmlspecialchars($featuredGame->getThumbnail()) ?>" alt="<?= htmlspecialchars($featuredGame->getTitle()) ?>">
                      </div>
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

    <!-- DAILY CHALLENGE -->
    <div class="widget">
      <div class="daily-row">
        <div class="trophy-box">
          <!-- Trophy icon -->
          <svg viewBox="0 0 24 24"><path d="M19 5h-2V3H7v2H5c-1.1 0-2 .9-2 2v1c0 2.55 1.92 4.63 4.39 4.94.63 1.5 1.98 2.63 3.61 2.96V19H7v2h10v-2h-4v-3.1c1.63-.33 2.98-1.46 3.61-2.96C19.08 12.63 21 10.55 21 8V7c0-1.1-.9-2-2-2zM5 8V7h2v3.82C5.84 10.4 5 9.3 5 8zm14 0c0 1.3-.84 2.4-2 2.82V7h2v1z"/></svg>
        </div>
        <div>
          <strong>Daily Challenge</strong>
          <p>Play a game and complete the challenge to earn XP!</p>
        </div>
      </div>
      <div class="challenge-task">
        <p>Play a Platformer game without losing once.</p>
        <div class="progress-bar"><div class="progress-fill"></div></div>
        <div class="progress-label">0 / 1</div>
        <div class="reward">Reward: 50 XP</div>
      </div>
    </div>

    <!-- TOP PLAYED THIS WEEK -->
    <div class="widget">
      <div class="widget-title">
        <!-- Fire icon -->
        <svg viewBox="0 0 24 24"><path d="M13.5.67s.74 2.65.74 4.8c0 2.06-1.35 3.73-3.41 3.73-2.07 0-3.63-1.67-3.63-3.73l.03-.36C5.21 7.51 4 10.62 4 14c0 4.42 3.58 8 8 8s8-3.58 8-8C20 8.61 17.41 3.8 13.5.67zM11.71 19c-1.78 0-3.22-1.4-3.22-3.14 0-1.62 1.05-2.76 2.81-3.12 1.77-.36 3.6-1.21 4.62-2.58.39 1.29.59 2.65.59 4.04 0 2.65-2.15 4.8-4.8 4.8z"/></svg>
        Top Played This Week
      </div>
      <div class="leaderboard">
        <div class="lb-row">
          <span class="lb-rank">1</span>
          <div class="lb-thumb lb-t1">PD</div>
          <span class="lb-name">Pixel Drift</span>
          <span class="lb-plays">2.1K plays</span>
        </div>
        <div class="lb-row">
          <span class="lb-rank">2</span>
          <div class="lb-thumb lb-t2">TT</div>
          <span class="lb-name">Tower Tactics</span>
          <span class="lb-plays">1.8K plays</span>
        </div>
        <div class="lb-row">
          <span class="lb-rank">3</span>
          <div class="lb-thumb lb-t3">BJ</div>
          <span class="lb-name">Box Jumper</span>
          <span class="lb-plays">1.6K plays</span>
        </div>
      </div>
    </div>

    <!-- TOP DEVELOPERS -->
    <div class="widget">
      <div class="section-header" style="margin-bottom:12px">
        <div class="widget-title" style="margin:0">
          <!-- People icon -->
          <svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
          Top Developers
        </div>
        <a class="view-all" href="?url=developers">View All →</a>
      </div>
      <div class="dev-list">
        <div class="dev-row">
          <div class="dev-avatar da-1">JA</div>
          <div class="dev-info">
            <div class="dev-name">James Inopia</div>
            <div class="dev-games">12 Games</div>
          </div>
          <div class="dev-rating">★ 4.8</div>
        </div>
        <div class="dev-row">
          <div class="dev-avatar da-2">MC</div>
          <div class="dev-info">
            <div class="dev-name">Christine Arenal</div>
            <div class="dev-games">8 Games</div>
          </div>
          <div class="dev-rating">★ 4.7</div>
        </div>
        <div class="dev-row">
          <div class="dev-avatar da-3">NG</div>
          <div class="dev-info">
            <div class="dev-name">Noah Lonoy</div>
            <div class="dev-games">6 Games</div>
          </div>
          <div class="dev-rating">★ 4.6</div>
        </div>
        <div class="dev-row">
          <div class="dev-avatar da-4">HT</div>
          <div class="dev-info">
            <div class="dev-name">Harvey Ablen</div>
            <div class="dev-games">5 Games</div>
          </div>
          <div class="dev-rating">★ 4.5</div>
        </div>
      </div>
    </div>

  </aside><!-- end .side-col -->