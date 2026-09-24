<!-- ══════════════════════════════════════════
    PAGE BODY — GAMES PAGE
══════════════════════════════════════════ -->
<?php
if (!function_exists('yaw8_thumbnail_html')) {
    // Renders a game's thumbnail if it has, otherwise a colored placeholder with the title as text
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

if (!function_exists('yaw8_quick_actions_html')) {
    // Favorite + Report icon buttons
    function yaw8_quick_actions_html() {
        return '
                    <div class="game-quick-actions">
                        <button type="button" class="game-quick-btn game-favorite-btn" title="Favorite" aria-label="Favorite">
                            <svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                        </button>
                        <button type="button" class="game-quick-btn game-report-btn" title="Report" aria-label="Report">
                            <svg viewBox="0 0 24 24"><path d="M14.4 6L14 4H5v17h2v-7h5.6l.4 2h7V6z"/></svg>
                        </button>
                    </div>';
    }
}
?>
<!-- ─────────────
    PAGE HEADER
───────────── -->
<section class="page-header">
<div class="page-header-content">
    <h1>All The <span class="accent">Games</span></h1>
    <p>Featured picks, community favorites, and every single game the YA!W8 crew has shipped — all in one place. Filter by genre and find your next favorite.</p>
</div>
</section>

<!-- ─────────────────
    TOP PLAYED GAMES (ALL TIME)
───────────────── -->
<section class="panel" id="top-played-games-all-time">
<div class="section-header">
    <h2 class="section-title">
    <!-- Trophy icon -->
    <svg viewBox="0 0 24 24"><path d="M19 5h-2V3H7v2H5c-1.1 0-2 .9-2 2v1c0 2.55 1.92 4.63 4.39 4.94A5.03 5.03 0 0 0 11 15.9V19H7v2h10v-2h-4v-3.1a5.03 5.03 0 0 0 3.61-2.96C19.08 12.63 21 10.55 21 8V7c0-1.1-.9-2-2-2zM5 8V7h2v3.82C5.84 10.4 5 9.3 5 8zm14 0c0 1.3-.84 2.4-2 2.82V7h2v1z"/></svg>
    Top Played Games
    </h2>
</div>

<div class="games-grid">

    <?php if(isset($topPlayedGames)): ?>
        <section class="top-played-games-all-time">
            <?php foreach($topPlayedGames as $index => $topPlayedGame): ?>
                <?php $rank = $index + 1; ?>
                <div class="game-card"
                    data-game-id="<?= htmlspecialchars($topPlayedGame->getGameId()) ?>"
                    data-title="<?= htmlspecialchars($topPlayedGame->getTitle()) ?>"
                    data-description="<?= htmlspecialchars($topPlayedGame->getDescription()) ?>"
                    data-controls="<?= htmlspecialchars($topPlayedGame->getControls()) ?>"
                    data-total-plays="<?= htmlspecialchars($topPlayedGame->getTotalPlays()) ?>"
                    data-date-released="<?= htmlspecialchars($topPlayedGame->getDateReleased()) ?>"
                    data-last-updated="<?= htmlspecialchars($topPlayedGame->getLastUpdated()) ?>"
                    data-thumbnail="<?= htmlspecialchars($topPlayedGame->getThumbnail()) ?>"
                    data-genre-names="<?= htmlspecialchars($topPlayedGame->getGenreNames()) ?>"
                    data-avg-rating="<?= htmlspecialchars($topPlayedGame->getAvgRating()) ?>"
                    data-dev-names="<?= htmlspecialchars($topPlayedGame->getDevNames()) ?>"
                    data-access-type="<?= htmlspecialchars($topPlayedGame->getAccessType()) ?>"
                >
                    <?= yaw8_quick_actions_html() ?>
                    <?php
                        $topHasThumb = !empty($topPlayedGame->getThumbnail());
                        $topColorClasses = ['gt-pixel-drift', 'gt-tower-tactics', 'gt-box-jumper', 'gt-color-clash'];
                        $topThumbClass = $topHasThumb ? 'sp-forest' : $topColorClasses[$index % count($topColorClasses)];
                    ?>
                    <div class="game-thumb <?= $topThumbClass ?>">
                        <span class="rank-badge rank-<?= $rank ?>"><?= $rank ?></span>
                        <?php if ($topHasThumb): ?>
                            <img src="<?= htmlspecialchars($topPlayedGame->getThumbnail()) ?>" alt="<?= htmlspecialchars($topPlayedGame->getTitle()) ?>">
                        <?php else: ?>
                            <div class="game-thumb-title"><?= str_replace(' ', '<br>', strtoupper(htmlspecialchars($topPlayedGame->getTitle()))) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="game-info">
                        <div class="game-meta">
                            <span class="game-name"><?= htmlspecialchars($topPlayedGame->getTitle()) ?></span>
                            <span class="game-more">···</span>
                        </div>
                        <div class="game-genre"><?= htmlspecialchars($topPlayedGame->getGenreNames()) ?></div>
                        <div class="game-footer">
                            <span class="stars"><?= htmlspecialchars($topPlayedGame->getAvgRating()) ?></span>
                            <button class="btn-play">PLAY</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
    
</div>
</section>

<!-- ─────────────────
    TOP PLAYED GAMES (WEEKLY)
───────────────── -->
<section class="panel" id="top-played-games-weekly">
<div class="section-header">
    <h2 class="section-title">
    <!-- Gamepad icon -->
    <svg viewBox="0 0 24 24"><path d="M15 7.5V2H9v5.5l3 3 3-3zM7.5 9H2v6h5.5l3-3-3-3zM9 16.5V22h6v-5.5l-3-3-3 3zM16.5 9l-3 3 3 3H22V9h-5.5z"/></svg>
    Top Rated Games (Weekly)
    </h2>
</div>

<div class="games-grid">

    <?php if(isset($topWeeklyPlayedGames)): ?>
        <section class="top-played-games-weekly">
            <?php foreach($topWeeklyPlayedGames as $index => $topWeeklyPlayedGame): ?>
                <div class="game-card"
                    data-game-id="<?= htmlspecialchars($topWeeklyPlayedGame->getGameId()) ?>"
                    data-title="<?= htmlspecialchars($topWeeklyPlayedGame->getTitle()) ?>"
                    data-description="<?= htmlspecialchars($topWeeklyPlayedGame->getDescription()) ?>"
                    data-controls="<?= htmlspecialchars($topWeeklyPlayedGame->getControls()) ?>"
                    data-total-plays="<?= htmlspecialchars($topWeeklyPlayedGame->getTotalPlays()) ?>"
                    data-date-released="<?= htmlspecialchars($topWeeklyPlayedGame->getDateReleased()) ?>"
                    data-last-updated="<?= htmlspecialchars($topWeeklyPlayedGame->getLastUpdated()) ?>"
                    data-thumbnail="<?= htmlspecialchars($topWeeklyPlayedGame->getThumbnail()) ?>"
                    data-genre-names="<?= htmlspecialchars($topWeeklyPlayedGame->getGenreNames()) ?>"
                    data-avg-rating="<?= htmlspecialchars($topWeeklyPlayedGame->getAvgRating()) ?>"
                    data-dev-names="<?= htmlspecialchars($topWeeklyPlayedGame->getDevNames()) ?>"
                >
                    <?= yaw8_thumbnail_html($topWeeklyPlayedGame->getThumbnail(), $topWeeklyPlayedGame->getTitle(), $index) ?>
                    <div class="game-info">
                        <div class="game-meta">
                            <span class="game-name"><?= htmlspecialchars($topWeeklyPlayedGame->getTitle()) ?></span>
                            <span class="game-more">···</span>
                        </div>
                        <div class="game-genre"><?= htmlspecialchars($topWeeklyPlayedGame->getGenreNames()) ?></div>
                        <div class="game-footer">
                            <span class="stars"><?= htmlspecialchars($topWeeklyPlayedGame->getAvgRating()) ?></span>
                            <button class="btn-play">PLAY</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

</div>
</section>

<!-- ─────────────────
    TOP RATED GAMES (ALL TIME)
───────────────── -->
<section class="panel" id="top-all-time">
<div class="section-header">
    <h2 class="section-title">
    <!-- Trophy icon -->
    <svg viewBox="0 0 24 24"><path d="M19 5h-2V3H7v2H5c-1.1 0-2 .9-2 2v1c0 2.55 1.92 4.63 4.39 4.94A5.03 5.03 0 0 0 11 15.9V19H7v2h10v-2h-4v-3.1a5.03 5.03 0 0 0 3.61-2.96C19.08 12.63 21 10.55 21 8V7c0-1.1-.9-2-2-2zM5 8V7h2v3.82C5.84 10.4 5 9.3 5 8zm14 0c0 1.3-.84 2.4-2 2.82V7h2v1z"/></svg>
    Top Rating Games (All Time)
    </h2>
</div>

<div class="games-grid">

    <?php if(isset($topRatedGames)): ?>
        <section class="top-played-games-all-time">
            <?php foreach($topRatedGames as $index => $topRatedGame): ?>
                <?php $rank = $index + 1; ?>
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
                    data-access-type="<?= htmlspecialchars($featuredGame->getAccessType()) ?>"
                    data-game-id="<?= htmlspecialchars($topRatedGame->getGameId()) ?>"
                    data-title="<?= htmlspecialchars($topRatedGame->getTitle()) ?>"
                    data-description="<?= htmlspecialchars($topRatedGame->getDescription()) ?>"
                    data-controls="<?= htmlspecialchars($topRatedGame->getControls()) ?>"
                    data-total-plays="<?= htmlspecialchars($topRatedGame->getTotalPlays()) ?>"
                    data-date-released="<?= htmlspecialchars($topRatedGame->getDateReleased()) ?>"
                    data-last-updated="<?= htmlspecialchars($topRatedGame->getLastUpdated()) ?>"
                    data-thumbnail="<?= htmlspecialchars($topRatedGame->getThumbnail()) ?>"
                    data-genre-names="<?= htmlspecialchars($topRatedGame->getGenreNames()) ?>"
                    data-avg-rating="<?= htmlspecialchars($topRatedGame->getAvgRating()) ?>"
                    data-dev-names="<?= htmlspecialchars($topRatedGame->getDevNames()) ?>"
                >
                    <?= yaw8_quick_actions_html() ?>
                    <?= yaw8_thumbnail_html($featuredGame->getThumbnail(), $featuredGame->getTitle(), $index) ?>
                    <?php
                        $topHasThumb = !empty($topRatedGame->getThumbnail());
                        $topColorClasses = ['gt-pixel-drift', 'gt-tower-tactics', 'gt-box-jumper', 'gt-color-clash'];
                        $topThumbClass = $topHasThumb ? 'sp-forest' : $topColorClasses[$index % count($topColorClasses)];
                    ?>
                    <div class="game-thumb <?= $topThumbClass ?>">
                        <span class="rank-badge rank-<?= $rank ?>"><?= $rank ?></span>
                        <?php if ($topHasThumb): ?>
                            <img src="<?= htmlspecialchars($topRatedGame->getThumbnail()) ?>" alt="<?= htmlspecialchars($topRatedGame->getTitle()) ?>">
                        <?php else: ?>
                            <div class="game-thumb-title"><?= str_replace(' ', '<br>', strtoupper(htmlspecialchars($topRatedGame->getTitle()))) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="game-info">
                        <div class="game-meta">
                            <span class="game-name"><?= htmlspecialchars($topRatedGame->getTitle()) ?></span>
                            <span class="game-more">···</span>
                        </div>
                        <div class="game-genre"><?= htmlspecialchars($topRatedGame->getGenreNames()) ?></div>
                        <div class="game-footer">
                            <span class="stars"><?= htmlspecialchars($topRatedGame->getAvgRating()) ?></span>
                            <button class="btn-play">PLAY</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
    
</div>
</section>

<!-- ─────────────────
    TOP RATED GAMES (WEEKLY)
───────────────── -->
<section class="panel" id="top-weekly">
<div class="section-header">
    <h2 class="section-title">
    <!-- Gamepad icon -->
    <svg viewBox="0 0 24 24"><path d="M15 7.5V2H9v5.5l3 3 3-3zM7.5 9H2v6h5.5l3-3-3-3zM9 16.5V22h6v-5.5l-3-3-3 3zM16.5 9l-3 3 3 3H22V9h-5.5z"/></svg>
    Top Rating Games (Weekly)
    </h2>
</div>

<div class="games-grid">

    <?php if(isset($topWeeklyRatedGames)): ?>
        <section class="top-played-games-weekly">
            <?php foreach($topWeeklyRatedGames as $index => $topWeeklyRatedGame): ?>
                <div class="game-card"
                    data-game-id="<?= htmlspecialchars($topWeeklyRatedGame->getGameId()) ?>"
                    data-title="<?= htmlspecialchars($topWeeklyRatedGame->getTitle()) ?>"
                    data-description="<?= htmlspecialchars($topWeeklyRatedGame->getDescription()) ?>"
                    data-controls="<?= htmlspecialchars($topWeeklyRatedGame->getControls()) ?>"
                    data-total-plays="<?= htmlspecialchars($topWeeklyRatedGame->getTotalPlays()) ?>"
                    data-date-released="<?= htmlspecialchars($topWeeklyRatedGame->getDateReleased()) ?>"
                    data-last-updated="<?= htmlspecialchars($topWeeklyRatedGame->getLastUpdated()) ?>"
                    data-thumbnail="<?= htmlspecialchars($topWeeklyRatedGame->getThumbnail()) ?>"
                    data-genre-names="<?= htmlspecialchars($topWeeklyRatedGame->getGenreNames()) ?>"
                    data-avg-rating="<?= htmlspecialchars($topWeeklyRatedGame->getAvgRating()) ?>"
                    data-dev-names="<?= htmlspecialchars($topWeeklyRatedGame->getDevNames()) ?>"
                >
                    <?= yaw8_thumbnail_html($topWeeklyRatedGame->getThumbnail(), $topWeeklyRatedGame->getTitle(), $index) ?>
                    <div class="game-info">
                        <div class="game-meta">
                            <span class="game-name"><?= htmlspecialchars($topWeeklyRatedGame->getTitle()) ?></span>
                            <span class="game-more">···</span>
                        </div>
                        <div class="game-genre"><?= htmlspecialchars($topWeeklyRatedGame->getGenreNames()) ?></div>
                        <div class="game-footer">
                            <span class="stars"><?= htmlspecialchars($topWeeklyRatedGame->getAvgRating()) ?></span>
                            <button class="btn-play">PLAY</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

</div>
</section>

<!-- ─────────────────
    TRENDING GAMES
───────────────── -->
<section class="panel" id="trending">
<div class="section-header">
    <h2 class="section-title">
    <!-- Gamepad icon -->
    <svg viewBox="0 0 24 24"><path d="M15 7.5V2H9v5.5l3 3 3-3zM7.5 9H2v6h5.5l3-3-3-3zM9 16.5V22h6v-5.5l-3-3-3 3zM16.5 9l-3 3 3 3H22V9h-5.5z"/></svg>
    Trending Games
    </h2>
</div>

<div class="games-grid">

    <?php if(isset($trendingGames)): ?>
        <section class="top-played-games-weekly">
            <?php foreach($trendingGames as $index => $trendingGame): ?>
                <div class="game-card"
                    data-game-id="<?= htmlspecialchars($trendingGame->getGameId()) ?>"
                    data-title="<?= htmlspecialchars($trendingGame->getTitle()) ?>"
                    data-description="<?= htmlspecialchars($trendingGame->getDescription()) ?>"
                    data-controls="<?= htmlspecialchars($trendingGame->getControls()) ?>"
                    data-total-plays="<?= htmlspecialchars($trendingGame->getTotalPlays()) ?>"
                    data-date-released="<?= htmlspecialchars($trendingGame->getDateReleased()) ?>"
                    data-last-updated="<?= htmlspecialchars($trendingGame->getLastUpdated()) ?>"
                    data-thumbnail="<?= htmlspecialchars($trendingGame->getThumbnail()) ?>"
                    data-genre-names="<?= htmlspecialchars($trendingGame->getGenreNames()) ?>"
                    data-avg-rating="<?= htmlspecialchars($trendingGame->getAvgRating()) ?>"
                    data-dev-names="<?= htmlspecialchars($trendingGame->getDevNames()) ?>"
                >
                    <?= yaw8_thumbnail_html($trendingGame->getThumbnail(), $trendingGame->getTitle(), $index) ?>
                    <div class="game-info">
                        <div class="game-meta">
                            <span class="game-name"><?= htmlspecialchars($trendingGame->getTitle()) ?></span>
                            <span class="game-more">···</span>
                        </div>
                        <div class="game-genre"><?= htmlspecialchars($trendingGame->getGenreNames()) ?></div>
                        <div class="game-footer">
                            <span class="stars"><?= htmlspecialchars($trendingGame->getAvgRating()) ?></span>
                            <button class="btn-play">PLAY</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

</div>
</section>

<!-- ─────────────
    ALL GAMES
───────────── -->
<section class="panel" id="all-games">
<div class="section-header">
    <h2 class="section-title">
    <!-- Grid icon -->
    <svg viewBox="0 0 24 24"><path d="M3 3h8v8H3V3zm10 0h8v8h-8V3zM3 13h8v8H3v-8zm10 0h8v8h-8v-8z"/></svg>
    All Games
    </h2>
</div>

<div class="filter-chips" id="genreFilters"></div>

<div class="games-grid" id="allGamesGrid">

    <?php if(isset($games)): ?>
        <section class="top-played-games">
            <?php foreach($games as $index => $game): ?>
                <div class="game-card"
                    data-game-id="<?= htmlspecialchars($game->getGameId()) ?>"
                    data-title="<?= htmlspecialchars($game->getTitle()) ?>"
                    data-description="<?= htmlspecialchars($game->getDescription()) ?>"
                    data-controls="<?= htmlspecialchars($game->getControls()) ?>"
                    data-total-plays="<?= htmlspecialchars($game->getTotalPlays()) ?>"
                    data-date-released="<?= htmlspecialchars($game->getDateReleased()) ?>"
                    data-last-updated="<?= htmlspecialchars($game->getLastUpdated()) ?>"
                    data-thumbnail="<?= htmlspecialchars($game->getThumbnail()) ?>"
                    data-genre-names="<?= htmlspecialchars($game->getGenreNames()) ?>"
                    data-avg-rating="<?= htmlspecialchars($game->getAvgRating()) ?>"
                    data-dev-names="<?= htmlspecialchars($game->getDevNames()) ?>"
                    data-access-type="<?= htmlspecialchars($game->getAccessType()) ?>"
                >
                    <?= yaw8_quick_actions_html() ?>
                    <?= yaw8_thumbnail_html($game->getThumbnail(), $game->getTitle(), $index) ?>
                    <div class="game-info">
                        <div class="game-meta">
                            <span class="game-name"><?= htmlspecialchars($game->getTitle()) ?></span>
                            <span class="game-more">···</span>
                        </div>
                        <div class="game-genre"><?= htmlspecialchars($game->getGenreNames()) ?></div>
                        <div class="game-footer">
                            <span class="stars"><?= htmlspecialchars($game->getAvgRating()) ?></span>
                            <button class="btn-play">PLAY</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

</div>
</section>

</div>