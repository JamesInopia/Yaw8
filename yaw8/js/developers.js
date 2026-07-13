// ═══════════════════════════════════════════
// DEVELOPER DATABASE - hardcoded for now,
// mirrors the dev names already used across
// the site (index.html / script.js)
// ═══════════════════════════════════════════
const developerDatabase = [
    {
        id: 'james-inopia',
        name: 'James Inopia',
        role: 'Puzzle & Arcade Dev',
        games: 12,
        rating: '4.8',
        avatarClass: 'da-1',
        top: true,
        joined: 'Sept 2023',
        quote: "A good puzzle doesn't need a tutorial — the moment you understand it, that's the fun.",
        bio: "James builds fast, replayable arcade and puzzle games with a focus on tight controls and short session lengths. He started making games on lunch breaks freshman year and hasn't stopped since.",
        skills: ['Unity', 'C#', 'Level Design', 'Pixel Art'],
        gamesMade: [
            { name: 'Wobble Bao', genre: 'Arcade', rating: '4.8', thumbClass: 'da-3', thumbImage: '../Images/Wobble_Bao.png' },
            { name: 'Tower Tactics', genre: 'Strategy', rating: '4.7', thumbClass: 'da-4' },
            { name: 'Pixel Runner', genre: 'Arcade', rating: '4.7', thumbClass: 'da-1' },
            { name: 'Block Drop Deluxe', genre: 'Puzzle', rating: '4.6', thumbClass: 'da-2' },
            { name: 'Neon Dash', genre: 'Arcade', rating: '4.9', thumbClass: 'da-5' }
        ]
    },

    {
        id: 'christine-arenal',
        name: 'Christine Arenal',
        role: 'Puzzle Designer',
        games: 8,
        rating: '4.7',
        avatarClass: 'da-2',
        top: true,
        joined: 'Jan 2024',
        quote: "I want players to feel smart, not stuck. Every level has to teach its own solution.",
        bio: "Christine designs mechanic-driven puzzle games, often prototyping on paper before touching code. She's especially interested in color and pattern-based logic puzzles.",
        skills: ['Godot', 'Puzzle Design', 'UI/UX', 'Figma'],
        gamesMade: [
            { name: 'Wobble Bao', genre: 'Arcade', rating: '4.8', thumbClass: 'da-3', thumbImage: '../Images/Wobble_Bao.png' },
            { name: 'Color Clash', genre: 'Puzzle', rating: '4.4', thumbClass: 'da-2' },
        ]
    },

    {
        id: 'noah-lonoy',
        name: 'Noah Lonoy',
        role: 'Action Dev',
        games: 6,
        rating: '4.6',
        avatarClass: 'da-3',
        top: true,
        joined: 'Aug 2023',
        quote: "Fast games live or die on feedback — every hit needs to feel like it landed.",
        bio: "Noah focuses on fast-paced action games with an emphasis on responsive combat and screen feel. He's currently experimenting with a dash-based combo system for his next release.",
        skills: ['Unity', 'C#', 'Combat Design', 'Animation'],
        gamesMade: [
            { name: 'Tower Tactics', genre: 'Strategy', rating: '4.7', thumbClass: 'da-4' },
            { name: 'Shadow Strike', genre: 'Action', rating: '4.6', thumbClass: 'da-1' },
            { name: 'Wobble Bao', genre: 'Arcade', rating: '4.8', thumbClass: 'da-3', thumbImage: '../Images/Wobble_Bao.png' },
            { name: 'Blade Rush', genre: 'Action', rating: '4.5', thumbClass: 'da-4' }
        ]
    },

    {
        id: 'harvey-ablen',
        name: 'Harvey Ablen',
        role: 'Strategy Dev',
        games: 5,
        rating: '4.5',
        avatarClass: 'da-4',
        top: true,
        joined: 'Nov 2023',
        quote: "Strategy games should reward patience. I want players thinking three moves ahead, not one.",
        bio: "Harvey builds tactical and tower-defense style games with a heavy focus on balance and long-term systems. He spends more time in spreadsheets than in the game engine, by his own admission.",
        skills: ['Unity', 'Game Balancing', 'Systems Design', 'C#'],
        gamesMade: [
            { name: 'Tower Tactics', genre: 'Strategy', rating: '4.7', thumbClass: 'da-4' },
            { name: 'Wobble Bao', genre: 'Arcade', rating: '4.8', thumbClass: 'da-3', thumbImage: '../Images/Wobble_Bao.png' },
            { name: 'Kingdom Siege', genre: 'Strategy', rating: '4.4', thumbClass: 'da-1' }
        ]
    },

    {
        id: 'jirsy-chan',
        name: 'Jirsy Chan',
        role: 'Art & Animation',
        games: 4,
        rating: '4.6',
        avatarClass: 'da-5',
        top: false,
        joined: 'Feb 2024',
        quote: "A game's world should feel alive even in a single still frame.",
        bio: "Jirsy handles art direction, character design, and animation across several YA!W8 titles. She's the reason a lot of student games on this platform actually look and feel cohesive.",
        skills: ['Aseprite', 'Illustration', '2D Animation', 'Art Direction'],
        gamesMade: [
            { name: 'Color Clash', genre: 'Puzzle', rating: '4.4', thumbClass: 'da-2' },
            { name: 'Dreamscape', genre: 'Adventure', rating: '4.6', thumbClass: 'da-6' },
            { name: 'Petal Fall', genre: 'Puzzle', rating: '4.5', thumbClass: 'da-2' }
        ]
    },
];

