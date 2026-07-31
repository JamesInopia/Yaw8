<!-- ══════════════════════════════════════════
    PAGE BODY — GAMES PAGE
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
<!-- ─────────────
    PAGE HEADER
───────────── -->
<section class="page-header"> <!--Change the image/background-->
<div class="page-header-content">
    <h1>All The <span class="accent">Games</span></h1>
    <p>Featured picks, community favorites, and every single game the YA!W8 crew has shipped — all in one place. Filter by genre and find your next favorite.</p>
</div>
</section>

<!-- ─────────────────
    TOP PLAYED GAMES
───────────────── -->
<section class="panel" id="top-played">
<div class="section-header">
    <h2 class="section-title">
    <!-- Trophy icon -->
    <svg viewBox="0 0 24 24"><path d="M19 5h-2V3H7v2H5c-1.1 0-2 .9-2 2v1c0 2.55 1.92 4.63 4.39 4.94A5.03 5.03 0 0 0 11 15.9V19H7v2h10v-2h-4v-3.1a5.03 5.03 0 0 0 3.61-2.96C19.08 12.63 21 10.55 21 8V7c0-1.1-.9-2-2-2zM5 8V7h2v3.82C5.84 10.4 5 9.3 5 8zm14 0c0 1.3-.84 2.4-2 2.82V7h2v1z"/></svg>
    Top Played Games
    </h2>
</div>

<div class="games-grid">

    <?php if(isset($topPlayedGames)): ?>
        <section class="top-played-games">
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
                >
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
    FEATURED GAMES
───────────────── -->
<section class="panel" id="featured-games">
<div class="section-header">
    <h2 class="section-title">
    <!-- Gamepad icon -->
    <svg viewBox="0 0 24 24"><path d="M15 7.5V2H9v5.5l3 3 3-3zM7.5 9H2v6h5.5l3-3-3-3zM9 16.5V22h6v-5.5l-3-3-3 3zM16.5 9l-3 3 3 3H22V9h-5.5z"/></svg>
    Featured Games
    </h2>
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
                >
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

</div><!-- end .page-wrap-single -->