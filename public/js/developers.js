// ═══════════════════════════════════════════
// SAFE DATABASE REFERENCE
// Ensures the script doesn't crash if the PHP 
// controller passes nothing.
// ═══════════════════════════════════════════
const safeDevDatabase = typeof developerDatabase !== 'undefined' ? developerDatabase : [];

function initials(name) {
    if (!name) return 'U';
    return name.split(' ').map(p => p[0]).join('').slice(0, 2).toUpperCase();
}

// ═══════════════════════════════════════════
// ALL DEVELOPERS — filter/sort state
// ═══════════════════════════════════════════
let currentSearch = '';
let currentSort = 'rating';   // 'rating' | 'games' | 'name'
let currentScope = 'all';     // 'all' | 'top'

function getFilteredSortedDevs() {
    let devs = [...safeDevDatabase]; 

    if (currentScope === 'top') {
        devs = devs.filter(d => d.top);
    }

    const query = currentSearch.trim().toLowerCase();
    if (query) {
        devs = devs.filter(d => (d.name || '').toLowerCase().includes(query));
    }

    switch (currentSort) {
        case 'games':
            devs.sort((a, b) => (b.games || 0) - (a.games || 0));
            break;
        case 'name':
            devs.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
            break;
        case 'rating':
        default:
            devs.sort((a, b) => parseFloat(b.rating || 0) - parseFloat(a.rating || 0));
    }

    return devs;
}

// ═══════════════════════════════════════════
// RENDER — TOP DEVELOPERS
// ═══════════════════════════════════════════
function renderTopDevelopers() {
    const grid = document.getElementById('topDevGrid');
    if (!grid) return;
    grid.innerHTML = '';

    const topDevs = safeDevDatabase.filter(d => d.top);

    if (topDevs.length === 0) {
        grid.innerHTML = `<p style="color:var(--muted); font-size:13px; grid-column: 1 / -1;">No top developers found.</p>`;
        return;
    }

    topDevs.forEach((dev, i) => {
        const card = document.createElement('div');
        card.className = 'top-dev-card';
        card.innerHTML = `
            <div class="top-dev-rank">${i + 1}</div>
            <div class="top-dev-avatar ${dev.avatarClass || 'da-1'}">${initials(dev.name)}</div>
            <div class="top-dev-name">${dev.name || 'Unknown'}</div>
            <div class="top-dev-role">${dev.role || 'Developer'}</div>
            <div class="top-dev-metrics">
                <div class="top-dev-metric">
                    <span class="val">${dev.games || 0}</span>
                    <span class="key">Games</span>
                </div>
                <div class="top-dev-metric rating">
                    <span class="val">★ ${dev.rating || '0.0'}</span>
                    <span class="key">Rating</span>
                </div>
            </div>
            <button class="btn-view-profile" data-dev-id="${dev.id}">View Profile</button>
        `;
        grid.appendChild(card);
    });
}

// ═══════════════════════════════════════════
// RENDER — ALL DEVELOPERS
// ═══════════════════════════════════════════
function renderAllDevelopers() {
    const grid = document.getElementById('allDevGrid');
    if (!grid) return;
    grid.innerHTML = '';

    const devs = getFilteredSortedDevs();

    if (devs.length === 0) {
        grid.innerHTML = `<p style="color:var(--muted); font-size:13px; grid-column: 1 / -1;">No developers match your criteria.</p>`;
        return;
    }

    devs.forEach(dev => {
        const card = document.createElement('div');
        card.className = 'all-dev-card';
        card.id = `dev-${dev.id}`;
        card.setAttribute('data-dev-id', dev.id);
        card.innerHTML = `
            <div class="all-dev-avatar ${dev.avatarClass || 'da-1'}">${initials(dev.name)}</div>
            <div class="all-dev-info">
                <div class="all-dev-name">${dev.name || 'Unknown'}</div>
                <div class="all-dev-games">${dev.games || 0} Games · ${dev.role || 'Developer'}</div>
            </div>
            <div class="all-dev-rating">★ ${dev.rating || '0.0'}</div>
        `;
        grid.appendChild(card);
    });
}

// ═══════════════════════════════════════════
// MODALS — shared open/close helpers
// ═══════════════════════════════════════════
function openModal(modalEl) {
    if (!modalEl) return;
    modalEl.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalEl) {
    if (!modalEl) return;
    modalEl.classList.remove('active');
    document.body.style.overflow = 'auto';

    if (window.location.hash.startsWith('#dev-')) {
        history.replaceState(null, '', window.location.pathname + window.location.search);
    }
}

// ═══════════════════════════════════════════
// DEVELOPER PROFILE MODAL
// ═══════════════════════════════════════════
const devModal = document.getElementById('devModal');
const devModalInner = document.getElementById('devModalInner');
const devModalClose = document.getElementById('devModalClose');
const devModalOverlay = document.getElementById('devModalOverlay');