// ═══════════════════════════════════════════
// GAME COLLABORATIONS - games made by more
// than one developer (NEEDS DATABASE)
// ═══════════════════════════════════════════
const collaborationDatabase = [
    {
        name: 'Wobble Bao',
        genre: 'Arcade',
        rating: '4.8',
        thumbClass: 'da-3',
        thumbImage: '../Images/Wobble_Bao.png', // same game image used on the Homepage (.sp-forest)
        releaseDate: 'March 2024',
        description: "A bouncy, physics-based arcade game where you guide a wobbling bao bun through obstacle-filled kitchens. Built as a five-person collaboration, it's become one of the most played games on the platform thanks to its simple controls and chaotic multiplayer mode.",
        team: ['Christine Arenal', 'James Inopia', 'Harvey Ablen', 'Noah Lonoy']
    },
    {
        name: 'Color Clash',
        genre: 'Puzzle',
        rating: '4.4',
        thumbClass: 'da-2',
        releaseDate: 'Oct 2024',
        description: "A fast-paced color-matching puzzle game where the grid keeps shifting under you. Two developers split the work — one on core mechanics, one on level curve and difficulty pacing.",
        team: ['Christine Arenal', 'Jirsy Chan']
    },
    {
        name: 'Tower Tactics',
        genre: 'Strategy',
        rating: '4.7',
        thumbClass: 'da-4',
        releaseDate: 'Jan 2025',
        description: "A tactical tower-defense game with a branching upgrade tree and permadeath units. Built by a three-person team over one semester as a course capstone project that later got polished for public release.",
        team: ['Harvey Ablen', 'James Inopia', 'Noah Lonoy']
    },
];

function initials(name) {
    return name.split(' ').map(p => p[0]).join('').slice(0, 2).toUpperCase();
}

const avatarClasses = ['da-1', 'da-2', 'da-3', 'da-4', 'da-5', 'da-6'];

// ═══════════════════════════════════════════
// ALL DEVELOPERS — filter/sort state
// Driven by the navbar search input + the
// filter dropdown (sort by / show).
// ═══════════════════════════════════════════
let currentSearch = '';
let currentSort = 'rating';   // 'rating' | 'games' | 'name'
let currentScope = 'all';     // 'all' | 'top'

