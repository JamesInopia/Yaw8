// ═══════════════════════════════════════════
// GAME PAGE TEMPLATE
// Change this to 'gameDatabase" for the real data source
// ═══════════════════════════════════════════

    // -- Shared nav + loading screen --
    (function () {
    // Loading screen
    const loadingScreen = document.getElementById('loading-screen');
    const loaderBarFill = document.getElementById('loaderBarFill');
    const loaderText = document.getElementById('loaderText');

    if (loadingScreen) {
        let progress = 0;
        const interval = setInterval(function () {
        progress += Math.random() * 7;
        if (progress > 90) progress = 90;
        loaderBarFill.style.width = progress + '%';
        loaderText.textContent = `Loading… ${Math.floor(progress)}%`;
        }, 400);

        window.addEventListener('load', function () {
        clearInterval(interval);
        loaderBarFill.style.width = '100%';
        loaderText.textContent = 'Loading…';
        setTimeout(function () {
            loadingScreen.classList.add('loaded');
            document.body.style.overflow = 'auto';
        }, 500);
        });

        document.body.style.overflow = 'hidden';
    }

    // Nav link active state
    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href === '#') e.preventDefault();
        document.querySelectorAll('.nav-links a').forEach(l => l.classList.remove('active'));
        this.classList.add('active');
        });
    });
    
    })();

    // -- Game page logic --
    (function () {

    // -- Sample data — same shape as script.js's gameDatabase --
    const gameDatabase = {
        'pixel-drift': {
        id: 'pixel-drift',
        name: 'Pixel Drift',
        developer: 'James Inopia',
        rating: '4.6',
        plays: '16.2k',
        likes: 812,
        genre: 'Racing',
        released: 'Jan 12, 2026',
        updated: 'Mar 2, 2026',
        description: 'Pixel Drift is a fast-paced racing game where you drift through neon-lit tracks. Master the art of drifting to achieve high scores and unlock challenging levels.',
        thumbnail: 'gt-pixel-drift',
        iframeSrc: null 
        },

        'tower-tactics': {
        id: 'tower-tactics',
        name: 'Tower Tactics',
        developer: 'Harvey Ablen',
        rating: '4.7',
        plays: '14.8k',
        likes: 640,
        genre: 'Strategy',
        released: 'Nov 3, 2025',
        updated: 'Feb 20, 2026',
        description: 'Tower Tactics is a strategic tower defense game where you build and upgrade towers to defend your base against waves of enemies.',
        thumbnail: 'gt-tower-tactics',
        iframeSrc: null
        },

        'box-jumper': {
        id: 'box-jumper',
        name: 'Box Jumper',
        developer: 'Noah Lonoy',
        rating: '4.5',
        plays: '12.1k',
        likes: 505,
        genre: 'Arcade',
        released: 'Oct 18, 2025',
        updated: 'Jan 30, 2026',
        description: 'Box Jumper is an addictive arcade game where you jump over obstacles and collect points. Time your jumps perfectly to avoid falling into the void.',
        thumbnail: 'gt-box-jumper',
        iframeSrc: null
        },

        'color-clash': {
        id: 'color-clash',
        name: 'Color Clash',
        developer: 'Christine Arenal',
        rating: '4.4',
        plays: '25.3k',
        likes: 560,
        genre: 'Puzzle',
        released: 'Feb 28, 2026',
        updated: 'Mar 15, 2026',
        description: 'Color Clash is a vibrant puzzle game where you match colors to clear the board. Create combos by matching three or more tiles and unlock special power-ups.',
        thumbnail: 'gt-color-clash',
        iframeSrc: null
        },

        'wobble-bao': {
        id: 'wobble-bao',
        name: 'Wobble Bao',
        developer: 'Christine Arenal, James Inopia, Harvey Ablen, Noah Lonoy, Jirsy Chan',
        rating: '4.8',
        plays: '28.6k',
        likes: 940,
        genre: 'Arcade',
        released: 'Jan 28, 2026',
        updated: 'Feb 10, 2026',
        description: 'Wobble Bao is a goofy, Flappy Bird–style game where you control Bao Bao, a wobbly Xiao Long Bao trying to survive a deadly kitchen. Dodge forks, knives, and fiery pots—or get cooked, sliced, and served!',
        thumbnail: 'sp-forest',
        iframeSrc: null
        },

        'cyber-cell': {
        id: 'cyber-cell',
        name: 'Cyber Cell',
        developer: 'Noah Lonoy',
        rating: '4.7',
        plays: '18.4k',
        likes: 705,
        genre: 'Action',
        released: null,
        updated: null,
        description: 'Cyber Cell is a futuristic action game where you navigate through digital environments fighting cyber threats. Use advanced weapons and abilities to defeat enemies and protect the virtual world from corruption.',
        thumbnail: 'sp-cyber',
        iframeSrc: null
        },

        'space-cleanup': {
        id: 'space-cleanup',
        name: 'Space Cleanup',
        developer: 'James Inopia',
        rating: '4.6',
        plays: '15.7k',
        likes: 615,
        genre: 'Puzzle',
        released: null,
        updated: null,
        description: 'Space Cleanup is an engaging puzzle game set in outer space where you collect and organize debris. Plan your moves strategically to complete missions and unlock new levels.',
        thumbnail: 'sp-space',
        iframeSrc: null
        },

        'neon-vault': {
        id: 'neon-vault',
        name: 'Neon Vault',
        developer: 'Harvey Ablen',
        rating: '4.3',
        plays: '9.4k',
        likes: 388,
        genre: 'Puzzle',
        released: 'Apr 18, 2026',
        updated: 'May 2, 2026',
        description: 'Neon Vault is a stealth-puzzle game where you crack security grids and slip past laser trip-wires to reach the vault. Chain your moves precisely, one misstep triggers the alarm and resets the floor.',
        thumbnail: 'gt-neon-vault',
        iframeSrc: null
        },

        'star-forge': {
        id: 'star-forge',
        name: 'Star Forge',
        developer: 'James Inopia, Christine Arenal',
        rating: '4.5',
        plays: '11.2k',
        likes: 470,
        genre: 'Strategy',
        released: 'May 30, 2026',
        updated: 'Jun 20, 2026',
        description: 'Star Forge drops you on a dead moon with nothing but scrap and a dream. Mine resources, build your forge, and launch increasingly ambitious ships against a ticking oxygen clock.',
        thumbnail: 'gt-star-forge',
        iframeSrc: null
        },

        'blob-quest': {
        id: 'blob-quest',
        name: 'Blob Quest',
        developer: 'Jirsy Chan',
        rating: '4.2',
        plays: '7.1k',
        likes: 302,
        genre: 'Adventure',
        released: null,
        updated: null,
        description: 'Blob Quest follows a squishy little slime named Glob as he bounces, stretches, and squeezes through a colorful kingdom to rescue his jarred friends.',
        thumbnail: 'gt-blob-quest',
        iframeSrc: null
        },

        'sky-punch': {
        id: 'sky-punch',
        name: 'Sky Punch',
        developer: 'Noah Lonoy, Harvey Ablen',
        rating: '4.4',
        plays: '13.9k',
        likes: 540,
        genre: 'Action',
        released: 'May 12, 2026',
        updated: 'Jun 5, 2026',
        description: 'Sky Punch is a one-button brawler set on collapsing sky platforms. Time your punches to knock rivals off the edge before the storm catches up to you.',
        thumbnail: 'gt-sky-punch',
        iframeSrc: null
        }
    };

    const recommendedIds = Object.keys(gameDatabase);

    const params = new URLSearchParams(window.location.search);
    const requestedId = params.get('game');
    const currentId = (requestedId && gameDatabase[requestedId]) ? requestedId : Object.keys(gameDatabase)[0];
    const game = gameDatabase[currentId];

    // -- Populate title / meta / description --
    document.getElementById('gpTitle').textContent = game.name;
    document.getElementById('gpGenre').textContent = game.genre;
    const gpDevEl = document.getElementById('gpDev');
    const gpDevNames = game.developer.split(',').map(n => n.trim());
    gpDevEl.innerHTML = gpDevNames.map((name, i) => {
        const slug = name.toLowerCase().replace(/\s+/g, '-');
        const sep = i < gpDevNames.length - 1 ? '<span class="dev-sep">, </span>' : '';
        return `<a class="dev-name-link" href="developers.html#dev-${slug}">${name}</a>${sep}`;
    }).join('');
    document.getElementById('gpRating').textContent = `★ ${game.rating}`;
    document.getElementById('gpPlays').textContent = `${game.plays} plays`;
    document.getElementById('gpDescription').textContent = game.description;
    document.getElementById('gpReleased').textContent = game.released || '—';
    document.getElementById('gpUpdated').textContent = game.updated || '—';
    document.getElementById('gpLikeCount').textContent = game.likes;
    document.getElementById('gpStartThumb').className = `gp-start-thumb ${game.thumbnail}`;
    document.title = `${game.name} — Play Now — YA!W8`;

    const placeholderSrcDoc = `
    <!DOCTYPE html><html><head><meta charset="UTF-8"><style>
    html, body{
        margin:0;
        height:100%;
        background:#000;
        display:flex;
        align-items:center;
        justify-content:center;
        font-family:Poppins, sans-serif;
    }

    p{
        color:#5a6472;
        font-size:14px;
        text-align:center;
        padding:0 24px;
    }

    </style></head><body>
    <p>This game hasn't been uploaded yet.</p>
    </body></html>`;

    // -- Player element wiring --
    const screen = document.getElementById('gpScreen');
    const startCard = document.getElementById('gpStartCard');
    const playBtn = document.getElementById('gpPlayBtn');
    const frame = document.getElementById('gpFrame');
    const restartBtn = document.getElementById('gpRestartBtn');
    const muteBtn = document.getElementById('gpMuteBtn');
    const fullscreenBtn = document.getElementById('gpFullscreenBtn');

    function loadGame() {
        if (game.iframeSrc) {
        frame.src = game.iframeSrc;
        } else {
        frame.srcdoc = placeholderSrcDoc;
        }
    }

    function startGame() {
        loadGame();
        startCard.classList.add('hidden');
        screen.classList.add('playing');
    }

    playBtn.addEventListener('click', startGame);

    restartBtn.addEventListener('click', function () {
        startCard.classList.add('hidden');
        loadGame();
    });

    let muted = false;
    muteBtn.addEventListener('click', function () {
        muted = !muted;
        muteBtn.classList.toggle('active', muted);
        frame.contentWindow && frame.contentWindow.postMessage({ type: 'setMuted', muted }, '*');
    });

    fullscreenBtn.addEventListener('click', function () {
        if (screen.requestFullscreen) screen.requestFullscreen();
        else if (screen.webkitRequestFullscreen) screen.webkitRequestFullscreen();
    });

    window.addEventListener('message', function (e) {
        if (e.data && e.data.type === 'gameOver') {
        startCard.classList.remove('hidden');
        screen.classList.remove('playing');
        startCard.querySelector('.gp-start-badge').textContent = `GAME OVER — SCORE ${e.data.score}`;
        playBtn.textContent = '';
        const svg = '<svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>';
        playBtn.innerHTML = svg + ' Play Again';
        }
    });

    // -- Tabs --
    document.querySelectorAll('.gp-tab').forEach(tab => {
        tab.addEventListener('click', function () {
        document.querySelectorAll('.gp-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.gp-tab-content').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('gpTab' + this.dataset.tab.charAt(0).toUpperCase() + this.dataset.tab.slice(1)).classList.add('active');
        });
    });

    // -- Like / Favorite / Nominate buttons (local, front-end only demo state) --
    const likeBtn = document.getElementById('gpLikeBtn');
    const likeCountEl = document.getElementById('gpLikeCount');
    let liked = false;
    likeBtn.addEventListener('click', function () {
        liked = !liked;
        likeBtn.classList.toggle('active', liked);
        likeCountEl.textContent = game.likes + (liked ? 1 : 0);
    });

    document.getElementById('gpFavBtn').addEventListener('click', function () {
        this.classList.toggle('active');
    });

    document.getElementById('gpShareBtn').addEventListener('click', function () {
        if (navigator.share) {
        navigator.share({ title: game.name, url: window.location.href });
        } else if (navigator.clipboard) {
        navigator.clipboard.writeText(window.location.href);
        const original = this.innerHTML;
        this.textContent = 'Link copied!';
        setTimeout(() => { this.innerHTML = original; }, 1500);
        }
    });

    document.getElementById('gpNominateBtn').addEventListener('click', function () {
        showToast(`${game.name} nominated for the Community Spotlight!`);
    });

    // -- Lightweight toast --
    function showToast(text) {
        let toast = document.getElementById('gpToast');
        if (!toast) {
        toast = document.createElement('div');
        toast.id = 'gpToast';
        toast.className = 'gp-toast';
        document.body.appendChild(toast);
        }
        toast.textContent = text;
        toast.classList.add('show');
        clearTimeout(showToast._t);
        showToast._t = setTimeout(() => toast.classList.remove('show'), 2200);
    }

    // -- Comments --
    const comments = [
        { name: 'Jirsy Chan', time: '2 days ago', text: 'This is so addicting, beat my friend\u2019s high score already!' },
        { name: 'Christine Arenal', time: '5 days ago', text: 'Love the neon art style, feels great on desktop.' }
    ];

    const commentList = document.getElementById('gpCommentList');
    const commentCount = document.getElementById('gpCommentCount');
    const commentInput = document.getElementById('gpCommentInput');
    const commentSubmit = document.getElementById('gpCommentSubmit');

    function initials(name) {
        return name.split(' ').map(p => p[0]).join('').slice(0, 2).toUpperCase();
    }

    function renderComments() {
        commentList.innerHTML = '';
        commentCount.textContent = comments.length;
        if (comments.length === 0) {
        commentList.innerHTML = '<p class="gp-empty">No comments yet — be the first to share your score.</p>';
        return;
        }
        comments.forEach(c => {
        const row = document.createElement('div');
        row.className = 'gp-comment';
        row.innerHTML = `
            <div class="gp-comment-avatar">${initials(c.name)}</div>
            <div class="gp-comment-body">
            <div class="gp-comment-head">
                <span class="gp-comment-name">${c.name}</span>
                <span class="gp-comment-time">${c.time}</span>
            </div>
            <p class="gp-comment-text"></p>
            </div>`;
        row.querySelector('.gp-comment-text').textContent = c.text;
        commentList.appendChild(row);
        });
    }
    renderComments();

    commentSubmit.addEventListener('click', function () {
        const text = commentInput.value.trim();
        if (!text) return;
        comments.unshift({ name: 'You', time: 'just now', text });
        commentInput.value = '';
        renderComments();
    });

    // -- Sidebar: Up Next + More by developer --
    function thumbFor(g) {
        return `<div class="gp-queue-thumb ${g.thumbnail}"></div>`;
    }

    const queueEl = document.getElementById('gpQueue');
    recommendedIds
        .filter(id => id !== currentId)
        .slice(0, 3)
        .map(id => gameDatabase[id])
        .forEach(g => {
        const row = document.createElement('div');
        row.className = 'gp-queue-row';
        row.innerHTML = `
            ${thumbFor(g)}
            <div class="gp-queue-info">
            <div class="gp-queue-name">${g.name}</div>
            <div class="gp-queue-meta">${g.genre} · ★ ${g.rating}</div>
            </div>`;
        row.addEventListener('click', () => {
            window.location.href = `gamepage.html?game=${g.id}`;
        });
        queueEl.appendChild(row);
        });

    const devGamesEl = document.getElementById('gpDevGames');
    const sameDevGames = Object.values(gameDatabase).filter(g => g.developer === game.developer && g.id !== currentId);
    if (sameDevGames.length === 0) {
        devGamesEl.innerHTML = '<p class="gp-empty">No other games from this developer yet.</p>';
    } else {
        sameDevGames.forEach(g => {
        const row = document.createElement('div');
        row.className = 'gp-queue-row';
        row.innerHTML = `
            ${thumbFor(g)}
            <div class="gp-queue-info">
            <div class="gp-queue-name">${g.name}</div>
            <div class="gp-queue-meta">${g.genre} · ★ ${g.rating}</div>
            </div>`;
        row.addEventListener('click', () => {
            window.location.href = `gamepage.html?game=${g.id}`;
        });
        devGamesEl.appendChild(row);
        });
    }

})();