<!-- DELETE GAME CONFIRMATION MODAL -->
<div class="modal" id="deleteGameModal">
    <div class="modal-overlay" id="deleteGameOverlay"></div>
    <div class="modal-content" style="max-width: 380px; margin-top: 120px;">
        <div class="modal-inner" style="padding: 32px;">
            <h1 style="font-size: 19px; font-weight: 800; color: #F5F7FA; margin-bottom: 8px;">Delete this game?</h1>
            <p style="font-size: 13px; color: #8892a4; line-height: 1.6; margin-bottom: 24px;">This can't be undone. The game and its stats will be permanently removed.</p>
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button class="form-btn-cancel" id="deleteGameCancelBtn">Cancel</button>
                <button class="form-btn-submit btn-delete-confirm" id="deleteGameConfirmBtn" style="background: #FF4FD8; box-shadow: 0 0 22px rgba(255, 79, 216, 0.3);">
                    <svg viewBox="0 0 24 24" style="width:16px;height:16px;fill:currentColor;flex-shrink:0;"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>