function openDeveloperModal(devId) {
    const dev = safeDevDatabase.find(d => d.id == devId);
    if (!dev || !devModalInner) return;

    // Properly target the IDs defined in developerDetails-modal.php
    const avatarEl = document.getElementById('modalDevAvatar');
    if (avatarEl) {
        avatarEl.className = `dev-modal-avatar ${dev.avatarClass || 'da-1'}`;
        avatarEl.textContent = initials(dev.name);
    }

    const nameEl = document.getElementById('modalDevName');
    if (nameEl) nameEl.textContent = dev.name || 'Unknown Developer';

    const joinedEl = document.getElementById('modalDevJoined');
    if (joinedEl) joinedEl.textContent = dev.joined || 'Recent';

    const gamesEl = document.getElementById('modalDevGames');
    if (gamesEl) gamesEl.textContent = dev.games || 0;

    const ratingEl = document.getElementById('modalDevRating');
    if (ratingEl) ratingEl.textContent = `★ ${dev.rating || 'N/A'}`;

    const bioEl = document.getElementById('modalDevBio');
    if (bioEl) bioEl.textContent = dev.bio || 'This developer has not provided a bio yet.';

    const gamesListEl = document.getElementById('modalDevGamesList');
    if (gamesListEl) {
        const gamesMade = dev.gamesMade || [];
        if (gamesMade.length > 0) {
            gamesListEl.innerHTML = gamesMade.map(game => {
                const thumbStyle = game.thumbImage ? ` style="background-image:url('${game.thumbImage}')"` : '';
                const thumbClass = game.thumbImage ? 'dev-game-thumb has-image' : `dev-game-thumb ${game.thumbClass || 'da-1'}`;
                const thumbContent = game.thumbImage ? '' : initials(game.name);
                return `
                    <div class="dev-game-card">
                        <div class="${thumbClass}"${thumbStyle}>${thumbContent}</div>
                        <div class="dev-game-info">
                            <div class="dev-game-name">${game.name}</div>
                            <div class="dev-game-meta">
                                <span>${game.genre || 'Game'}</span>
                                <span>★ ${game.rating || 'N/A'}</span>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            gamesListEl.innerHTML = '<p style="color:var(--muted); font-size:13px;">No games published yet.</p>';
        }
    }

    openModal(devModal);
}

if (devModalClose) devModalClose.addEventListener('click', () => closeModal(devModal));
if (devModalOverlay) devModalOverlay.addEventListener('click', () => closeModal(devModal));

// Close modal on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal(devModal);
    }
});

// ═══════════════════════════════════════════
// CLICK HANDLING — opens developer modal
// ═══════════════════════════════════════════
document.addEventListener('click', function(e) {
    const viewProfileBtn = e.target.closest('.btn-view-profile');
    if (viewProfileBtn) {
        openDeveloperModal(viewProfileBtn.getAttribute('data-dev-id'));
        return;
    }

    const allDevCard = e.target.closest('.all-dev-card');
    if (allDevCard) {
        openDeveloperModal(allDevCard.getAttribute('data-dev-id'));
        return;
    }
});

// ═══════════════════════════════════════════
// NAVBAR SEARCH — filters the All Developers
// ═══════════════════════════════════════════
const searchInput = document.getElementById('searchInput');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        currentSearch = this.value;
        renderAllDevelopers();
    });
}

// ═══════════════════════════════════════════
// NAVBAR FILTER DROPDOWN 
// ═══════════════════════════════════════════
const filterBtn = document.getElementById('filterBtn');
const devFilterDropdown = document.getElementById('devFilterDropdown');

if (filterBtn && devFilterDropdown) {
    filterBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        devFilterDropdown.classList.toggle('open');
    });

    document.addEventListener('click', function(e) {
        if (!devFilterDropdown.contains(e.target)) {
            devFilterDropdown.classList.remove('open');
        }
    });

    devFilterDropdown.querySelectorAll('[data-sort]').forEach(btn => {
        btn.addEventListener('click', function() {
            devFilterDropdown.querySelectorAll('[data-sort]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentSort = this.getAttribute('data-sort');
            renderAllDevelopers();
            devFilterDropdown.classList.remove('open');
        });
    });

    devFilterDropdown.querySelectorAll('[data-scope]').forEach(btn => {
        btn.addEventListener('click', function() {
            devFilterDropdown.querySelectorAll('[data-scope]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentScope = this.getAttribute('data-scope');
            renderAllDevelopers();
            devFilterDropdown.classList.remove('open');
        });
    });
}

// ═══════════════════════════════════════════
// DEEP LINK — open a developer's profile modal 
// ═══════════════════════════════════════════
function openDevModalFromHash() {
    const hash = window.location.hash;
    if (!hash || !hash.startsWith('#dev-')) return;

    const devId = hash.slice('#dev-'.length);
    if (safeDevDatabase.some(d => d.id == devId)) {
        openDeveloperModal(devId);
    }
}

// Kick off rendering
renderTopDevelopers();
renderAllDevelopers();
openDevModalFromHash();

window.addEventListener('hashchange', openDevModalFromHash);

// ═══════════════════════════════════════════
// LOADING SCREEN
// ═══════════════════════════════════════════
(function() {
    const loadingScreen = document.getElementById('loading-screen');
    const loaderBarFill  = document.getElementById('loaderBarFill');
    const loaderText     = document.getElementById('loaderText');

    if (!loadingScreen) return;

    let progress = 0;
    const interval = setInterval(function() {
        progress += Math.random() * 7;
        if (progress > 90) progress = 90;
        loaderBarFill.style.width = progress + '%';
        loaderText.textContent = `Loading… ${Math.floor(progress)}%`;
    }, 400);

    window.addEventListener('load', function() {
        clearInterval(interval);
        loaderBarFill.style.width = '100%';
        loaderText.textContent = 'Loading…';

        setTimeout(function() {
            loadingScreen.classList.add('loaded');
            document.body.style.overflow = 'auto';
        }, 500);
    });

    document.body.style.overflow = 'hidden';
})();