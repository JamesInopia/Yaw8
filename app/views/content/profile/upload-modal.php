<!-- UPLOAD GAME MODAL -->
<div class="modal" id="uploadGameModal">
    <div class="modal-overlay" id="uploadGameOverlay"></div>
    <div class="modal-content" style="max-width: 500px; text-align: center;">
        <button class="modal-close" id="uploadGameClose">
            <svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
        </button>

        <div class="modal-inner" style="padding: 40px;">
            <div style="margin-bottom: 24px;">
                <h1 style="font-size: 22px; font-weight: 800; color: #F5F7FA;">Upload your game</h1>
            </div>

            <div class="upload-dropzone" id="uploadDropzone" style="margin-bottom: 20px;">
                <div class="upload-dropzone-icon">
                    <svg viewBox="0 0 24 24" style="width: 40px; height: 40px; fill: #8892a4;"><path d="M5 20h14v-2H5v2zM12 2 6 8h3.5v6h5V8H18l-6-6z"/></svg>
                </div>
                <p class="upload-dropzone-text" style="color: #8892a4; margin-bottom: 15px;">Drag and drop game files to upload</p>
                <button type="button" class="upload-select-btn" id="uploadSelectFilesBtn" style="padding: 10px 20px; background: var(--cyan); color: #000; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">Select files</button>
                <input type="file" accept=".zip,application/zip,application/x-zip-compressed" id="uploadFileInput" multiple hidden />
            </div>

            <div>
                <p style="font-size: 11px; color: #8892a4;">By submitting your game to YA!W8, you confirm you have the rights to share this content and agree to our <a href="#" style="color: var(--cyan);">Terms of Service</a> and <a href="#" style="color: var(--cyan);">Community Guidelines</a>.</p>
            </div>
        </div>
    </div>
</div>