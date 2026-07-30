<!-- EDIT GAME DETAILS MODAL -->
<div id="edit-details-modal" class="modal">
    <div class="modal-overlay"></div>
    <div class="modal-content" style="max-width: 900px; height: 85vh; display: flex; flex-direction: column;">
        <button class="modal-close" id="editModalClose">
            <svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
        </button>

        <div class="modal-inner" style="display: flex; flex-direction: column; height: 100%; padding: 40px;">
            <div style="margin-bottom: 24px; flex-shrink: 0;">
                <h1 id="editGameDetailsTitle" style="font-size: 26px; font-weight: 800; color: #F5F7FA; margin-bottom: 6px;">Edit Game Details</h1>
                <p style="font-size: 13px; color: #8892a4; line-height: 1.6;">Update your game settings, thumbnail, or upload a new game build file.</p>
            </div>

            <form id="editGameForm" novalidate style="display: flex; flex-direction: column; overflow: hidden; flex: 1;">
                <input type="hidden" id="editGameId" value="" />
                
                <!-- MAIN WRAPPER -->
                <div style="display: flex; gap: 40px; flex: 1; overflow-y: auto; padding-right: 15px; scroll-behavior: smooth;">
                    
                    <!-- LEFT COLUMN -->
                    <div style="flex: 2; display: flex; flex-direction: column; gap: 18px;">
                        <div class="form-group">
                            <label class="form-label">Game Title <span class="form-required">*</span></label>
                            <input type="text" class="form-input" placeholder="e.g. Pixel Drift" id="editGameTitle" />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Description <span class="form-required">*</span></label>
                            <textarea class="form-input form-textarea" placeholder="Describe your game in a few sentences…" id="editDescription" style="min-height: 160px;"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Controls <span class="form-required">*</span></label>
                            <input type="text" class="form-input" placeholder="e.g. WASD to move, Space to jump" id="editControls" />
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div class="form-group">
                                <label class="form-label">Project Type <span class="form-required">*</span></label>
                                <select class="form-input form-select" id="editProjectType">
                                    <option value="solo">Solo Project</option>
                                    <option value="collab">Collaborative Project</option>
                                </select>
                            </div>
                        </div>

                        <!-- Collaborators Section -->
                        <div class="form-group" id="editCollabSection">
                            <label class="form-label">Add Collaborators</label>
                            <div style="display: flex; gap: 8px; margin-bottom: 12px;">
                                <input type="text" class="form-input" id="editCollabInput" placeholder="Username or email of co-developer" />
                                <button type="button" class="collab-add-btn" id="editCollabAddBtn" style="padding: 0 16px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; cursor: pointer; color: white;">
                                    <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: currentColor;"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                                </button>
                            </div>
                            <div class="collab-chip-list" id="editCollabChipList" style="display: flex; flex-wrap: wrap; gap: 8px;"></div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN -->
                    <div style="flex: 1; display: flex; flex-direction: column; gap: 24px;">
                        <!-- THUMBNAIL -->
                        <div class="form-group">
                            <label class="form-label">Thumbnail</label>
                            
                            <div class="thumb-frame-box" id="editThumbnailUploadBox" style="position: relative; overflow: hidden; cursor: pointer; background-size: cover; background-position: center; border: 2px dashed rgba(255,255,255,0.2); border-radius: 8px; height: 160px; display: flex; align-items: center; justify-content: center;">
                                
                                <button type="button" id="editThumbRemoveBtn" style="display: none; position: absolute; top: 8px; right: 8px; background: rgba(0,0,0,0.6); color: #fff; border: 1px solid rgba(255,255,255,0.2); border-radius: 50%; width: 26px; height: 26px; align-items: center; justify-content: center; cursor: pointer; z-index: 10; padding: 0;">
                                    <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: currentColor;"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                                </button>

                                <div class="thumb-frame-inner" style="width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; transition: background 0.2s;">
                                    <div id="editThumbStateEmpty" style="display: flex; flex-direction: column; align-items: center; pointer-events: none;">
                                        <svg class="plus-icon" viewBox="0 0 24 24" style="width: 24px; height: 24px; stroke: #8892a4; stroke-width: 2; margin-bottom: 8px;"><line x1="12" y1="4" x2="12" y2="20"/><line x1="4" y1="12" x2="20" y2="12"/></svg>
                                        <span style="color: #8892a4; font-size: 12px;">Add a thumbnail</span>
                                    </div>

                                    <div id="editThumbStateFilled" style="display: none; flex-direction: column; align-items: center; pointer-events: none;">
                                        <svg viewBox="0 0 24 24" style="width: 24px; height: 24px; fill: #fff; margin-bottom: 8px;"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                        <span style="color: #fff; font-size: 13px; font-weight: 600;">Change thumbnail</span>
                                    </div>
                                </div>
                                <input type="file" id="editThumbnailFileInput" accept="image/*" hidden />
                            </div>
                        </div>

                        <!-- REPLACE GAME FILE INPUT -->
                        <div class="form-group">
                            <label class="form-label">Replace Game File (.zip)</label>
                            <input type="file" class="form-input" id="editGameFileInput" accept=".zip,application/zip,application/x-zip-compressed" style="padding: 8px 12px; cursor: pointer;" />
                            <span style="font-size: 11px; color: #8892a4; margin-top: 4px;">Upload a new zip file to replace the existing file in the database.</span>
                        </div>

                        <!-- GENRES -->
                        <div class="genre-box">
                            <h4 style="color: #F5F7FA; font-size: 14px; margin-bottom: 12px;">Genres</h4>
                            <div style="display: flex; gap: 8px; margin-bottom: 12px;">
                                <!-- Changed from <input type="text"> to <select> -->
                                <select class="form-input form-select" id="editGenreInput">
                                    <option value="" disabled selected>Select a genre...</option>
                                    <!-- Example PHP loop to populate from DB -->
                                    <?php foreach ($availableGenres as $dbGenre): ?>
                                        <option value="<?= htmlspecialchars($dbGenre['name']) ?>"><?= htmlspecialchars($dbGenre['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="genre-add-btn" id="editGenreAddBtn" style="padding: 0 16px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; cursor: pointer; color: white;">
                                    <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: currentColor;"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                                </button>
                            </div>
                            <div class="genre-chip-list" id="editGenreChipList" style="display: flex; flex-wrap: wrap; gap: 8px;"></div>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end; padding-top: 24px; margin-top: 10px; border-top: 1px solid rgba(255,255,255,0.1); flex-shrink: 0;">
                    <button type="button" class="form-btn-cancel" id="editCancelBtn">Cancel</button>
                    <button type="submit" class="form-btn-submit">
                        <svg viewBox="0 0 24 24" style="width:16px;height:16px;fill:currentColor;flex-shrink:0;"><path d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z"/></svg>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>