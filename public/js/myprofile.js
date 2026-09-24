// ═══════════════════════════════════════════
// SESSION + STORAGE HELPERS
// ═══════════════════════════════════════════
function getCurrentUser() {
    try {
        const user = JSON.parse(localStorage.getItem('yaw8_user'));
        if (user && user.name) return user;
    } catch (e) {}
    return null;
}

function getProfileMeta() {
    let meta = null;
    try { meta = JSON.parse(localStorage.getItem('yaw8_profileMeta')); } catch (e) {}
    if (!meta) {
        meta = {
            bio: "Just a student building games and figuring things out one bug at a time.",
            joinedAt: new Date().toISOString()
        };
        try { localStorage.setItem('yaw8_profileMeta', JSON.stringify(meta)); } catch (e) {}
    }
    return meta;
}

function saveProfileMeta(meta) {
    try { localStorage.setItem('yaw8_profileMeta', JSON.stringify(meta)); } catch (e) {}
}

function loadMyGames() {
    try {
        const games = JSON.parse(localStorage.getItem('yaw8_myGames'));
        if (Array.isArray(games)) return games;
    } catch (e) {}
    return null;
}

function saveMyGames(games) {
    try { localStorage.setItem('yaw8_myGames', JSON.stringify(games)); } catch (e) {}
}

// Turns a raw stored path (e.g. "uploads/thumbnails/x.jpg" or
// "uploads/feature-graphics/11/fg_x.png", with or without a stray
// leading slash) into a URL the browser can actually load.
function toPublicUploadUrl(rawPath) {
    if (!rawPath) return '';
    const cleaned = String(rawPath).replace(/^\/+/, ''); // drop any leading slash(es)
    return '/Yaw8/public/' + cleaned;
}

function slugify(name) {
    return name.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '') || 'game-' + Date.now();
}

function getInitials(name) {
    if (!name) return '?';
    const parts = name.trim().split(/\s+/);
    const first = parts[0] ? parts[0][0] : '';
    const last = parts.length > 1 ? parts[parts.length - 1][0] : '';
    return (first + last).toUpperCase();
}

function formatMonthYear(isoString) {
    try {
        const d = new Date(isoString);
        return d.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
    } catch (e) {
        return '—';
    }
}

// Grab the user for the top profile info, or default to a generic profile if local storage is cleared
const currentUser = getCurrentUser() || { name: 'Developer', username: 'developer', email: '' }; 
let activeUser = currentUser;

// Wait for the HTML to load, then use the PHP data
document.addEventListener('DOMContentLoaded', () => {
    // 1. Assign activeUser to dbUser if available
    if (typeof dbUser !== 'undefined' && dbUser) {
        activeUser = dbUser;
        // Ensure name/fullname property compatibility
        activeUser.name = dbUser.fullname || dbUser.name || 'Guest';
    }

    const gamesToLoad = typeof myRealGames !== 'undefined' ? myRealGames : [];
    
    renderProfile(activeUser, gamesToLoad);
    wireEditModal();
    initMyGamesPage(gamesToLoad);
});

// ═══════════════════════════════════════════
// PROFILE HERO + ACCOUNT DETAILS
// ═══════════════════════════════════════════

// ── Render (display-only, safe to call repeatedly) ──
function renderProfile(user, myGames) {
    const meta = getProfileMeta();
    updateProfileDisplay(user);
    updateProfileStats(myGames, meta);
}

// Hero + account-details fields only
function updateProfileDisplay(user) {
    document.getElementById('profileAvatar').textContent = getInitials(user.name);
    document.getElementById('profileName').textContent = user.name;
    document.getElementById('profileUsername').textContent = '@' + user.username;
    document.getElementById('profileBio').innerHTML = user.bio && user.bio.trim() !== "" ? user.bio : "<i>User's Bio is currently under construction.. Please try again later!</i>";
    // ── Account details panel ──
    document.getElementById('infoName').textContent = user.name;
    document.getElementById('infoUsername').textContent = '@' + user.username;
    document.getElementById('infoEmail').textContent = user.email;
}

function updateProfileStats(myGames, meta) {
    meta = meta || getProfileMeta();
    const totalPlays = myGames.reduce((sum, g) => sum + (Number(g.plays) || 0), 0);
    const ratedGames = myGames.filter(g => g.rating);
    const avgRating = ratedGames.length
        ? (ratedGames.reduce((sum, g) => sum + parseFloat(g.rating), 0) / ratedGames.length).toFixed(1)
        : null;

    document.getElementById('statGames').textContent = myGames.length;
    document.getElementById('statPlays').textContent = totalPlays;
    document.getElementById('statRating').textContent = avgRating ? '★ ' + avgRating : '–';
    document.getElementById('statSince').textContent = formatMonthYear(meta.joinedAt);
}

