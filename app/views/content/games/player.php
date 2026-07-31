<!-- ══════════════════════════════════════════
    PAGE BODY — GAME PLAYER PAGE
    Rendered by GamesController::player(); $game is a Game model instance,
    $playable is ['type' => 'html'|'jar'|'unsupported', 'url' => string|null]
    from GameService::resolvePlayable().
══════════════════════════════════════════ -->
<?php
if (!function_exists('yaw8_player_thumb_classes')) {
    // Same fallback thumbnail convention used on the games grid/modal:
    // a real image if one exists, otherwise one of 4 preset gradients.
    function yaw8_player_thumb_classes($thumbnail, $gameId) {
        $colorClasses = ['gt-pixel-drift', 'gt-tower-tactics', 'gt-box-jumper', 'gt-color-clash'];
        if (!empty($thumbnail)) {
            return 'sp-forest';
        }
        $hash = 0;
        foreach (str_split((string) $gameId) as $ch) {
            $hash = ($hash * 31 + ord($ch)) % count($colorClasses);
        }
        return $colorClasses[$hash];
    }
}

$devNames = array_filter(array_map('trim', explode(',', (string) $game->getDevNames())));
$avgRatingDisplay = number_format((float) $game->getAvgRating(), 1);
$userRating = (int) ($game->getUserRating() ?? 0);
$thumbClass = yaw8_player_thumb_classes($game->getThumbnail(), $game->getGameId());
?>

<div class="gp-cabinet" id="gpCabinet"
    data-game-id="<?= htmlspecialchars($game->getGameId()) ?>"
    data-playable-type="<?= htmlspecialchars($playable['type']) ?>"
    data-playable-url="<?= htmlspecialchars($playable['url'] ?? '') ?>"
>
    <div class="gp-cabinet-glow"></div>

    <?php if ($playable['type'] === 'jar'): ?>
        <!-- .jar files can't run in a browser (no Java plugin support left
             anywhere) — offer a download instead of pretending to play it. -->
        <div class="gp-screen">
            <div class="gp-startcard">
                <div class="gp-start-thumb <?= htmlspecialchars($thumbClass) ?>">
                    <?php if (!empty($game->getThumbnail())): ?>
                        <img src="<?= htmlspecialchars($game->getThumbnail()) ?>" alt="<?= htmlspecialchars($game->getTitle()) ?>">
                    <?php endif; ?>
                </div>
                <p style="position:relative;max-width:420px;color:#8892a4;font-size:13px;line-height:1.7;">
                    This game is a Java (.jar) file. Browsers can't run Java applets anymore —
                    download it and run it locally with Java installed.
                </p>
                <a class="gp-play-btn" href="<?= htmlspecialchars($playable['url']) ?>" download style="position:relative;">
                    <svg viewBox="0 0 24 24"><path d="M5 20h14v-2H5v2zM19 9h-4V3H9v6H5l7 7 7-7z"/></svg>
                    Download to Play
                </a>
            </div>
        </div>
    <?php elseif ($playable['type'] === 'unsupported'): ?>
        <div class="gp-screen">
            <div class="gp-startcard">
                <div class="gp-start-thumb <?= htmlspecialchars($thumbClass) ?>">
                    <?php if (!empty($game->getThumbnail())): ?>
                        <img src="<?= htmlspecialchars($game->getThumbnail()) ?>" alt="<?= htmlspecialchars($game->getTitle()) ?>">
                    <?php endif; ?>
                </div>
                <p style="position:relative;max-width:420px;color:#8892a4;font-size:13px;line-height:1.7;">
                    This game isn't available to play yet.
                </p>
            </div>
        </div>
    <?php else: ?>
        <div class="gp-screen" id="gpScreen">
            <!-- Insert-coin / start overlay -->
            <div class="gp-startcard" id="gpStartCard">
                <div class="gp-start-thumb <?= htmlspecialchars($thumbClass) ?>" id="gpStartThumb">
                    <?php if (!empty($game->getThumbnail())): ?>
                        <img src="<?= htmlspecialchars($game->getThumbnail()) ?>" alt="<?= htmlspecialchars($game->getTitle()) ?>">
                    <?php endif; ?>
                </div>
                <button class="gp-play-btn" id="gpPlayBtn">
                    <svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    Play Now
                </button>
            </div>

            <!-- Game iframe -->
            <iframe class="gp-iframe" id="gpFrame" title="Game player" allowfullscreen></iframe>

            <!-- CRT scanline overlay -->
            <div class="gp-scanlines"></div>

            <!-- In-game HUD controls -->
            <div class="gp-hud">
                <button class="gp-hud-btn" id="gpRestartBtn" title="Restart">
                    <svg viewBox="0 0 24 24"><path d="M12 5V1L7 6l5 5V7c3.31 0 6 2.69 6 6s-2.69 6-6 6-6-2.69-6-6H4c0 4.42 3.58 8 8 8s8-3.58 8-8-3.58-8-8-8z"/></svg>
                </button>
                <button class="gp-hud-btn" id="gpMuteBtn" title="Mute">
                    <svg viewBox="0 0 24 24"><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/></svg>
                </button>
                <button class="gp-hud-btn" id="gpFullscreenBtn" title="Fullscreen">
                    <svg viewBox="0 0 24 24"><path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"/></svg>
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- ─────────────────
    TOOLBAR: title / meta / actions
