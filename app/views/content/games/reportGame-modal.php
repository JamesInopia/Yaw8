<!-- ══════════════════════════════════════════
REPORT GAME MODAL (shared across pages)
══════════════════════════════════════════ -->
<div id="report-game-modal" class="modal">
<div class="modal-overlay"></div>
<div class="modal-content modal-content-sm">
<button class="modal-close" id="reportModalClose">
    <svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
</button>

<div class="modal-inner" style="padding: 40px;">
    <h1 class="modal-title-lg">Report Game</h1>
    <p class="modal-subtitle" style="margin-bottom: 24px;">
        Help us keep YA!W8 safe — tell us what's wrong with <strong id="reportGameName">this game</strong>.
    </p>

    <form id="reportGameForm" novalidate class="form-stack">
        <input type="hidden" id="reportGameId" value="" />

        <div class="form-group">
            <label class="form-label">Reason <span class="form-required">*</span></label>
            <select class="form-input form-select" id="reportReason" required>
                <option value="" disabled selected>Select a reason...</option>
                <option value="Inappropriate or Explicit Content">Inappropriate or Explicit Content</option>
                <option value="Hate Speech in Game">Hate Speech in Game</option>
                <option value="Fake Game / Misleading">Fake Game / Misleading</option>
                <option value="Slop / Low-Effort Content">Slop / Low-Effort Content</option>
                <option value="Stolen Game / Unauthorized Upload">Stolen Game / Unauthorized Upload</option>
                <option value="Malware, Virus, or Adware">Malware, Virus, or Adware</option>
                <option value="other">Others</option>
            </select>
        </div>

        <div class="form-group" id="reportOtherGroup" style="display: none;">
            <label class="form-label">Please specify <span class="form-required">*</span></label>
            <input type="text" class="form-input" id="reportOtherInput" maxlength="100" placeholder="Describe the reason in a few words..." />
        </div>

        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea class="form-input form-textarea" id="reportDescription" placeholder="Add any extra details that could help us review this report..." style="min-height: 120px;"></textarea>
        </div>

        <p class="form-error" id="reportFormError" style="display: none;"></p>

        <div class="form-actions">
            <button type="button" class="form-btn-cancel" id="reportCancelBtn">Cancel</button>
            <button type="submit" class="form-btn-submit" id="reportSubmitBtn">
                <svg viewBox="0 0 24 24" style="width:16px;height:16px;fill:currentColor;flex-shrink:0;"><path d="M14.4 6L14 4H5v17h2v-7h5.6l.4 2h7V6z"/></svg>
                Submit Report
            </button>
        </div>
    </form>
</div>

</div>
</div>
