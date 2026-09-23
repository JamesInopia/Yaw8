<!-- ══════════════════════════════════════════
GAME DETAILS MODAL
══════════════════════════════════════════ --> 
<div class="modal-inner">
  <!-- Game Header -->
  <div class="modal-header">
    <div class="modal-thumb-carousel" id="modalThumbCarousel">
      <div class="modal-thumb" id="modalThumbnail"></div>
      <button type="button" class="modal-thumb-arrow modal-thumb-prev" id="modalThumbPrev" aria-label="Previous feature graphic">
        <svg viewBox="0 0 24 24"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
      </button>
      <button type="button" class="modal-thumb-arrow modal-thumb-next" id="modalThumbNext" aria-label="Next feature graphic">
        <svg viewBox="0 0 24 24"><path d="M8.59 16.59L10 18l6-6-6-6-1.41 1.41L13.17 12z"/></svg>
      </button>
    </div>
    <div class="modal-header-info">
      <h1 id="modalGameName"></h1>
      <div class="modal-developer" id="modalDeveloperWrap"></div>
      <div class="modal-stats">
        <div class="stat">
          <span class="stat-label">Rating</span>
          <span class="stat-value" id="modalRating"></span>
        </div>
        <div class="stat">
          <span class="stat-label">Plays</span>
          <span class="stat-value" id="modalPlays"></span>
        </div>
        <div class="stat">
          <span class="stat-label">Genre</span>
          <span class="stat-value" id="modalGenre"></span>
        </div>
      </div>
      <button class="btn-play-modal">PLAY NOW</button>

      <!-- Rate / Favorite / Report -->
      <div class="modal-action-row" id="modalActionRow">
        <div class="modal-star-rating" id="modalStarRating" data-current="0" title="Rate this game">
          <button type="button" class="modal-star" data-value="1" aria-label="Rate 1 star">★</button>
          <button type="button" class="modal-star" data-value="2" aria-label="Rate 2 stars">★</button>
          <button type="button" class="modal-star" data-value="3" aria-label="Rate 3 stars">★</button>
          <button type="button" class="modal-star" data-value="4" aria-label="Rate 4 stars">★</button>
          <button type="button" class="modal-star" data-value="5" aria-label="Rate 5 stars">★</button>
        </div>
        <button type="button" class="modal-action-btn" id="modalFavBtn">
          <svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
          <span>Favorite</span>
        </button>
        <button type="button" class="modal-action-btn" id="modalDownloadBtn" style="display: none;">
          <svg viewBox="0 0 24 24"><path d="M5 20h14v-2H5v2zM13 4h-2v8H7l5 5 5-5h-4V4z"/></svg>
          <span>Download</span>
        </button>
        <button type="button" class="modal-action-btn" id="modalReportBtn">
          <svg viewBox="0 0 24 24"><path d="M14.4 6L14 4H5v17h2v-7h5.6l.4 2h7V6z"/></svg>
          <span>Report</span>
        </button>
      </div>
    </div>
  </div>

  <!-- Game Details -->
  <div class="modal-info-row">
    <!-- Description box -->
    <div class="modal-box modal-description-box">
      <h3>Description</h3>
      <p id="modalDescription"></p>
    </div>
    <div class="modal-dates-col">
      <div class="modal-box modal-date-box">
        <h4>Last Updated</h4>
        <p id="modalGameLastUpdated"></p>
      </div>

      <div class="modal-box modal-date-box">
        <h4>Game Released</h4>
        <p id="modalGameReleased"></p>
      </div>
    </div>
  </div>
</div>