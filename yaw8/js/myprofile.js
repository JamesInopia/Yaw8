// ═══════════════════════════════════════════
// SESSION + STORAGE HELPERS
// (shared by both the profile hero and the games grid below)
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

const currentUser = getCurrentUser();

let activeUser = currentUser;

if (!currentUser || currentUser.isGuest) {
    window.location.replace('login.html?redirect=' + encodeURIComponent('myprofile.html'));
} else {

    let myGames = loadMyGames();

    if (myGames === null) {
        myGames = [
            {
                id: 'demo-' + slugify(activeUser.name) + '-1',
                name: 'Glitch Runner',
                developer: 'by ' + activeUser.name,
                genre: 'Arcade',
                description: 'Glitch Runner is an endless runner where the level itself glitches and rewrites in real time. React fast, adapt faster, and see how far you can push the corrupted track.',
                rating: '4.3',
                plays: 6,
                status: 'published',
                thumbnail: 'gt-my-a',
                dateAdded: new Date(Date.now() - 12 * 24 * 60 * 60 * 1000).toISOString()
            },
            {
                id: 'demo-' + slugify(activeUser.name) + '-2',
                name: 'Loop Garden',
                developer: 'by ' + activeUser.name,
                genre: 'Puzzle',
                description: 'Loop Garden is a calm little puzzle game about arranging looping paths so seeds can travel from sun to soil. Still being polished before it goes public.',
                rating: null,
                plays: 0,
                status: 'review',
                thumbnail: 'gt-my-c',
                dateAdded: new Date(Date.now() - 2 * 24 * 60 * 60 * 1000).toISOString()
            }
        ];
        saveMyGames(myGames);
    }

    renderProfile(activeUser, myGames);
    wireEditModal();
    initMyGamesPage(myGames);
}

// ═══════════════════════════════════════════
// PROFILE HERO + ACCOUNT DETAILS
// ═══════════════════════════════════════════

// ── Render (display-only, safe to call repeatedly) ──
function renderProfile(user, myGames) {
    const meta = getProfileMeta();

    // ── Hero: avatar, name, username, bio ──
    document.getElementById('profileAvatar').textContent = getInitials(user.name);
    document.getElementById('profileName').textContent = user.name;
    document.getElementById('profileUsername').textContent = '@' + user.username;
    document.getElementById('profileBio').textContent = meta.bio;

    // ── Account details panel ──
    document.getElementById('infoName').textContent = user.name;
    document.getElementById('infoUsername').textContent = '@' + user.username;
    document.getElementById('infoEmail').textContent = user.email;

    // ── Stats ──
    updateProfileStats(myGames, meta);
}

// Recomputes just the profile hero's stat row. Called on initial load
// AND whenever the games grid below adds/edits/deletes a game, so the
// two sections never fall out of sync.
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

// ── Edit Profile modal (wired once; always acts on the latest activeUser) ──
function wireEditModal() {
    const editModal = document.getElementById('editProfileModal');
    const editBtn = document.getElementById('editProfileBtn');
    const editClose = document.getElementById('editProfileClose');
    const editCancel = document.getElementById('editProfileCancelBtn');
    const editOverlay = document.getElementById('editProfileOverlay');
    const editForm = document.getElementById('editProfileForm');

    function openEditModal() {
        const meta = getProfileMeta();
        document.getElementById('editName').value = activeUser.name;
        document.getElementById('editUsername').value = activeUser.username;
        document.getElementById('editBio').value = meta.bio;
        editModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeEditModal() {
        editModal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    editBtn.addEventListener('click', openEditModal);
    editClose.addEventListener('click', closeEditModal);
    editCancel.addEventListener('click', closeEditModal);
    editOverlay.addEventListener('click', closeEditModal);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && editModal.classList.contains('active')) closeEditModal();
    });

    editForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const newName = document.getElementById('editName').value.trim();
        const newUsername = document.getElementById('editUsername').value.trim();
        const newBio = document.getElementById('editBio').value.trim();

        if (!newName || !newUsername) return;

        const updatedUser = {
            name: newName,
            username: newUsername,
            email: activeUser.email,
            isGuest: false
        };

        try { localStorage.setItem('yaw8_user', JSON.stringify(updatedUser)); } catch (err) {}

        const meta = getProfileMeta();
        meta.bio = newBio || meta.bio;
        saveProfileMeta(meta);

        activeUser = updatedUser;
        closeEditModal();
        renderProfile(activeUser, loadMyGames() || []);
    });
}