function getFilteredSortedDevs() {
    let devs = [...developerDatabase];

    if (currentScope === 'top') {
        devs = devs.filter(d => d.top);
    }

    const query = currentSearch.trim().toLowerCase();
    if (query) {
        devs = devs.filter(d => d.name.toLowerCase().includes(query));
    }

    switch (currentSort) {
        case 'games':
            devs.sort((a, b) => b.games - a.games);
            break;
        case 'name':
            devs.sort((a, b) => a.name.localeCompare(b.name));
            break;
        case 'rating':
        default:
            devs.sort((a, b) => parseFloat(b.rating) - parseFloat(a.rating));
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

    const topDevs = developerDatabase.filter(d => d.top);

    topDevs.forEach((dev, i) => {
        const card = document.createElement('div');
        card.className = 'top-dev-card';
        card.innerHTML = `
            <div class="top-dev-rank">${i + 1}</div>
            <div class="top-dev-avatar ${dev.avatarClass}">${initials(dev.name)}</div>
            <div class="top-dev-name">${dev.name}</div>
            <div class="top-dev-role">${dev.role}</div>
            <div class="top-dev-metrics">
                <div class="top-dev-metric">
                    <span class="val">${dev.games}</span>
                    <span class="key">Games</span>
                </div>
                <div class="top-dev-metric rating">
                    <span class="val">★ ${dev.rating}</span>
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
// Reflects the current search term (from the
// navbar) + sort/scope (from the filter dropdown).
// ═══════════════════════════════════════════
function renderAllDevelopers() {
    const grid = document.getElementById('allDevGrid');
    if (!grid) return;
    grid.innerHTML = '';

    const devs = getFilteredSortedDevs();

    if (devs.length === 0) {
        grid.innerHTML = `<p style="color:var(--muted); font-size:13px; grid-column: 1 / -1;">No developers match "${currentSearch}".</p>`;
        return;
    }

    devs.forEach(dev => {
        const card = document.createElement('div');
        card.className = 'all-dev-card';
        card.id = `dev-${dev.id}`;
        card.setAttribute('data-dev-id', dev.id);
        card.innerHTML = `
            <div class="all-dev-avatar ${dev.avatarClass}">${initials(dev.name)}</div>
            <div class="all-dev-info">
                <div class="all-dev-name">${dev.name}</div>
                <div class="all-dev-games">${dev.games} Games · ${dev.role}</div>
            </div>
            <div class="all-dev-rating">★ ${dev.rating}</div>
        `;
        grid.appendChild(card);
    });
}

// ═══════════════════════════════════════════
// RENDER — GAME COLLABORATIONS
// ═══════════════════════════════════════════
function renderCollaborations() {
    const list = document.getElementById('collabList');
    if (!list) return;
    list.innerHTML = '';

    collaborationDatabase.forEach((game, index) => {
        const row = document.createElement('div');
        row.className = 'collab-row';
        row.setAttribute('data-game-index', index);

        const visibleTeam = game.team.slice(0, 4);
        const extra = game.team.length - visibleTeam.length;

        const avatarsHTML = visibleTeam
            .map((name, i) => `<div class="collab-avatar ${avatarClasses[i % avatarClasses.length]}" title="${name}">${initials(name)}</div>`)
            .join('') + (extra > 0 ? `<div class="collab-avatar more" title="${extra} more">+${extra}</div>` : '');

        // Use the real game image when available (e.g. Wobble Bao, same image as the Homepage),
        // otherwise fall back to the initials-on-gradient thumb.
        const thumbClass = game.thumbImage ? 'collab-thumb has-image' : `collab-thumb ${game.thumbClass}`;
        const thumbStyle = game.thumbImage ? ` style="background-image:url('${game.thumbImage}')"` : '';
        const thumbContent = game.thumbImage ? '' : initials(game.name);

        row.innerHTML = `
            <div class="${thumbClass}"${thumbStyle}>${thumbContent}</div>
            <div class="collab-info">
                <div class="collab-name">${game.name}</div>
                <div class="collab-genre">${game.genre} · ${game.team.length} contributors</div>
            </div>
            <div class="collab-avatars">${avatarsHTML}</div>
            <div class="collab-rating">★ ${game.rating}</div>
        `;
        list.appendChild(row);
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
}

// ═══════════════════════════════════════════
// DEVELOPER PROFILE MODAL
// ═══════════════════════════════════════════
const devModal = document.getElementById('devModal');
const devModalInner = document.getElementById('devModalInner');
const devModalClose = document.getElementById('devModalClose');
const devModalOverlay = document.getElementById('devModalOverlay');

function openDeveloperModal(devId) {
    const dev = developerDatabase.find(d => d.id === devId);
    if (!dev || !devModalInner) return;

    const skillsHTML = dev.skills
        .map(skill => `<span class="dev-skill-tag">${skill}</span>`)
        .join('');

    const gamesHTML = dev.gamesMade.map(game => {
        const thumbStyle = game.thumbImage ? ` style="background-image:url('${game.thumbImage}')"` : '';
        const thumbClass = game.thumbImage ? 'dev-game-thumb has-image' : `dev-game-thumb ${game.thumbClass}`;
        const thumbContent = game.thumbImage ? '' : initials(game.name);
        return `
            <div class="dev-game-card">
                <div class="${thumbClass}"${thumbStyle}>${thumbContent}</div>
                <div class="dev-game-info">
                    <div class="dev-game-name">${game.name}</div>
                    <div class="dev-game-meta">
                        <span>${game.genre}</span>
                        <span>★ ${game.rating}</span>
                    </div>
                </div>
            </div>
        `;
    }).join('');

    devModalInner.innerHTML = `
        <div class="dev-modal-avatar-row">
            <div class="dev-modal-avatar ${dev.avatarClass}">${initials(dev.name)}</div>
            <div class="dev-modal-name">
                <h1>${dev.name}</h1>
                <p>${dev.role} · Member since ${dev.joined}</p>
            </div>
        </div>

        <div class="modal-stats">
            <div class="stat">
                <span class="stat-label">Games</span>
                <span class="stat-value">${dev.games}</span>
            </div>
            <div class="stat">
                <span class="stat-label">Rating</span>
                <span class="stat-value">★ ${dev.rating}</span>
            </div>
            <div class="stat">
                <span class="stat-label">Status</span>
                <span class="stat-value">${dev.top ? 'Top Dev' : 'Active'}</span>
            </div>
        </div>

        <div class="modal-box dev-quote-box">
            <p>"${dev.quote}"</p>
        </div>

        <div class="modal-section">
            <h3>About</h3>
            <p>${dev.bio}</p>
        </div>

        <div class="modal-section">
            <h3>Skills</h3>
            <div class="dev-skills">${skillsHTML}</div>
        </div>

        <div class="modal-section">
            <h3>Games Made</h3>
            <div class="dev-games-grid">${gamesHTML}</div>
        </div>
    `;

    openModal(devModal);
}

if (devModalClose) devModalClose.addEventListener('click', () => closeModal(devModal));
if (devModalOverlay) devModalOverlay.addEventListener('click', () => closeModal(devModal));

// ═══════════════════════════════════════════
// GAME COLLABORATION MODAL
// ═══════════════════════════════════════════
const gameModal = document.getElementById('gameModal');
const gameModalInner = document.getElementById('gameModalInner');
const gameModalClose = document.getElementById('gameModalClose');
const gameModalOverlay = document.getElementById('gameModalOverlay');

// ─────────────────────────────────────────────
// DEVELOPER NAME LINKS (game modal "By …" line)
// Same pattern as the homepage (script.js): first
// 2 devs always visible, rest hidden behind a
// "+N more" toggle. Each name is a hoverable link
// that opens that developer's profile modal.
// ─────────────────────────────────────────────
function renderCollabDevelopers(team) {
    const VISIBLE = 2;
    const visibleDevs = team.slice(0, VISIBLE);
    const hiddenDevs = team.slice(VISIBLE);

    function devLinkHTML(name) {
        const dev = developerDatabase.find(d => d.name === name);
        const devId = dev ? dev.id : name.toLowerCase().replace(/\s+/g, '-');
        return `<a href="developers.html#dev-${devId}" class="dev-name-link" data-dev-id="${devId}">${name}</a>`;
    }

    let html = `<span class="dev-inline">By </span>`;

    visibleDevs.forEach((name, i) => {
        html += devLinkHTML(name);
        if (i < visibleDevs.length - 1) html += `<span class="dev-sep">, </span>`;
    });

    if (hiddenDevs.length > 0) {
        html += `<span class="dev-hidden-names" style="display:none">`;
        hiddenDevs.forEach(name => {
            html += `<span class="dev-sep">, </span>${devLinkHTML(name)}`;
        });
        html += `</span>`;
        html += `<span class="dev-sep"> </span>`;
        html += `<button type="button" class="dev-more-btn">+${hiddenDevs.length} more</button>`;
    }

    return html;
}

function openGameModal(index) {
    const game = collaborationDatabase[index];
    if (!game || !gameModalInner) return;

    const teamHTML = game.team.map((name, i) => {
        const dev = developerDatabase.find(d => d.name === name);
        const avatarClass = dev ? dev.avatarClass : avatarClasses[i % avatarClasses.length];
        const role = dev ? dev.role : 'Contributor';
        return `
            <div class="game-team-member" ${dev ? `data-dev-id="${dev.id}"` : ''}>
                <div class="member-avatar ${avatarClass}">${initials(name)}</div>
                <div>
                    <div class="member-name">${name}</div>
                    <div class="dev-game-meta" style="justify-content:flex-start; gap:6px;">${role}</div>
                </div>
            </div>
        `;
    }).join('');

    const thumbStyle = game.thumbImage
        ? `background-image:url('${game.thumbImage}'); background-size:cover; background-position:center;`
        : '';

    gameModalInner.innerHTML = `
        <div class="modal-header">
            <div class="modal-thumb ${game.thumbImage ? '' : game.thumbClass}" style="${thumbStyle} ${game.thumbImage ? '' : 'display:flex; align-items:center; justify-content:center; font-size:40px; font-weight:800; color:#fff;'}">
                ${game.thumbImage ? '' : initials(game.name)}
            </div>
            <div class="modal-header-info">
                <h1>${game.name}</h1>
                <p class="modal-developer">${renderCollabDevelopers(game.team)}</p>
                <div class="modal-stats">
                    <div class="stat">
                        <span class="stat-label">Genre</span>
                        <span class="stat-value">${game.genre}</span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">Rating</span>
                        <span class="stat-value">★ ${game.rating}</span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">Released</span>
                        <span class="stat-value">${game.releaseDate}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-section">
            <h3>About</h3>
            <p>${game.description}</p>
        </div>

        <div class="modal-section">
            <h3>Development Team</h3>
            <div class="game-team-list">${teamHTML}</div>
        </div>
    `;

    openModal(gameModal);
}

if (gameModalClose) gameModalClose.addEventListener('click', () => closeModal(gameModal));
if (gameModalOverlay) gameModalOverlay.addEventListener('click', () => closeModal(gameModal));

// Close either modal on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal(devModal);
        closeModal(gameModal);
    }
});

// ═══════════════════════════════════════════
// CLICK HANDLING — opens developer / game
// modals from anywhere they can be triggered
// ═══════════════════════════════════════════
document.addEventListener('click', function(e) {
    // A linked developer name (e.g. inside a game modal's "By …" line)
    const devNameLink = e.target.closest('.dev-name-link[data-dev-id]');
    if (devNameLink) {
        e.preventDefault();
        closeModal(gameModal);
        openDeveloperModal(devNameLink.getAttribute('data-dev-id'));
        return;
    }

    // "+N more" / "less" toggle on a game modal's "By …" line
    const moreBtn = e.target.closest('.dev-more-btn');
    if (moreBtn) {
        const wrap = moreBtn.closest('.modal-developer');
        const hiddenSpan = wrap ? wrap.querySelector('.dev-hidden-names') : null;
        if (hiddenSpan) {
            const expanded = hiddenSpan.style.display !== 'none';
            const hiddenCount = hiddenSpan.querySelectorAll('.dev-name-link').length;
            hiddenSpan.style.display = expanded ? 'none' : 'inline';
            moreBtn.textContent = expanded ? `+${hiddenCount} more` : 'less';
        }
        return;
    }

    // "View Profile" button on a Top Developer card
    const viewProfileBtn = e.target.closest('.btn-view-profile');
    if (viewProfileBtn) {
        openDeveloperModal(viewProfileBtn.getAttribute('data-dev-id'));
        return;
    }

    // A card in the All Developers grid
    const allDevCard = e.target.closest('.all-dev-card');
    if (allDevCard) {
        openDeveloperModal(allDevCard.getAttribute('data-dev-id'));
        return;
    }

    // A developer's avatar inside an open game modal's team list
    const teamMember = e.target.closest('.game-team-member[data-dev-id]');
    if (teamMember) {
        closeModal(gameModal);
        openDeveloperModal(teamMember.getAttribute('data-dev-id'));
        return;
    }

    // A row in Game Collaborations
    const collabRow = e.target.closest('.collab-row');
    if (collabRow) {
        openGameModal(parseInt(collabRow.getAttribute('data-game-index'), 10));
        return;
    }
});

// ═══════════════════════════════════════════
// NAVBAR SEARCH — filters the All Developers
// grid as the person types (this page only,
// since developers.js isn't loaded elsewhere) (NOT FUNCTIONAL)
// ═══════════════════════════════════════════
const searchInput = document.getElementById('searchInput');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        currentSearch = this.value;
        renderAllDevelopers();
    });
}

// ═══════════════════════════════════════════
// NAVBAR FILTER DROPDOWN — sort by / show (NOT FUNCTIONAL)
// ═══════════════════════════════════════════
const filterBtn = document.getElementById('filterBtn');
const devFilterDropdown = document.getElementById('devFilterDropdown');

if (filterBtn && devFilterDropdown) {
    filterBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        devFilterDropdown.classList.toggle('open');
    });

    // Close when clicking anywhere outside the dropdown
    document.addEventListener('click', function(e) {
        if (!devFilterDropdown.contains(e.target)) {
            devFilterDropdown.classList.remove('open');
        }
    });

    // Sort options
    devFilterDropdown.querySelectorAll('[data-sort]').forEach(btn => {
        btn.addEventListener('click', function() {
            devFilterDropdown.querySelectorAll('[data-sort]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentSort = this.getAttribute('data-sort');
            renderAllDevelopers();
            devFilterDropdown.classList.remove('open');
        });
    });

    // Scope options (All vs Top only)
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
// directly when the page loads (or the hash
// changes) with a #dev-<id> link, e.g. clicking
// a developer's name from the homepage.
// ═══════════════════════════════════════════
function openDevModalFromHash() {
    const hash = window.location.hash;
    if (!hash || !hash.startsWith('#dev-')) return;

    const devId = hash.slice('#dev-'.length);
    if (developerDatabase.some(d => d.id === devId)) {
        openDeveloperModal(devId);
    }
}

// Kick off rendering
renderTopDevelopers();
renderAllDevelopers();
renderCollaborations();
openDevModalFromHash();

window.addEventListener('hashchange', openDevModalFromHash);


// ═══════════════════════════════════════════
// NAVBAR — User dropdown toggle
// (same behavior as script.js on the homepage)
// ═══════════════════════════════════════════
const avatarBtn    = document.getElementById('avatarBtn');
const userDropdown = document.getElementById('userDropdown');

if (avatarBtn && userDropdown) {
    const avatarWrap = avatarBtn.closest('.avatar-wrap');

    avatarBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        const isOpen = userDropdown.classList.contains('open');
        userDropdown.classList.toggle('open', !isOpen);
        avatarBtn.classList.toggle('open', !isOpen);
    });

    document.addEventListener('click', function(e) {
        if (!avatarWrap.contains(e.target)) {
            userDropdown.classList.remove('open');
            avatarBtn.classList.remove('open');
        }
    });

    userDropdown.addEventListener('click', function(e) {
        e.stopPropagation();
        setTimeout(() => {
            userDropdown.classList.remove('open');
            avatarBtn.classList.remove('open');
        }, 120);
    });
}

// ═══════════════════════════════════════════
// LOADING SCREEN
// (same behavior as script.js on the homepage)
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