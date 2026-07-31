<!-- EDIT PROFILE MODAL -->
<div class="modal" id="editProfileModal">
    <div class="modal-overlay" id="editProfileOverlay"></div>
    <div class="modal-content" style="max-width: 480px;">
        <button class="modal-close" id="editProfileClose">
            <svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
        </button>

        <div class="modal-inner" style="padding: 40px;">
            <div style="margin-bottom: 24px;">
                <h1 style="font-size: 22px; font-weight: 800; color: #F5F7FA; margin-bottom: 6px;">Edit Profile</h1>
                <p style="font-size: 13px; color: #8892a4; line-height: 1.6;">Update how your name, username, and bio appear across YA!W8.</p>
            </div>

            <form id="editProfileForm" novalidate>
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Full Name <span class="form-required">*</span></label>
                        <input type="text" class="form-input" id="editName" placeholder="Your full name" />
                    </div>

                    <div class="form-group">
                        <label class="form-label">Username <span class="form-required">*</span></label>
                        <input type="text" class="form-input" id="editUsername" placeholder="yourusername" />
                    </div>

                    <div class="form-group">
                        <label class="form-label">Bio</label>
                        <textarea class="form-input form-textarea" id="editBio" placeholder="Tell other students a bit about yourself…" style="min-height: 80px;"></textarea>
                    </div>

                    <div style="display: flex; gap: 12px; justify-content: flex-end; padding-top: 4px;">
                        <button type="button" class="form-btn-cancel" id="editProfileCancelBtn">Cancel</button>
                        <button type="submit" class="form-btn-submit">
                            <svg viewBox="0 0 24 24" style="width:16px;height:16px;fill:currentColor;flex-shrink:0;"><path d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z"/></svg>
                            Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>