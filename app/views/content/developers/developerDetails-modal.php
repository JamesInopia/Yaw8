<!-- AFTER (fixed) -->
<div id="devModal" class="modal">
    <div class="modal-overlay" id="devModalOverlay"></div>
    <div class="modal-content">
        <button id="devModalClose" class="modal-close">&times;</button>
        <div id="devModalInner" class="modal-inner">
            
            <!-- Avatar and Header Info -->
            <div class="dev-modal-avatar-row">
                <div id="modalDevAvatar" class="dev-modal-avatar"></div>
                <div class="dev-modal-name">
                    <h1 id="modalDevName"></h1>
                    <p>Member since <span id="modalDevJoined"></span></p>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="modal-stats">
                <div class="stat">
                    <span class="stat-label">Games</span>
                    <span id="modalDevGames" class="stat-value"></span>
                </div>
                <div class="stat">
                    <span class="stat-label">Rating</span>
                    <span id="modalDevRating" class="stat-value"></span>
                </div>
            </div>

            <!-- Bio Section -->
            <div class="modal-section">
                <p id="modalDevBio"></p>
            </div>

            <!-- Games Grid Section -->
            <div class="modal-section">
                <h3>Games Made</h3>
                <div id="modalDevGamesList" class="dev-games-grid"></div>
            </div>
            
        </div>
    </div>
</div>