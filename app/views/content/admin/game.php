<!-- ══════════════════════════════════════════
    PAGE BODY — ADMIN GAME DETAILS
══════════════════════════════════════════ -->
<div id="adminGamePage" data-game-id="<?= (int) $gameId ?>" style="display:contents;">

    <a class="admin-back" href="?url=admin#games">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
        Back to dashboard
    </a>

    <section class="panel game-detail" id="gameDetailPanel" aria-live="polite">
        <div class="section-header">
            <h2 class="section-title">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 7.5V2H9v5.5l3 3 3-3zM7.5 9H2v6h5.5l3-3-3-3zM9 16.5V22h6v-5.5l-3-3-3 3zM16.5 9l-3 3 3 3H22V9h-5.5z"/></svg>
                Game Details
            </h2>
            <span class="admin-status" id="detailStatusBadge" hidden></span>
        </div>

        <div class="admin-loading" id="detailLoading">Loading game…</div>
        <div class="admin-error" id="detailError" hidden></div>

        <div id="detailBody" hidden>
            <div class="detail-top">
                <div class="detail-thumb" id="detailThumb"></div>

                <div class="detail-fields">
                    <div class="detail-field">
                        <span class="detail-label">Title</span>
                        <span class="detail-value" id="dTitle"></span>
                    </div>
                    <div class="detail-field">
                        <span class="detail-label">Rating</span>
                        <span class="detail-value" id="dRating"></span>
                    </div>
                    <div class="detail-field">
                        <span class="detail-label">Developer(s)</span>
                        <span class="detail-value" id="dDevs"></span>
                    </div>
                    <div class="detail-field">
                        <span class="detail-label">Total Plays</span>
                        <span class="detail-value" id="dPlays"></span>
                    </div>
                    <div class="detail-field">
                        <span class="detail-label">Project Type</span>
                        <span class="detail-value" id="dProjectType"></span>
                    </div>
                    <div class="detail-field">
                        <span class="detail-label">Last Updated</span>
                        <span class="detail-value" id="dUpdated"></span>
                    </div>
                    <div class="detail-field">
                        <span class="detail-label">Genre</span>
                        <span class="detail-value" id="dGenre"></span>
                    </div>
                    <div class="detail-field">
                        <span class="detail-label">Released</span>
                        <span class="detail-value" id="dReleased"></span>
                    </div>
                </div>
            </div>

            <div class="detail-block">
                <span class="detail-label">Description</span>
                <p class="detail-text" id="dDescription"></p>
            </div>
            <div class="detail-block">
                <span class="detail-label">Controls</span>
                <p class="detail-text" id="dControls"></p>
            </div>

            <!-- Status actions -->
            <div class="detail-actions" role="group" aria-label="Change game status">
                <button type="button" class="admin-btn admin-btn-green" data-set-status="published">Confirm publication</button>
                <button type="button" class="admin-btn admin-btn-amber" data-set-status="under_review">Put under review</button>
                <button type="button" class="admin-btn admin-btn-slate" data-set-status="unlisted">Unlist</button>
                <a class="admin-btn admin-btn-ghost" id="previewLink" href="#" target="_blank" rel="noopener">Preview game ↗</a>
            </div>
        </div>
    </section>

    <!-- REPORTS -->
    <section class="panel reports-panel" id="reportsPanel" hidden>
        <div class="section-header">
            <h2 class="section-title">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14.4 6 14 4H5v17h2v-7h5.6l.4 2h7V6z"/></svg>
                Reports <span class="reports-open-count" id="reportsOpenCount"></span>
            </h2>
            <button type="button" class="admin-btn admin-btn-outline" id="resolveAllBtn" disabled>Resolve all</button>
        </div>

        <div class="reports-list" id="reportsList"></div>
    </section>
</div>

<!-- Confirm dialog for "Resolve all" -->
<div class="admin-dialog" id="confirmDialog" role="dialog" aria-modal="true" aria-labelledby="confirmTitle" hidden>
    <div class="admin-dialog-overlay" data-close-dialog></div>
    <div class="admin-dialog-box">
        <h3 id="confirmTitle">Resolve all reports?</h3>
        <p id="confirmText"></p>
        <div class="admin-dialog-actions">
            <button type="button" class="admin-btn admin-btn-ghost" id="confirmCancel" data-close-dialog>Cancel</button>
            <button type="button" class="admin-btn admin-btn-green" id="confirmOk">Yes, resolve all</button>
        </div>
    </div>
</div>