// Edit Profile modal
function wireEditModal() {
    const editModal = document.getElementById('editProfileModal');
    const editBtn = document.getElementById('editProfileBtn');
    const editClose = document.getElementById('editProfileClose');
    const editCancel = document.getElementById('editProfileCancelBtn');
    const editOverlay = document.getElementById('editProfileOverlay');
    const editForm = document.getElementById('editProfileForm');

    function openEditModal() {
        // Populate profile fields with the active user's current data
        document.getElementById('editName').value = activeUser.name || '';
        document.getElementById('editUsername').value = activeUser.username || '';
        document.getElementById('editBio').value = activeUser.bio || '';
        
        editModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeEditModal() {
        editModal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    if (editBtn) editBtn.addEventListener('click', openEditModal);
    if (editClose) editClose.addEventListener('click', closeEditModal);
    if (editCancel) editCancel.addEventListener('click', closeEditModal);
    if (editOverlay) editOverlay.addEventListener('click', closeEditModal);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && editModal.classList.contains('active')) closeEditModal();
    });

    if (editForm) {
        editForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const newName = document.getElementById('editName').value.trim();
            const newUsername = document.getElementById('editUsername').value.trim();
            const newBio = document.getElementById('editBio').value.trim();

            if (!newName || !newUsername) {
                alert("Name and username are required.");
                return;
            }

            // Prepare data to send to the PHP backend
            const formData = new FormData();
            formData.append('fullname', newName);
            formData.append('username', newUsername);
            formData.append('bio', newBio);

            fetch('?url=profile/editProfile', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    activeUser.name = newName;
                    activeUser.fullname = newName;
                    activeUser.username = newUsername;
                    activeUser.bio = newBio;
                    updateProfileDisplay(activeUser);
                    closeEditModal();
                } else {
                    alert('Update failed: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error updating profile:', error);
                alert('An error occurred while saving your profile.');
            });
        });
    }
}

// ═══════════════════════════════════════════
// MY GAMES — grid, add/edit, manage, delete
// ═══════════════════════════════════════════
function mapDbGame(g) {
    return {
        id: g.gameId,
        name: g.title,
        description: g.description,
        controls: g.controls,
        genre: g.genre || '',
        status: g.status,
        plays: g.totalPlays,
        dateAdded: g.dateReleased,
        thumbnail: g.thumbnail,
        accessType: g.accessType || 'online',
        allowDownload: g.allowDownload == 1 || g.allowDownload === true,
        featureGraphics: Array.isArray(g.featureGraphics) ? g.featureGraphics : []
    };
}

// Label + CSS class for a game's status badge.
// published -> Published, unlisted -> Unlisted, anything else -> Under Review
function getGameStatusInfo(game) {
    const val = String(game && game.status !== undefined ? game.status : '').toLowerCase().trim();
    if (val === 'published' || val === '1' || val === 'true') return { label: 'Published', cls: 'published' };
    if (val === 'unlisted') return { label: 'Unlisted', cls: 'unlisted' };
    return { label: 'Under Review', cls: 'review' };
}