// ═══════════════════════════════════════════
// MY GAMES — grid, add/edit, manage, delete
// ═══════════════════════════════════════════
function initMyGamesPage(initialGames) {
    let games = initialGames;
    let sortMode = 'newest';

    const grid = document.getElementById('myGamesGrid');
    const emptyState = document.getElementById('myGamesEmpty');
    const countEl = document.getElementById('mgCount');
    const sortDropdownBtn = document.getElementById('sortDropdownBtn');
    const sortDropdownMenu = document.getElementById('sortDropdownMenu');
    const sortDropdownLabel = document.getElementById('sortDropdownLabel');
    const sortOptions = document.querySelectorAll('.sort-option');

    // ── Stats + grid render ──
    function render() {
        // Stats (games-section mini stats)
        const published = games.filter(g => g.status === 'published');
        const totalPlays = games.reduce((sum, g) => sum + (Number(g.plays) || 0), 0);
        const rated = games.filter(g => g.rating);
        const avgRating = rated.length
            ? (rated.reduce((sum, g) => sum + parseFloat(g.rating), 0) / rated.length).toFixed(1)
            : null;

        document.getElementById('mgStatTotal').textContent = games.length;
        document.getElementById('mgStatPublished').textContent = published.length;
        document.getElementById('mgStatPlays').textContent = totalPlays;
        document.getElementById('mgStatRating').textContent = avgRating ? '★ ' + avgRating : '–';
        countEl.textContent = games.length;

        // Keep the profile hero's stats in sync with any change here
        updateProfileStats(games);

        // Sort
        const sorted = [...games].sort((a, b) => {
            if (sortMode === 'newest') return new Date(b.dateAdded) - new Date(a.dateAdded);
            if (sortMode === 'oldest') return new Date(a.dateAdded) - new Date(b.dateAdded);
            if (sortMode === 'rating') return (parseFloat(b.rating) || 0) - (parseFloat(a.rating) || 0);
            if (sortMode === 'plays') return (Number(b.plays) || 0) - (Number(a.plays) || 0);
            return 0;
        });

        // Empty state
        if (sorted.length === 0) {
            grid.style.display = 'none';
            emptyState.style.display = 'flex';
            return;
        }
        grid.style.display = '';
        emptyState.style.display = 'none';

        grid.innerHTML = '';
        sorted.forEach(game => {
            const card = document.createElement('div');
            card.className = 'game-card';
            card.setAttribute('data-game-id', game.id);

            const initialsTitle = game.name.toUpperCase().split(' ').join('<br>');
            const statusLabel = game.status === 'published' ? 'Published' : 'Under Review';
            const statusClass = game.status === 'published' ? 'published' : 'review';
            const ratingDisplay = game.rating ? '★ ' + game.rating : 'New';

            card.innerHTML = `
                <div class="game-thumb ${game.thumbnail || 'gt-my-a'}">
                    <span class="status-badge ${statusClass}">${statusLabel}</span>
                    <div class="game-thumb-title">${initialsTitle}</div>
                </div>
                <div class="game-info">
                    <div class="game-meta">
                        <span class="game-name">${game.name}</span>
                    </div>
                    <div class="game-genre">${game.genre} · ${game.plays || 0} plays</div>
                    <div class="game-footer">
                        <span class="stars">${ratingDisplay}</span>
                        <button class="btn-manage" type="button">Manage</button>
                    </div>
                </div>
            `;

            card.addEventListener('click', function () {
                openManageModal(game.id);
            });

            grid.appendChild(card);
        });
    }

    sortDropdownBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        sortDropdownBtn.classList.toggle('open');
        sortDropdownMenu.classList.toggle('open');
    });

    sortOptions.forEach(function (opt) {
        opt.addEventListener('click', function () {
            sortMode = this.getAttribute('data-value');
            sortDropdownLabel.textContent = this.textContent;
            sortOptions.forEach(o => o.classList.remove('active'));
            this.classList.add('active');
            sortDropdownBtn.classList.remove('open');
            sortDropdownMenu.classList.remove('open');
            render();
        });
    });

    document.addEventListener('click', function (e) {
        if (!sortDropdownBtn.contains(e.target) && !sortDropdownMenu.contains(e.target)) {
            sortDropdownBtn.classList.remove('open');
            sortDropdownMenu.classList.remove('open');
        }
    });

    // ═══════════════════════════════════════════
    // ADD / EDIT GAME MODAL
    // ═══════════════════════════════════════════
    const gameFormModal = document.getElementById('gameFormModal');
    const gameFormOverlay = document.getElementById('gameFormOverlay');
    const gameFormClose = document.getElementById('gameFormClose');
    const gameFormCancel = document.getElementById('gameFormCancelBtn');
    const gameForm = document.getElementById('gameForm');
    const gameFormTitle = document.getElementById('gameFormTitle');
    const gameFormSubmitBtn = document.getElementById('gameFormSubmitBtn');

    function openGameForm(editId) {
        gameForm.reset();
        document.getElementById('gameFormId').value = editId || '';

        if (editId) {
            const game = games.find(g => g.id === editId);
            gameFormTitle.textContent = 'Edit Game';
            gameFormSubmitBtn.innerHTML = 'Save Changes';
            document.getElementById('gameFormName').value = game.name;
            document.getElementById('gameFormGenre').value = game.genre;
            document.getElementById('gameFormThumb').value = game.thumbnail || 'gt-my-a';
            document.getElementById('gameFormDescription').value = game.description || '';
        } else {
            gameFormTitle.textContent = 'Submit a New Game';
            gameFormSubmitBtn.innerHTML = 'Submit Game';
        }

        gameFormModal.classList.add('active');
        gameFormModal.scrollTop = 0;
        document.body.style.overflow = 'hidden';
    }

    function closeGameForm() {
        gameFormModal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    gameFormClose.addEventListener('click', closeGameForm);
    gameFormCancel.addEventListener('click', closeGameForm);
    gameFormOverlay.addEventListener('click', closeGameForm);

    gameForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const editId = document.getElementById('gameFormId').value;
        const name = document.getElementById('gameFormName').value.trim();
        const genre = document.getElementById('gameFormGenre').value;
        const thumbnail = document.getElementById('gameFormThumb').value;
        const description = document.getElementById('gameFormDescription').value.trim();

        if (!name || !genre || !description) return;

        if (editId) {
            const game = games.find(g => g.id === editId);
            game.name = name;
            game.genre = genre;
            game.thumbnail = thumbnail;
            game.description = description;
        }

        saveMyGames(games);
        closeGameForm();
        render();
    });

    // ═══════════════════════════════════════════
    // SUBMIT GAME MODAL
    // ═══════════════════════════════════════════
    const submitGameModal = document.getElementById('submit-game-modal');
    const submitModalClose = document.getElementById('submitModalClose');
    const submitCancelBtn = document.getElementById('submitCancelBtn');
    const submitGameForm = document.getElementById('submitGameForm');
    const thumbnailStyles = ['gt-my-a', 'gt-my-b', 'gt-my-c', 'gt-my-d'];

    function openSubmitModal() {
        submitGameForm.reset();
        submitGameModal.classList.add('active');
        submitGameModal.scrollTop = 0;
        document.body.style.overflow = 'hidden';
    }

    function closeSubmitModal() {
        submitGameModal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    document.getElementById('addGameBtn').addEventListener('click', openSubmitModal);
    document.getElementById('emptyAddGameBtn').addEventListener('click', openSubmitModal);
    submitModalClose.addEventListener('click', closeSubmitModal);
    submitCancelBtn.addEventListener('click', closeSubmitModal);
    submitGameModal.querySelector('.modal-overlay').addEventListener('click', closeSubmitModal);

    submitGameForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const name = document.getElementById('submitGameTitle').value.trim();
        const genre = document.getElementById('submitGameGenre').value;
        const devName = document.getElementById('submitDevName').value.trim();
        const gameUrl = document.getElementById('submitGameUrl').value.trim();
        const thumbnailUrl = document.getElementById('submitThumbnail').value.trim();
        const description = document.getElementById('submitDescription').value.trim();

        if (!name || !genre || !devName || !gameUrl || !description) return;

        games.push({
            id: slugify(name) + '-' + Date.now(),
            name: name,
            developer: 'by ' + devName,
            genre: genre,
            description: description,
            rating: null,
            plays: 0,
            status: 'review',
            thumbnail: thumbnailStyles[games.length % thumbnailStyles.length],
            thumbnailUrl: thumbnailUrl,
            gameUrl: gameUrl,
            dateAdded: new Date().toISOString()
        });

        saveMyGames(games);
        closeSubmitModal();
        render();
    });

    // ═══════════════════════════════════════════
    // MANAGE GAME MODAL (view / edit / delete entry point)
    // ═══════════════════════════════════════════
    const manageModal = document.getElementById('manageGameModal');
    const manageOverlay = document.getElementById('manageGameOverlay');
    const manageClose = document.getElementById('manageGameClose');
    const manageInner = document.getElementById('manageGameInner');

    function openManageModal(gameId) {
        const game = games.find(g => g.id === gameId);
        if (!game) return;

        const statusLabel = game.status === 'published' ? 'Published' : 'Under Review';
        const ratingDisplay = game.rating ? '★ ' + game.rating : 'Not yet rated';

        manageInner.innerHTML = `
            <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:16px; margin-bottom: 22px;">
                <div>
                    <h1 style="font-size: 24px; font-weight: 800; color: #F5F7FA; margin-bottom: 6px;">${game.name}</h1>
                    <p style="font-size: 13px; color: #8892a4;">${game.genre} · ${statusLabel}</p>
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
            openGameForm(game.id);
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
        games = games.filter(g => g.id !== pendingDeleteId);
        saveMyGames(games);
        closeDeleteConfirm();
        render();
    });

    // ── Escape key closes whichever modal is open ──
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        if (deleteModal.classList.contains('active')) closeDeleteConfirm();
        else if (manageModal.classList.contains('active')) closeManageModal();
        else if (gameFormModal.classList.contains('active')) closeGameForm();
        else if (submitGameModal.classList.contains('active')) closeSubmitModal();
    });

    render();
}