<!-- ══════════════════════════════════════════
GAME DETAILS MODAL
══════════════════════════════════════════ --> 
<div class="modal-inner">
  <!-- Game Header -->
  <div class="modal-header">
    <div class="modal-thumb" id="modalThumbnail"></div>
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
    </div>
  </div>

  <!-- Row: Description (left) + Game Released & Last Updated (right, stacked) -->
  <div class="modal-info-row">
    <!-- Description box -->
    <div class="modal-box modal-description-box">
      <h3>Description</h3>
      <p id="modalDescription"></p>
    </div>

    <!-- Right column: two stacked date boxes -->
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