function initMyGamesPage(initialGames) {
    let games = initialGames;
    let sortMode = 'newest';
    let currentFilter = 'all';

    const grid = document.getElementById('myGamesGrid');
    const emptyState = document.getElementById('myGamesEmpty');
    const countEl = document.getElementById('mgCount');
    const sortDropdownBtn = document.getElementById('sortDropdownBtn');
    const sortDropdownMenu = document.getElementById('sortDropdownMenu');
    const sortDropdownLabel = document.getElementById('sortDropdownLabel');
    const sortOptions = document.querySelectorAll('.sort-option');

    // Helper to evaluate if a game is published safely
    function isGamePublished(game) {
        if (!game) return false;
        const val = String(game.status !== undefined ? game.status : game.is_published || '').toLowerCase().trim();
        return val === 'published' || val === '1' || val === 'true';
    }

    // ── Stats + grid render ──
    function render() {
        // 1. FILTER the games based on currentFilter
        let filteredGames = games;
        const normalizedFilter = (currentFilter || 'all').toLowerCase().trim();

        if (['review', 'under_review', 'under-review', 'pending', 'draft'].includes(normalizedFilter)) {
            filteredGames = games.filter(g => getGameStatusInfo(g).cls === 'review');
        } else if (normalizedFilter === 'published') {
            filteredGames = games.filter(g => isGamePublished(g));
        } else {
            filteredGames = games;
        }

        // 2. Calculate global stats
        const published = games.filter(g => isGamePublished(g));
        const totalPlays = games.reduce((sum, g) => sum + (Number(g.plays) || 0), 0);
        const rated = games.filter(g => g.rating);
        const avgRating = rated.length
            ? (rated.reduce((sum, g) => sum + parseFloat(g.rating), 0) / rated.length).toFixed(1)
            : null;

        if (document.getElementById('mgStatTotal')) document.getElementById('mgStatTotal').textContent = games.length;
        if (document.getElementById('mgStatPublished')) document.getElementById('mgStatPublished').textContent = published.length;
        if (document.getElementById('mgStatPlays')) document.getElementById('mgStatPlays').textContent = totalPlays;
        if (document.getElementById('mgStatRating')) document.getElementById('mgStatRating').textContent = avgRating ? '★ ' + avgRating : '–';
        
        if (countEl) countEl.textContent = filteredGames.length;

        // Keep profile hero stats synced
        updateProfileStats(games);

        // 3. SORT the filtered array
        const sorted = [...filteredGames].sort((a, b) => {
            if (sortMode === 'newest') return new Date(b.dateAdded || 0) - new Date(a.dateAdded || 0);
            if (sortMode === 'oldest') return new Date(a.dateAdded || 0) - new Date(b.dateAdded || 0);
            if (sortMode === 'rating') return (parseFloat(b.rating) || 0) - (parseFloat(a.rating) || 0);
            if (sortMode === 'plays') return (Number(b.plays) || 0) - (Number(a.plays) || 0);
            return 0;
        });

        if (!grid) return;

        if (sorted.length === 0) {
            grid.style.display = 'none';
            if (emptyState) emptyState.style.display = 'flex';
            return;
        }
        grid.style.display = '';
        if (emptyState) emptyState.style.display = 'none';

        // 4. Render cards
        grid.innerHTML = sorted.map((game, index) => {
            const statusInfo = getGameStatusInfo(game);
            const statusLabel = statusInfo.label;
            const statusClass = statusInfo.cls;
            const ratingDisplay = game.rating ? `★ ${parseFloat(game.rating).toFixed(1)}` : 'New';
            
            const safeName = String(game.name || '').replace(/"/g, '&quot;');
            const safeGenre = String(game.genre || '').trim().replace(/"/g, '&quot;');

            let thumbHTML = '';
            if (game.thumbnail && game.thumbnail.includes('.')) {
                const imgPath = toPublicUploadUrl(game.thumbnail);
                thumbHTML = `
                <div class="game-thumb" style="background-image: url('${imgPath}'); background-size: cover; background-position: center;">
                    <span class="status-badge ${statusClass}">${statusLabel}</span>
                </div>`;
            } else {
                const initialsTitle = safeName.toUpperCase().split(' ').join('<br>');
                const colorClasses = ['gt-my-a', 'gt-my-b', 'gt-my-c', 'gt-my-d'];
                const colorClass = colorClasses[index % colorClasses.length];
                thumbHTML = `
                <div class="game-thumb ${colorClass}">
                    <span class="status-badge ${statusClass}">${statusLabel}</span>
                    <div class="game-thumb-title">${initialsTitle}</div>
                </div>`;
            }

            return `
            <div class="game-card" data-game-id="${game.id}">
                ${thumbHTML}
                <div class="game-info">
                    <div class="game-meta">
                        <span class="game-name">${safeName}</span>
                    </div>
                    <div class="game-genre">${safeGenre ? safeGenre + ' · ' : ''}${game.plays || 0} plays</div>
                    <div class="game-footer">
                        <span class="stars">${ratingDisplay}</span>
                        <button class="btn-manage" type="button" onclick="window.openManageModal(${game.id})">Manage</button>
                    </div>
                </div>
            </div>`;
        }).join('');
    }

    // ── Dropdown Event Handlers ──
    if (sortDropdownBtn) {
        sortDropdownBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            sortDropdownBtn.classList.toggle('open');
            if (sortDropdownMenu) sortDropdownMenu.classList.toggle('open');
        });
    }

    // ── Sort Dropdown Items ──
    sortOptions.forEach(function (opt) {
        opt.addEventListener('click', function (e) {
            e.stopPropagation();
            const value = this.getAttribute('data-value');
            if (!value) return;

            if (value === 'review') {
                currentFilter = 'review';
            } else {
                sortMode = value;
                currentFilter = 'all'; 
            }

            document.querySelectorAll('[data-filter]').forEach(btn => {
                const fVal = (btn.getAttribute('data-filter') || '').toLowerCase().trim();
                btn.classList.toggle('active', fVal === 'all');
            });

            if (sortDropdownLabel) sortDropdownLabel.textContent = this.textContent;
            sortOptions.forEach(o => o.classList.remove('active'));
            this.classList.add('active');
            if (sortDropdownBtn) sortDropdownBtn.classList.remove('open');
            if (sortDropdownMenu) sortDropdownMenu.classList.remove('open');

            render();
        });
    });

    // ── Filter Buttons ──
    const filterOptions = document.querySelectorAll('[data-filter]');
    filterOptions.forEach(function (opt) {
        opt.addEventListener('click', function (e) {
            // Ignore if this is part of the sort dropdown to prevent class conflicts
            if (this.classList.contains('sort-option')) return;

            e.preventDefault();
            e.stopPropagation();

            const filterVal = (this.getAttribute('data-filter') || 'all').toLowerCase().trim();
            currentFilter = filterVal;

            filterOptions.forEach(o => {
                if (!o.classList.contains('sort-option')) {
                    o.classList.remove('active');
                }
            });
            this.classList.add('active');

            render();
        });
    });

    // Run initial render
    render();

    // ═══════════════════════════════════════════
    // UPLOAD GAME MODAL
    // ═══════════════════════════════════════════
    const uploadGameModal = document.getElementById('uploadGameModal');
    const uploadGameClose = document.getElementById('uploadGameClose');
    const uploadGameOverlay = document.getElementById('uploadGameOverlay');
    const uploadDropzone = document.getElementById('uploadDropzone');
    const uploadSelectFilesBtn = document.getElementById('uploadSelectFilesBtn');
    const uploadFileInput = document.getElementById('uploadFileInput');

    function openUploadModal() {
        uploadGameModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeUploadModal() {
        uploadGameModal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    uploadGameClose.addEventListener('click', closeUploadModal);
    uploadGameOverlay.addEventListener('click', closeUploadModal);

    uploadSelectFilesBtn.addEventListener('click', function () {
        uploadFileInput.click();
    });

    uploadFileInput.addEventListener('change', function () {
        closeUploadModal();
        openSubmitModal();
    });

    // Dragging files onto the dropzone behaves the same way for now.
    ['dragenter', 'dragover'].forEach(evt => {
        uploadDropzone.addEventListener(evt, function (e) {
            e.preventDefault();
            uploadDropzone.classList.add('drag-over');
        });
    });
    ['dragleave', 'drop'].forEach(evt => {
        uploadDropzone.addEventListener(evt, function (e) {
            e.preventDefault();
            uploadDropzone.classList.remove('drag-over');
        });
    });
    uploadDropzone.addEventListener('drop', function (e) {
        e.preventDefault();
        uploadDropzone.classList.remove('drag-over');

        // Transfer dropped files to input
        if (e.dataTransfer && e.dataTransfer.files.length > 0) {
            uploadFileInput.files = e.dataTransfer.files;
        }

        closeUploadModal();
        openSubmitModal();
    });

    // ═══════════════════════════════════════════
    // SUBMIT GAME MODAL
    // ═══════════════════════════════════════════
    const submitGameModal = document.getElementById('submit-game-modal');
    const submitModalClose = document.getElementById('submitModalClose');
    const submitCancelBtn = document.getElementById('submitCancelBtn');
    const submitGameForm = document.getElementById('submitGameForm');
    const thumbnailStyles = ['gt-my-a', 'gt-my-b', 'gt-my-c', 'gt-my-d'];

    // ── Project Type & Collaborator Visibility ──
    const submitProjectType = document.getElementById('submitProjectType');
    const collabSection = document.getElementById('collabSection');

    submitProjectType.addEventListener('change', function () {
        collabSection.style.display = this.value === 'collab' ? 'block' : 'none';
    });

    // ── Genre Tickboxes ──
    // A row of real checkboxes styled as toggleable chips. wireGenreTickboxes()
    // wires up both the submit and edit modals identically; each returns
    // {getSelected, setSelected} so the rest of the form code can read/write
    // the checked set without caring how the tickboxes are implemented.
    function wireGenreTickboxes(groupEl) {
        if (!groupEl) return null;
        const checkboxes = Array.from(groupEl.querySelectorAll('input[type="checkbox"]'));

        // Keep the chip's visual "active" state in sync with its checkbox —
        // this is the fallback for browsers without CSS :has() support.
        checkboxes.forEach((cb) => {
            const syncActive = () => cb.closest('.genre-chip')?.classList.toggle('active', cb.checked);
            cb.addEventListener('change', syncActive);
            syncActive();
        });

        return {
            getSelected() {
                return checkboxes.filter((cb) => cb.checked).map((cb) => cb.value);
            },
            setSelected(genreNames) {
                const wanted = new Set((genreNames || []).map((g) => String(g).trim()).filter(Boolean));
                checkboxes.forEach((cb) => {
                    cb.checked = wanted.has(cb.value);
                    cb.closest('.genre-chip')?.classList.toggle('active', cb.checked);
                });
            },
        };
    }

    const submitGenreTickboxes = wireGenreTickboxes(document.getElementById('genreChipGroup'));

    function resetGenreChips() {
        if (submitGenreTickboxes) submitGenreTickboxes.setSelected([]);
    }

    // ── Thumbnail preview (right column) ──
    const thumbnailUploadBox = document.getElementById('thumbnailUploadBox');
    const thumbnailFileInput = document.getElementById('thumbnailFileInput');
    const thumbRemoveBtn = document.getElementById('thumbRemoveBtn'); // Grab the new button

    thumbnailUploadBox.addEventListener('click', function () {
        thumbnailFileInput.click();
    });

    thumbnailFileInput.addEventListener('change', function () {
        const file = thumbnailFileInput.files && thumbnailFileInput.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (e) {
            thumbnailUploadBox.style.backgroundImage = `url(${e.target.result})`;
            thumbnailUploadBox.classList.add('has-image');
        };
        reader.readAsDataURL(file);
    });

    // Handle removing the thumbnail
    thumbRemoveBtn.addEventListener('click', function (e) {
        e.stopPropagation(); // Stop the click from opening the file input
        resetThumbnailPreview();
    });

    function resetThumbnailPreview() {
        thumbnailUploadBox.style.backgroundImage = '';
        thumbnailUploadBox.classList.remove('has-image');
        thumbnailFileInput.value = '';
    }

    // ── Availability (Playable Online / Download Only) ──
    function wireAccessTypeToggle(prefix) {
        const toggle = document.getElementById(prefix + 'AccessTypeToggle');
        const hiddenInput = document.getElementById(prefix + 'AccessType');
        const allowDownloadGroup = document.getElementById(prefix + 'AllowDownloadGroup');
        const downloadOnlyNote = document.getElementById(prefix + 'DownloadOnlyNote');
        if (!toggle || !hiddenInput) return null;

        function setAccessType(value) {
            const normalized = value === 'download_only' ? 'download_only' : 'online';
            hiddenInput.value = normalized;
            toggle.querySelectorAll('.access-type-btn').forEach(function (btn) {
                btn.classList.toggle('active', btn.getAttribute('data-value') === normalized);
            });
            const isDownloadOnly = normalized === 'download_only';
            if (allowDownloadGroup) allowDownloadGroup.style.display = isDownloadOnly ? 'none' : 'block';
            if (downloadOnlyNote) downloadOnlyNote.style.display = isDownloadOnly ? 'block' : 'none';
        }

        toggle.querySelectorAll('.access-type-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                setAccessType(btn.getAttribute('data-value'));
            });
        });

        return setAccessType;
    }

    const setSubmitAccessType = wireAccessTypeToggle('submit');

    // ── Feature Graphics (multiple images/videos) ──
    // Each modal tracks two lists: "existing" graphics already saved on the
    // game (only ever populated for Edit, from the game's own data) and
    // "new" files just picked/dropped by the user. Both render in the same
    // preview grid and either can be removed before saving; on submit the
    // remaining existing ones are sent back as "existing_feature_graphics"
    // (so the server knows what to keep) alongside any new files to upload.
    const featureGraphicsFiles = {};
    const featureGraphicsExisting = {};
    const featureGraphicsControllers = {};

    function wireFeatureGraphics(prefix) {
        const dropzone = document.getElementById(prefix + 'FeatureGraphicsDropzone');
        const input = document.getElementById(prefix + 'FeatureGraphicsInput');
        const previewList = document.getElementById(prefix + 'FeatureGraphicsPreviewList');
        if (!dropzone || !input || !previewList) return null;

        featureGraphicsFiles[prefix] = [];
        featureGraphicsExisting[prefix] = [];

        function renderPreviews() {
            previewList.innerHTML = '';

            featureGraphicsExisting[prefix].forEach(function (item, idx) {
                const isVideo = item.mediaType === 'video';
                const url = toPublicUploadUrl(item.filePath);
                const el = document.createElement('div');
                el.className = 'feature-graphics-preview-item';
                el.innerHTML = (isVideo
                    ? '<video src="' + url + '" muted></video>'
                    : '<img src="' + url + '" alt="">') +
                    '<span class="fg-existing-badge">Current</span>' +
                    '<button type="button" class="fg-remove-btn" data-existing-idx="' + idx + '">' +
                    '<svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>' +
                    '</button>';
                previewList.appendChild(el);
            });

            featureGraphicsFiles[prefix].forEach(function (file, idx) {
                const url = URL.createObjectURL(file);
                const isVideo = file.type.indexOf('video/') === 0;
                const el = document.createElement('div');
                el.className = 'feature-graphics-preview-item';
                el.innerHTML = (isVideo
                    ? '<video src="' + url + '" muted></video><span class="fg-video-badge">Video</span>'
                    : '<img src="' + url + '" alt="">') +
                    '<button type="button" class="fg-remove-btn" data-idx="' + idx + '">' +
                    '<svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>' +
                    '</button>';
                previewList.appendChild(el);
            });
        }

        function addFiles(fileList) {
            Array.from(fileList || []).forEach(function (file) {
                const isMedia = file.type.indexOf('image/') === 0 || file.type.indexOf('video/') === 0;
                if (isMedia) featureGraphicsFiles[prefix].push(file);
            });
            renderPreviews();
        }

        dropzone.addEventListener('click', function () { input.click(); });

        dropzone.addEventListener('dragover', function (e) {
            e.preventDefault();
            dropzone.classList.add('dragover');
        });
        dropzone.addEventListener('dragleave', function () {
            dropzone.classList.remove('dragover');
        });
        dropzone.addEventListener('drop', function (e) {
            e.preventDefault();
            dropzone.classList.remove('dragover');
            addFiles(e.dataTransfer.files);
        });

        input.addEventListener('change', function () {
            addFiles(input.files);
            input.value = '';
        });

        previewList.addEventListener('click', function (e) {
            const btn = e.target.closest('.fg-remove-btn');
            if (!btn) return;
            if (btn.hasAttribute('data-existing-idx')) {
                featureGraphicsExisting[prefix].splice(Number(btn.getAttribute('data-existing-idx')), 1);
            } else {
                featureGraphicsFiles[prefix].splice(Number(btn.getAttribute('data-idx')), 1);
            }
            renderPreviews();
        });

        featureGraphicsControllers[prefix] = {
            reset() {
                featureGraphicsFiles[prefix] = [];
                featureGraphicsExisting[prefix] = [];
                renderPreviews();
            },
            setExisting(items) {
                featureGraphicsExisting[prefix] = Array.isArray(items) ? items.slice() : [];
                featureGraphicsFiles[prefix] = [];
                renderPreviews();
            },
            getExisting() {
                return featureGraphicsExisting[prefix].slice();
            },
        };

        // Kept for backwards compatibility with existing callers that just
        // want to clear everything (equivalent to .reset()).
        return featureGraphicsControllers[prefix].reset;
    }

    const resetSubmitFeatureGraphics = wireFeatureGraphics('submit');

    // ── Add Collaborators (left column) ──
    const collabInput = document.getElementById('collabInput');
    const collabAddBtn = document.getElementById('collabAddBtn');
    const collabChipList = document.getElementById('collabChipList');
    let collaborators = [];

    function renderCollabChips() {
        collabChipList.innerHTML = '';
        collaborators.forEach((name, idx) => {
            const chip = document.createElement('span');
            chip.className = 'collab-chip';
            chip.innerHTML = `${escapeHtml(name)} <button type="button" data-idx="${idx}"><svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg></button>`;
            collabChipList.appendChild(chip);
        });
    }

    function escapeHtml(str) {
        return String(str || '').replace(/[&<>"']/g, m => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        })[m]);
    }

    function addCollaborator() {
        const name = collabInput.value.trim();
        if (!name || collaborators.includes(name)) return;
        collaborators.push(name);
        collabInput.value = '';
        renderCollabChips();
    }

    collabAddBtn.addEventListener('click', addCollaborator);
    collabInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addCollaborator();
        }
    });

    collabChipList.addEventListener('click', function (e) {
        const btn = e.target.closest('button[data-idx]');
        if (!btn) return;
        collaborators.splice(Number(btn.getAttribute('data-idx')), 1);
        renderCollabChips();
    });

    function resetCollaborators() {
        collaborators = [];
        if (collabInput) collabInput.value = '';
        renderCollabChips();
    }

    // ── Main Open / Close Modal Logic ──
    function openSubmitModal() {
        submitGameForm.reset();
        resetGenreChips();
        resetThumbnailPreview();
        resetCollaborators();
        if (setSubmitAccessType) setSubmitAccessType('online');
        if (resetSubmitFeatureGraphics) resetSubmitFeatureGraphics();

        document.getElementById('gameDetailsTitle').textContent = 'Submit Your Game';
        document.getElementById('submitGameId').value = '';
        submitProjectType.value = 'solo';
        collabSection.style.display = 'none';

        submitGameModal.classList.add('active');
        submitGameModal.scrollTop = 0;
        document.body.style.overflow = 'hidden';
    }

    function closeSubmitModal() {
        submitGameModal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    document.getElementById('addGameBtn')?.addEventListener('click', () => openUploadModal());
    document.getElementById('emptyAddGameBtn')?.addEventListener('click', () => openUploadModal());

    if (submitModalClose) submitModalClose.addEventListener('click', closeSubmitModal);
    if (submitCancelBtn) submitCancelBtn.addEventListener('click', closeSubmitModal);
    
    const submitOverlayEl = submitGameModal ? submitGameModal.querySelector('.modal-overlay') : null;
    if (submitOverlayEl) submitOverlayEl.addEventListener('click', closeSubmitModal);

    // "Submit Game" / "Submit Your Game" elsewhere on the site link here with
    // ?upload=1 — open the upload flow straight away (once the loading screen is gone).
    if (new URLSearchParams(window.location.search).has('upload')) {
        history.replaceState(null, '', '?url=profile');
        const openFromLink = () => setTimeout(openUploadModal, 700);
        if (document.readyState === 'complete') openFromLink();
        else window.addEventListener('load', openFromLink);
    }

    submitGameForm.addEventListener('submit', function (e) {
        e.preventDefault();

        // 1. Gather Text Data
        const name = document.getElementById('submitGameTitle').value.trim();
        const projectType = document.getElementById('submitProjectType').value;
        const description = document.getElementById('submitDescription').value.trim();
        const controls = document.getElementById('submitControls').value.trim();

        // 2. Gather Files
        const gameZipFile = document.getElementById('uploadFileInput').files[0];
        const thumbnailFile = document.getElementById('thumbnailFileInput').files[0];

        if (!name || !description || !controls) {
            alert("Please fill in the required fields.");
            return;
        }

        // 3. Construct FormData Payload
        const formData = new FormData();
        formData.append('title', name);
        formData.append('description', description);
        formData.append('controls', controls);
        formData.append('projectType', projectType);
        formData.append('genres', JSON.stringify(submitGenreTickboxes ? submitGenreTickboxes.getSelected() : []));

        const activeCollaborators = projectType === 'collab' ? collaborators : [];
        formData.append('collaborators', JSON.stringify(activeCollaborators));

        formData.append('accessType', document.getElementById('submitAccessType').value);
        formData.append('allowDownload', document.getElementById('submitAllowDownload').checked ? '1' : '0');
        (featureGraphicsFiles.submit || []).forEach(function (file) {
            formData.append('feature_graphics[]', file);
        });

        if (gameZipFile) formData.append('game_file', gameZipFile);
        if (thumbnailFile) formData.append('thumbnail', thumbnailFile);

        // 4. Send AJAX Request to the ADD endpoint
        fetch('?url=profile/addGame', {
            method: 'POST',
            body: formData 
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log("Game submitted successfully:", data);

                // Add the newly saved game straight into the local array and
                // re-render — no page refresh needed to see it in the grid.
                if (data.game) {
                    games.push(mapDbGame(data.game));
                    render();
                }

                // Close modal and reset form
                closeSubmitModal();
            } else {
                alert('Submission failed: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error uploading game:', error);
            alert('An error occurred while uploading.');
        });
    });

    // ═══════════════════════════════════════════
    // EDIT GAME DETAILS MODAL
    // ═══════════════════════════════════════════
    const editGameModal = document.getElementById('edit-details-modal');
    const editModalClose = document.getElementById('editModalClose');
    const editCancelBtn = document.getElementById('editCancelBtn');
    const editGameForm = document.getElementById('editGameForm');

    // ── Project Type & Collaborator Visibility ──
    const editProjectType = document.getElementById('editProjectType');
    const editCollabSection = document.getElementById('editCollabSection');

    editProjectType.addEventListener('change', function () {
        editCollabSection.style.display = this.value === 'collab' ? 'block' : 'none';
    });

    // ── Genre Tickboxes ──
    const editGenreTickboxes = wireGenreTickboxes(document.getElementById('editGenreChipGroup'));

    function resetEditGenreChips() {
        if (editGenreTickboxes) editGenreTickboxes.setSelected([]);
    }

    // ── Thumbnail preview (right column) ──
    const editThumbnailUploadBox = document.getElementById('editThumbnailUploadBox');
    const editThumbnailFileInput = document.getElementById('editThumbnailFileInput');
    const editThumbRemoveBtn = document.getElementById('editThumbRemoveBtn');

    editThumbnailUploadBox.addEventListener('click', function () {
        editThumbnailFileInput.click();
    });

    editThumbnailFileInput.addEventListener('change', function () {
        const file = editThumbnailFileInput.files && editThumbnailFileInput.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (e) {
            editThumbnailUploadBox.style.backgroundImage = `url(${e.target.result})`;
            editThumbnailUploadBox.classList.add('has-image');
        };
        reader.readAsDataURL(file);
    });

    editThumbRemoveBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        resetEditThumbnailPreview();
    });

    function resetEditThumbnailPreview() {
        editThumbnailUploadBox.style.backgroundImage = '';
        editThumbnailUploadBox.classList.remove('has-image');
        editThumbnailFileInput.value = '';
    }

    const setEditAccessType = wireAccessTypeToggle('edit');
    wireFeatureGraphics('edit');

    // ── Add Collaborators (left column) ──
    const editCollabInput = document.getElementById('editCollabInput');
    const editCollabAddBtn = document.getElementById('editCollabAddBtn');
    const editCollabChipList = document.getElementById('editCollabChipList');
    let editCollaborators = [];

    function renderEditCollabChips() {
        editCollabChipList.innerHTML = '';
        editCollaborators.forEach((name, idx) => {
            const chip = document.createElement('span');
            chip.className = 'collab-chip';
            chip.innerHTML = `${escapeHtml(name)} <button type="button" data-idx="${idx}"><svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg></button>`;
            editCollabChipList.appendChild(chip);
        });
    }

    function addEditCollaborator() {
        const name = editCollabInput.value.trim();
        if (!name || editCollaborators.includes(name)) return;
        editCollaborators.push(name);
        editCollabInput.value = '';
        renderEditCollabChips();
    }

    editCollabAddBtn.addEventListener('click', addEditCollaborator);
    editCollabInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addEditCollaborator();
        }
    });

    editCollabChipList.addEventListener('click', function (e) {
        const btn = e.target.closest('button[data-idx]');
        if (!btn) return;
        editCollaborators.splice(Number(btn.getAttribute('data-idx')), 1);
        renderEditCollabChips();
    });

    function resetEditCollaborators() {
        editCollaborators = [];
        if (editCollabInput) editCollabInput.value = '';
        renderEditCollabChips();
    }

    // ── Open / Close / Populate ──
    function openEditGameModal(editId) {
        const game = games.find(g => g.id === editId);
        if (!game) return;

        editGameForm.reset();
        resetEditGenreChips();
        resetEditThumbnailPreview();
        resetEditCollaborators();
        if (setEditAccessType) setEditAccessType(game.accessType || 'online');
        const editAllowDownloadEl = document.getElementById('editAllowDownload');
        if (editAllowDownloadEl) editAllowDownloadEl.checked = !!game.allowDownload;

        document.getElementById('editGameId').value = game.id;
        document.getElementById('editGameTitle').value = game.name || '';
        document.getElementById('editDescription').value = game.description || '';
        document.getElementById('editControls').value = game.controls || '';

        // Restore genres
        if (editGenreTickboxes) {
            editGenreTickboxes.setSelected(game.genre ? game.genre.split(', ') : []);
        }

        // Restore the existing thumbnail (if the game already has one) so the
        // box shows the real image instead of the "Add a thumbnail" empty
        // state — the user can still click to replace it, or hit the remove
        // button to clear it entirely.
        if (game.thumbnail) {
            editThumbnailUploadBox.style.backgroundImage = `url(${toPublicUploadUrl(game.thumbnail)})`;
            editThumbnailUploadBox.classList.add('has-image');
        }

        // Restore the existing feature graphics (if any) into the preview
        // grid, so the user can see what's already saved, remove individual
        // ones, and/or add new ones — instead of the box looking empty.
        if (featureGraphicsControllers.edit) {
            featureGraphicsControllers.edit.setExisting(game.featureGraphics || []);
        }

        // Restore collaborators & project type
        if (game.collaborators && game.collaborators.length > 0) {
            editProjectType.value = 'collab';
            editCollabSection.style.display = 'block';
            editCollaborators = [...game.collaborators];
            renderEditCollabChips();
        } else {
            editProjectType.value = 'solo';
            editCollabSection.style.display = 'none';
        }

        editGameModal.classList.add('active');
        editGameModal.scrollTop = 0;
        document.body.style.overflow = 'hidden';
    }

    function closeEditGameModal() {
        editGameModal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    if (editModalClose) editModalClose.addEventListener('click', closeEditGameModal);
    if (editCancelBtn) editCancelBtn.addEventListener('click', closeEditGameModal);
    
    const editOverlayEl = editGameModal ? editGameModal.querySelector('.modal-overlay') : null;
    if (editOverlayEl) editOverlayEl.addEventListener('click', closeEditGameModal);

    editGameForm.addEventListener('submit', function (e) {
        e.preventDefault();

        // 1. Gather Text Data
        const id = document.getElementById('editGameId').value;
        const name = document.getElementById('editGameTitle').value.trim();
        const projectType = editProjectType.value;
        const description = document.getElementById('editDescription').value.trim();
        const controls = document.getElementById('editControls').value.trim();

        if (!id) {
            alert('Missing game id — cannot save changes.');
            return;
        }

        if (!name || !description || !controls) {
            alert("Please fill in the required fields.");
            return;
        }

        // 2. Gather Files (both optional on edit — only sent if replaced)
        const gameZipFile = document.getElementById('editGameFileInput').files[0];
        const thumbnailFile = editThumbnailFileInput.files[0];

        // 3. Construct FormData Payload
        const formData = new FormData();
        formData.append('id', id);
        formData.append('title', name);
        formData.append('description', description);
        formData.append('controls', controls);
        formData.append('projectType', projectType);
        formData.append('genres', JSON.stringify(editGenreTickboxes ? editGenreTickboxes.getSelected() : []));

        const activeCollaborators = projectType === 'collab' ? editCollaborators : [];
        formData.append('collaborators', JSON.stringify(activeCollaborators));

        formData.append('accessType', document.getElementById('editAccessType').value);
        formData.append('allowDownload', document.getElementById('editAllowDownload').checked ? '1' : '0');
        // Whatever "Current" tiles are still in the preview grid (i.e. the
        // user didn't remove them) get kept; new files are appended too.
        formData.append('existing_feature_graphics', JSON.stringify(
            featureGraphicsControllers.edit ? featureGraphicsControllers.edit.getExisting() : []
        ));
        (featureGraphicsFiles.edit || []).forEach(function (file) {
            formData.append('feature_graphics[]', file);
        });

        if (gameZipFile) formData.append('game_file', gameZipFile);
        if (thumbnailFile) formData.append('thumbnail', thumbnailFile);

        // 4. Send AJAX Request to the EDIT endpoint
        fetch('?url=profile/editGame', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log("Game updated successfully:", data);

                // Patch the matching card in the local array and re-render —
                // no page refresh needed to see the changes reflected.
                if (data.game) {
                    const updated = mapDbGame(data.game);
                    const idx = games.findIndex(g => String(g.id) === String(updated.id));
                    if (idx !== -1) {
                        games[idx] = updated;
                    } else {
                        games.push(updated);
                    }
                    render();
                }

                closeEditGameModal();
            } else {
                alert('Update failed: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error updating game:', error);
            alert('An error occurred while updating.');
        });
    });

    // ═══════════════════════════════════════════
    // MANAGE GAME MODAL
    // ═══════════════════════════════════════════
    const manageModal = document.getElementById('manageGameModal');
    const manageOverlay = document.getElementById('manageGameOverlay');
    const manageClose = document.getElementById('manageGameClose');
    const manageInner = document.getElementById('manageGameInner');

    window.openManageModal = function(gameId) {
        const game = games.find(g => g.id === gameId);
        if (!game) return;

        const statusLabel = getGameStatusInfo(game).label;
        const ratingDisplay = game.rating ? '★ ' + game.rating : 'Not yet rated';

        manageInner.innerHTML = `
            <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:16px; margin-bottom: 22px;">
                <div>
                    <h1 style="font-size: 24px; font-weight: 800; color: #F5F7FA; margin-bottom: 6px;">${game.name}</h1>
                    <p style="font-size: 13px; color: #8892a4;">${game.genre ? game.genre + ' · ' : ''}${statusLabel}</p>
                </div>
            </div>

            <div class="manage-modal-stats">
                <div class="stat">
                    <div class="stat-label">Plays</div>
                    <div class="stat-value">${game.plays || 0}</div>
                </div>
                <div class="stat">
                    <div class="stat-label">Rating</div>
                    <div class="stat-value">${ratingDisplay}</div>
                </div>
                <div class="stat">
                    <div class="stat-label">Submitted</div>
                    <div class="stat-value" style="font-size:13px;">${new Date(game.dateAdded).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</div>
                </div>
            </div>

            <div class="modal-box" style="margin-bottom: 22px;">
                <h4>Description</h4>
                <p>${game.description}</p>
            </div>

            <div style="display:flex; gap:12px; justify-content:flex-end;">
                <button class="form-btn-cancel" id="manageDeleteBtn" style="color:#FF4FD8; border-color: rgba(255,79,216,.3);">Delete</button>
                <button class="form-btn-submit" id="manageEditBtn">Edit Game</button>
            </div>
        `;

        manageInner.querySelector('#manageEditBtn').addEventListener('click', function () {
            closeManageModal();
            openEditGameModal(game.id);
        });

        manageInner.querySelector('#manageDeleteBtn').addEventListener('click', function () {
            closeManageModal();
            openDeleteConfirm(game.id);
        });

        manageModal.classList.add('active');
        manageModal.scrollTop = 0;
        document.body.style.overflow = 'hidden';
    }

    function closeManageModal() {
        manageModal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    manageClose.addEventListener('click', closeManageModal);
    manageOverlay.addEventListener('click', closeManageModal);

    // ═══════════════════════════════════════════
    // DELETE CONFIRMATION
    // ═══════════════════════════════════════════
    const deleteModal = document.getElementById('deleteGameModal');
    const deleteOverlay = document.getElementById('deleteGameOverlay');
    const deleteCancel = document.getElementById('deleteGameCancelBtn');
    const deleteConfirm = document.getElementById('deleteGameConfirmBtn');
    let pendingDeleteId = null;

    function openDeleteConfirm(gameId) {
        pendingDeleteId = gameId;
        deleteModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeDeleteConfirm() {
        pendingDeleteId = null;
        deleteModal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    deleteCancel.addEventListener('click', closeDeleteConfirm);
    deleteOverlay.addEventListener('click', closeDeleteConfirm);

    deleteConfirm.addEventListener('click', function () {
        if (!pendingDeleteId) return;

        // 1. Prepare data to send to server
        const formData = new FormData();
        formData.append('id', pendingDeleteId);

        // 2. Call the backend delete endpoint
        fetch('?url=profile/deleteGame', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 3. On success, remove from UI array and re-render
                games = games.filter(g => g.id !== pendingDeleteId);
                closeDeleteConfirm();
                render();
            } else {
                alert('Delete failed: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error deleting game:', error);
            alert('An error occurred while deleting.');
        });
    });

    // ── Escape key closes whichever modal is open ──
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        if (deleteModal.classList.contains('active')) closeDeleteConfirm();
        else if (manageModal.classList.contains('active')) closeManageModal();
        else if (editGameModal.classList.contains('active')) closeEditGameModal();
        else if (submitGameModal.classList.contains('active')) closeSubmitModal();
        else if (uploadGameModal.classList.contains('active')) closeUploadModal();
    });

    render();
}