───────────────── -->
<div class="gp-toolbar panel">
    <div class="gp-title-block">
        <h1 class="gp-title" id="gpTitle"><?= htmlspecialchars($game->getTitle()) ?></h1>
        <div class="gp-meta-row">
            <span class="badge gp-genre-badge" id="gpGenre"><?= htmlspecialchars($game->getGenreNames() ?: '—') ?></span>
            <span class="gp-dev">by <span id="gpDev">
                <?php foreach ($devNames as $i => $name): ?>
                    <?php $slug = strtolower(str_replace(' ', '-', $name)); ?>
                    <a class="dev-name-link" href="?url=developers#dev-<?= htmlspecialchars($slug) ?>"><?= htmlspecialchars($name) ?></a><?php if ($i < count($devNames) - 1): ?><span class="dev-sep">, </span><?php endif; ?>
                <?php endforeach; ?>
            </span></span>
            <span id="gpRating" class="gp-rating">★ <?= htmlspecialchars($avgRatingDisplay) ?></span>
            <span id="gpPlays"><?= htmlspecialchars($game->getTotalPlays()) ?> plays</span>
        </div>
    </div>

    <div class="gp-actions">
        <!-- 1-5 star rating (replaces the old Like button) -->
        <div class="gp-star-rating" id="gpStarRating" data-current="<?= $userRating ?>" title="<?= $userRating ? "Your rating: {$userRating}" : 'Rate this game' ?>">
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <button type="button" class="gp-star<?= $i <= $userRating ? ' filled' : '' ?>" data-value="<?= $i ?>" aria-label="Rate <?= $i ?> star<?= $i > 1 ? 's' : '' ?>">★</button>
            <?php endfor; ?>
        </div>
        <button class="gp-action-btn" id="gpFavBtn">
            <svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
            Favorite
        </button>
    </div>
</div>

<!-- ─────────────────
    ABOUT
───────────────── -->
<div class="panel">
    <div class="gp-tab-content active" id="gpTabAbout">
        <p id="gpDescription"><?= nl2br(htmlspecialchars($game->getDescription())) ?></p>
        <div class="gp-info-grid">
            <div>
                <span class="gp-info-label">Released</span>
                <span id="gpReleased"><?= htmlspecialchars($game->getDateReleased() ?: '—') ?></span>
            </div>
            <div>
                <span class="gp-info-label">Last Updated</span>
                <span id="gpUpdated"><?= htmlspecialchars($game->getLastUpdated() ?: '—') ?></span>
            </div>
            <div>
                <span class="gp-info-label">Controls</span>
                <span><?= htmlspecialchars($game->getControls() ?: '—') ?></span>
            </div>
        </div>
    </div>
</div>
