<!-- ══════════════════════════════════════════
    PAGE BODY — DOWNLOADER PAGE
══════════════════════════════════════════ -->
<?php
if (!function_exists('yaw8_dl_thumb_classes')) {
    // Renders a game's thumbnail if it has, otherwise a colored placeholder with the title as text
    function yaw8_dl_thumb_classes($thumbnail, $gameId) {
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
$thumbClass = yaw8_dl_thumb_classes($game->getThumbnail(), $game->getGameId());
$featureGraphics = $game->getFeatureGraphics() ?: [];
?>

<div class="dl-cabinet" id="dlCabinet" data-game-id="<?= htmlspecialchars($game->getGameId()) ?>">
    <!-- Media header: big preview + thumbnail filmstrip, Steam-style -->
    <div class="dl-media">
        <div class="dl-media-main <?= htmlspecialchars($thumbClass) ?>" id="dlMediaMain">
            <?php if (empty($featureGraphics) && !empty($game->getThumbnail())): ?>
                <img src="<?= htmlspecialchars($game->getThumbnail()) ?>" alt="<?= htmlspecialchars($game->getTitle()) ?>">
            <?php elseif (empty($featureGraphics)): ?>
                <div class="game-thumb-title"><?= nl2br(htmlspecialchars(strtoupper(str_replace(' ', "\n", $game->getTitle())))) ?></div>
            <?php endif; ?>
        </div>

        <button type="button" class="dl-media-arrow dl-media-prev" id="dlMediaPrev" aria-label="Previous">
            <svg viewBox="0 0 24 24"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
        </button>
        <button type="button" class="dl-media-arrow dl-media-next" id="dlMediaNext" aria-label="Next">
            <svg viewBox="0 0 24 24"><path d="M8.59 16.59L10 18l6-6-6-6-1.41 1.41L13.17 12z"/></svg>
        </button>

        <?php if (!empty($featureGraphics)): ?>
        <div class="dl-media-strip" id="dlMediaStrip">
            <?php foreach ($featureGraphics as $i => $fg): ?>
                <button type="button" class="dl-media-strip-item<?= $i === 0 ? ' active' : '' ?>" data-index="<?= $i ?>" data-type="<?= htmlspecialchars($fg->getMediaType()) ?>" data-path="<?= htmlspecialchars($fg->getFilePath()) ?>">
                    <?php if ($fg->isVideo()): ?>
                        <video src="<?= htmlspecialchars($fg->getFilePath()) ?>" muted preload="metadata"></video>
                        <span class="dl-strip-play"><svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg></span>
                    <?php else: ?>
                        <img src="<?= htmlspecialchars($fg->getFilePath()) ?>" alt="">
                    <?php endif; ?>
                </button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- ─────────────────
        TOOLBAR
    ───────────────── -->
    <div class="gp-toolbar panel">
        <div class="gp-title-block">
            <h1 class="gp-title" id="dlTitle"><?= htmlspecialchars($game->getTitle()) ?></h1>
            <div class="gp-meta-row">
                <span class="badge gp-genre-badge" id="dlGenre"><?= htmlspecialchars($game->getGenreNames() ?: '—') ?></span>
                <span class="gp-dev">by <span id="dlDev">
                    <?php foreach ($devNames as $i => $name): ?>
                        <?php $slug = strtolower(str_replace(' ', '-', $name)); ?>
                        <a class="dev-name-link" href="?url=developers#dev-<?= htmlspecialchars($slug) ?>"><?= htmlspecialchars($name) ?></a><?php if ($i < count($devNames) - 1): ?><span class="dev-sep">, </span><?php endif; ?>
                    <?php endforeach; ?>
                </span></span>
                <span id="dlRating" class="gp-rating">★ <?= htmlspecialchars($avgRatingDisplay) ?></span>
            </div>
        </div>

        <div class="gp-actions">
            <!-- 1-5 star rating -->
            <div class="gp-star-rating" id="dlStarRating" data-current="<?= $userRating ?>" title="<?= $userRating ? "Your rating: {$userRating}" : 'Rate this game' ?>">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <button type="button" class="gp-star<?= $i <= $userRating ? ' filled' : '' ?>" data-value="<?= $i ?>" aria-label="Rate <?= $i ?> star<?= $i > 1 ? 's' : '' ?>">★</button>
                <?php endfor; ?>
            </div>
            <button class="gp-action-btn" id="gpFavBtn">
                <svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                Favorite
            </button>
            <button class="gp-action-btn" id="gpDownloadBtn">
                <svg viewBox="0 0 24 24"><path d="M5 20h14v-2H5v2zM13 4h-2v8H7l5 5 5-5h-4V4z"/></svg>
                Download
            </button>
            <button class="gp-action-btn danger" id="gpReportBtn">
                <svg viewBox="0 0 24 24"><path d="M14.4 6L14 4H5v17h2v-7h5.6l.4 2h7V6z"/></svg>
                Report
            </button>
        </div>
    </div>

    <!-- ─────────────────
        ABOUT
    ───────────────── -->
    <div class="panel">
        <div class="gp-tab-content active" id="dlTabAbout">
            <p id="dlDescription"><?= nl2br(htmlspecialchars($game->getDescription())) ?></p>
            <div class="gp-info-grid">
                <div>
                    <span class="gp-info-label">Released</span>
                    <span id="dlReleased"><?= htmlspecialchars($game->getDateReleased() ?: '—') ?></span>
                </div>
                <div>
                    <span class="gp-info-label">Last Updated</span>
                    <span id="dlUpdated"><?= htmlspecialchars($game->getLastUpdated() ?: '—') ?></span>
                </div>
                <div>
                    <span class="gp-info-label">Controls</span>
                    <span><?= htmlspecialchars($game->getControls() ?: '—') ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
