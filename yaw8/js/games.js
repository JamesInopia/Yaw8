// ═══════════════════════════════════════════════════
    // GAMES.JS
// ═══════════════════════════════════════════════════

    // ─────────────────────────────────────────────
    // Extend the shared game database with a few more
    // titles so the All Games grid has real variety. (CHANGE THIS LATER)
    // ─────────────────────────────────────────────
    Object.assign(gameDatabase, {
    'neon-vault': {
        id: 'neon-vault',
        name: 'Neon Vault',
        developer: 'by Harvey Ablen',
        rating: '4.3',
        plays: 9,
        genre: 'Puzzle',
        description: 'Neon Vault is a stealth-puzzle game where you crack security grids and slip past laser trip-wires to reach the vault. Chain your moves precisely, one misstep triggers the alarm and resets the floor. Eleven vaults, each trickier than the last.',
        gamelastupdated: 'May 2, 2026',
        gamereleased: 'April 18, 2026',
        thumbnail: 'gt-neon-vault'
    },

    'star-forge': {
        id: 'star-forge',
        name: 'Star Forge',
        developer: 'by James Inopia, Christine Arenal',
        rating: '4.5',
        plays: 11,
        genre: 'Strategy',
        description: 'Star Forge drops you on a dead moon with nothing but scrap and a dream. Mine resources, build your forge, and launch increasingly ambitious ships. Balance production chains against a ticking oxygen clock in this slow-burn strategy sim.',
        gamelastupdated: 'June 20, 2026',
        gamereleased: 'May 30, 2026',
        thumbnail: 'gt-star-forge'
    },

    'blob-quest': {
        id: 'blob-quest',
        name: 'Blob Quest',
        developer: 'by Jirsy Chan',
        rating: '4.2',
        plays: 7,
        genre: 'Adventure',
        description: 'Blob Quest follows a squishy little slime named Glob as he bounces, stretches, and squeezes through a colorful kingdom to rescue his jarred friends. Simple controls, big heart, and a soundtrack that gets stuck in your head.',
        thumbnail: 'gt-blob-quest'
    },

    'sky-punch': {
        id: 'sky-punch',
        name: 'Sky Punch',
        developer: 'by Noah Lonoy, Harvey Ablen',
        rating: '4.4',
        plays: 13,
        genre: 'Action',
        description: 'Sky Punch is a one-button brawler set on collapsing sky platforms. Time your punches to knock rivals off the edge before the storm catches up to you. Fast rounds, faster rematches.',
        gamelastupdated: 'June 5, 2026',
        gamereleased: 'May 12, 2026',
        thumbnail: 'gt-sky-punch'
    }
    });

    // ═══════════════════════════════════════════
    // ALL GAMES GRID + GENRE FILTER
    // ═══════════════════════════════════════════
    (function initAllGamesGrid() {
    const grid = document.getElementById('allGamesGrid');
    const chipsWrap = document.getElementById('genreFilters');
    if (!grid) return;

    const allGames = Object.keys(gameDatabase).map(id => gameDatabase[id]);
    const genres = ['All', ...new Set(allGames.map(g => g.genre))];
    let activeGenre = 'All';

    const picturedThumbnails = ['sp-forest'];

    function renderGrid() {
        grid.innerHTML = '';
        allGames
        .filter(g => activeGenre === 'All' || g.genre === activeGenre)
        .forEach(game => {
            const card = document.createElement('div');
            card.className = 'game-card';
            card.setAttribute('data-game-id', game.id);
            const showTitle = !picturedThumbnails.includes(game.thumbnail);
            card.innerHTML = `
            <div class="game-thumb ${game.thumbnail}">
                ${showTitle ? `<div class="game-thumb-title">${game.name.toUpperCase().split(' ').join('<br>')}</div>` : ''}
            </div>
            <div class="game-info">
                <div class="game-meta">
                <span class="game-name">${game.name}</span>
                <span class="game-more">···</span>
                </div>
                <div class="game-genre">${game.genre}</div>
                <div class="game-footer">
                <span class="stars">★ ${game.rating}</span>
                <button class="btn-play">PLAY</button>
                </div>
            </div>
            `;
            card.addEventListener('click', function(e) {
            if (e.target.closest('.btn-play')) return;
            openGameModal(game.id);
            });
            grid.appendChild(card);
        });
    }

    if (chipsWrap) {
        genres.forEach(genre => {
        const chip = document.createElement('button');
        chip.className = 'filter-chip' + (genre === activeGenre ? ' active' : '');
        chip.textContent = genre;
        chip.addEventListener('click', function() {
            activeGenre = genre;
            chipsWrap.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            renderGrid();
        });
        chipsWrap.appendChild(chip);
        });
    }

    renderGrid();
    })();