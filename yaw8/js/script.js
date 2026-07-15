// ═══════════════════════════════════════════
    // GAME DATABASE - Optional sincce this is just hardcoded (need to change later)
    // ═══════════════════════════════════════════
    const gameDatabase = {
    'pixel-drift': {
        id: 'pixel-drift',
        name: 'Pixel Drift',
        developer: 'by James Inopia',
        rating: '4.6',
        plays: 16,
        genre: 'Racing',
        description: 'Pixel Drift is a fast-paced racing game where you drift through neon-lit tracks. Master the art of drifting to achieve high scores and unlock challenging levels. Feel the adrenaline rush as you compete against the clock in this retro-inspired racing experience.',
        thumbnail: 'gt-pixel-drift'
    },

    'tower-tactics': {
        id: 'tower-tactics',
        name: 'Tower Tactics',
        developer: 'by Harvey Ablen',
        rating: '4.7',
        plays: 14,
        genre: 'Strategy',
        description: 'Tower Tactics is a strategic tower defense game where you build and upgrade towers to defend your base. Plan your strategy carefully, manage resources, and defeat waves of enemies. Perfect your defense combinations to become the ultimate tower master.',
        thumbnail: 'gt-tower-tactics'
    },
    
    'box-jumper': {
        id: 'box-jumper',
        name: 'Box Jumper',
        developer: 'by Noah Lonoy',
        rating: '4.5',
        plays: 12,
        genre: 'Arcade',
        description: 'Box Jumper is an addictive arcade game where you jump over obstacles and collect points. Time your jumps perfectly to avoid falling into the void. Experience fast-paced action with gradually increasing difficulty that will test your reflexes.',
        thumbnail: 'gt-box-jumper'
    },

    'color-clash': {
        id: 'color-clash',
        name: 'Color Clash',
        developer: 'by Christine Arenal',
        rating: '4.4',
        plays: 25,
        genre: 'Puzzle',
        description: 'Color Clash is a vibrant puzzle game where you match colors to clear the board. Create combos by matching three or more tiles and unlock special power-ups. Challenge yourself with hundreds of levels filled with colorful fun.',
        gamelastupdated: 'March 15, 2026',
        gamereleased: 'February 28, 2026',
        thumbnail: 'gt-color-clash'
    },

    'wobble-bao': {
        id: 'wobble-bao',
        name: 'Wobble Bao',
        developer: 'by Christine Arenal, James Inopia, Harvey Ablen, Noah Lonoy, Jirsy Chan',
        rating: '4.8',
        plays: 28,
        genre: 'Arcade',
        description: 'Wobble Bao is a goofy, Flappy Bird–style game where you control Bao Bao, a wobbly Xiao Long Bao trying to survive a deadly kitchen. Dodge forks, knives, and fiery pots—or get cooked, sliced, and served!',
        gamelastupdated: 'February 10, 2026',
        gamereleased: 'January 28, 2026',
        thumbnail: 'sp-forest'
    },
    
    'cyber-cell': {
        id: 'cyber-cell',
        name: 'Cyber Cell',
        developer: 'by Noah Lonoy',
        rating: '4.7',
        plays: 18,
        genre: 'Action',
        description: 'Cyber Cell is a futuristic action game where you navigate through digital environments fighting cyber threats. Use advanced weapons and abilities to defeat enemies and protect the virtual world from corruption. Immerse yourself in neon-lit cyberpunk action.',
        thumbnail: 'sp-cyber'
    },

    'space-cleanup': {
        id: 'space-cleanup',
        name: 'Space Cleanup',
        developer: 'by James Inopia',
        rating: '4.6',
        plays: 15,
        genre: 'Puzzle',
        description: 'Space Cleanup is an engaging puzzle game set in outer space where you collect and organize debris. Plan your moves strategically to complete missions and unlock new levels. Help clean up the cosmos one piece at a time.',
        thumbnail: 'sp-space'
    }
    };

    // Recommended games (from community spotlight)
    const recommendedGameIds = ['wobble-bao', 'cyber-cell', 'space-cleanup'];

    // ═══════════════════════════════════════════
    // MODAL FUNCTIONALITY
    // ═══════════════════════════════════════════
    const modal = document.getElementById('game-modal');
    const modalClose = document.querySelector('.modal-close');
    const carouselPrev = document.querySelector('.carousel-prev');
    const carouselNext = document.querySelector('.carousel-next');
    const CAROUSEL_VISIBLE_ITEMS = 4; // number of cards shown at a time
    let currentGameId = null;
    let carouselIndex = 0; // current starting card index

    // Open modal only when clicking the card (not the play button)
    document.querySelectorAll('.game-card, .spotlight-card').forEach(card => {
    card.addEventListener('click', function(e) {
        if (e.target.closest('.btn-play') || e.target.closest('.dev-name-link')) {
        return;
        }
        const gameId = this.getAttribute('data-game-id');
        openGameModal(gameId);
    });
    });

    // ═══════════════════════════════════════════
    // PLAY BUTTON — send the user to the game page
    // ═══════════════════════════════════════════
    function goToGamePage(gameId) {
    const alreadyInHtmlFolder = window.location.pathname.includes('/html/');
    const basePath = alreadyInHtmlFolder ? '' : 'html/';
    window.location.href = `${basePath}gamepage.html?game=${gameId}`;
    }

    document.addEventListener('click', function(e) {
    const playBtn = e.target.closest('.btn-play');
    if (!playBtn) return;

    const card = playBtn.closest('[data-game-id]');
    if (!card) return;

    goToGamePage(card.getAttribute('data-game-id'));
    });

    const modalPlayBtn = document.querySelector('.btn-play-modal');
    if (modalPlayBtn) {
    modalPlayBtn.addEventListener('click', function() {
        if (!currentGameId) return;
        goToGamePage(currentGameId);
    });
    }

    // ─────────────────────────────────────────────
    // DEVELOPER DISPLAY
    // Always shows first 2 devs. The 3rd dev and beyond
    // are hidden behind "+N more" and revealed on click.
    // Each dev name is a hoverable link to their profile.
    // ─────────────────────────────────────────────
    function renderDevelopers(developerString) {
    const wrap = document.getElementById('modalDeveloperWrap');
    wrap.innerHTML = '';

    // Strip leading "by " and split on comma
    const cleaned = developerString.replace(/^by\s+/i, '');
    const devs = cleaned.split(',').map(d => d.trim()).filter(Boolean);

    const VISIBLE = 2; // always-visible dev count
    const visibleDevs = devs.slice(0, VISIBLE);
    const hiddenDevs  = devs.slice(VISIBLE); // 3rd dev onward is hidden

    // Helper: build a hoverable dev link
    function makeDevLink(name) {
        const slug = name.toLowerCase().replace(/\s+/g, '-');
        const a = document.createElement('a');
        // Points to the developer's profile card/modal on the Developers page
        a.href = `../html/developers.html#dev-${slug}`;
        a.className = 'dev-name-link';
        a.textContent = name;
        return a;
    }

    // "by " prefix
    const byText = document.createElement('span');
    byText.className = 'dev-inline';
    byText.textContent = 'by ';
    wrap.appendChild(byText);

    // Visible dev links (first 2), comma-separated
    visibleDevs.forEach((name, i) => {
        wrap.appendChild(makeDevLink(name));
        if (i < visibleDevs.length - 1) {
        const sep = document.createElement('span');
        sep.className = 'dev-sep';
        sep.textContent = ', ';
        wrap.appendChild(sep);
        }
    });

    if (hiddenDevs.length === 0) return;

    // Hidden span: 3rd dev onward — starts hidden
    const hiddenSpan = document.createElement('span');
    hiddenSpan.className = 'dev-hidden-names';
    hiddenSpan.style.display = 'none';

    hiddenDevs.forEach((name, i) => {
        const sep = document.createElement('span');
        sep.className = 'dev-sep';
        sep.textContent = ', ';
        hiddenSpan.appendChild(sep);
        hiddenSpan.appendChild(makeDevLink(name));
    });

    wrap.appendChild(hiddenSpan);

    // Space before toggle so it never butts up against the last dev name
    const toggleSpace = document.createElement('span');
    toggleSpace.className = 'dev-sep';
    toggleSpace.textContent = ' ';
    wrap.appendChild(toggleSpace);

    // "+N more" / "less" toggle
    const toggle = document.createElement('button');
    toggle.className = 'dev-more-btn';
    toggle.textContent = `+${hiddenDevs.length} more`;
    wrap.appendChild(toggle);

    toggle.addEventListener('click', function(e) {
        e.stopPropagation();
        const expanded = hiddenSpan.style.display !== 'none';
        if (expanded) {
        hiddenSpan.style.display = 'none';
        toggle.textContent = `+${hiddenDevs.length} more`;
        } else {
        hiddenSpan.style.display = 'inline';
        toggle.textContent = 'less';
        }
    });
    }

    function openGameModal(gameId) {
    currentGameId = gameId;
    const game = gameDatabase[gameId];
    
    if (!game) return;

    // Update modal content
    document.getElementById('modalGameName').textContent = game.name;
    renderDevelopers(game.developer);
    document.getElementById('modalRating').textContent = `★ ${game.rating}`;
    document.getElementById('modalPlays').textContent = `${game.plays}`;
    document.getElementById('modalGenre').textContent = game.genre;
    document.getElementById('modalDescription').textContent = game.description;
    document.getElementById('modalGameLastUpdated').textContent = game.gamelastupdated || '—';
    document.getElementById('modalGameReleased').textContent = game.gamereleased || '—';

    // Update thumbnail class
    const modalThumb = document.getElementById('modalThumbnail');
    modalThumb.className = `modal-thumb ${game.thumbnail}`;

    // Reset carousel
    carouselIndex = 0;
    populateCarousel(gameId);

    // Show modal
    modal.classList.add('active');
    modal.scrollTop = 0;
    document.body.style.overflow = 'hidden';
    }

    function closeModal() {
    modal.classList.remove('active');
    document.body.style.overflow = 'auto';
    }

    // Close modal
    modalClose.addEventListener('click', closeModal);
    modal.querySelector('.modal-overlay').addEventListener('click', closeModal);

    // Keyboard close
    document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && modal.classList.contains('active')) {
        closeModal();
    }
    });

    // ═══════════════════════════════════════════
    // CAROUSEL FUNCTIONALITY (RECOMMENDED GAMES)
    // Shows exactly 4 cards at a time. Prev/Next move
    // one full page (4 cards) at a time.
    // ═══════════════════════════════════════════
    function populateCarousel(excludeGameId) {
    const carouselTrack = document.getElementById('carouselTrack');
    carouselTrack.innerHTML = '';
    carouselIndex = 0;

    // Get all games except the currently open one, then shuffle randomly
    const allGameIds = Object.keys(gameDatabase);
    const availableGames = allGameIds
        .filter(id => id !== excludeGameId)
        .map(id => gameDatabase[id])
        .sort(() => Math.random() - 0.5);

    availableGames.forEach(game => {
        const carouselItem = document.createElement('div');
        carouselItem.className = 'carousel-item';
        carouselItem.innerHTML = `
        <div class="carousel-card">
            <div class="carousel-thumb ${game.thumbnail}"></div>
            <div class="carousel-info">
            <div class="carousel-name">${game.name}</div>
            <div class="carousel-by">${game.developer}</div>
            <div class="carousel-desc">${game.description}</div>
            <div class="carousel-rating">★ ${game.rating}</div>
            </div>
        </div>
        `;
        carouselItem.addEventListener('click', function() {
        openGameModal(game.id);
        });
        carouselTrack.appendChild(carouselItem);
    });

    updateCarouselPosition();
    }

    function getMaxIndex() {
    const totalItems = document.querySelectorAll('.carousel-item').length;
    return Math.max(0, totalItems - CAROUSEL_VISIBLE_ITEMS);
    }

    function updateCarouselPosition() {
    const carouselTrack = document.getElementById('carouselTrack');
    const container = document.querySelector('.carousel-container');
    const containerWidth = container.offsetWidth;
    const gap = 12;
    const itemWidth = (containerWidth - gap * (CAROUSEL_VISIBLE_ITEMS - 1)) / CAROUSEL_VISIBLE_ITEMS;
    const totalShift = carouselIndex * (itemWidth + gap);
    carouselTrack.style.transform = `translateX(-${totalShift}px)`;

    // Enable/disable buttons at the edges
    const maxIndex = getMaxIndex();
    carouselPrev.classList.toggle('disabled', carouselIndex <= 0);
    carouselNext.classList.toggle('disabled', carouselIndex >= maxIndex);
    }

    carouselPrev.addEventListener('click', function() {
    if (carouselIndex > 0) {
        carouselIndex--;
        updateCarouselPosition();
    }
    });

    carouselNext.addEventListener('click', function() {
    const maxIndex = getMaxIndex();
    if (carouselIndex < maxIndex) {
        carouselIndex++;
        updateCarouselPosition();
    }
    });

    // ═══════════════════════════════════════════
    // NAVBAR — Active link toggling
    // Clicking a nav link marks it active and removes
    // the active class from the previously active link.
    // ═══════════════════════════════════════════
    document.querySelectorAll('.nav-links a').forEach(link => {
    link.addEventListener('click', function(e) {

        const href = this.getAttribute('href');
        if (href === '#') {
        e.preventDefault();
        }
        // Remove active from all links
        document.querySelectorAll('.nav-links a').forEach(l => l.classList.remove('active'));
        // Mark this one active
        this.classList.add('active');
    });
    });

    // ═══════════════════════════════════════════
    // NAVBAR — User dropdown toggle
    // Clicking the avatar button opens/closes the
    // dropdown. Clicking anywhere else closes it.
    // ═══════════════════════════════════════════
    const avatarBtn     = document.getElementById('avatarBtn');
    const userDropdown  = document.getElementById('userDropdown');
    // The wrapper contains both the button and the dropdown
    const avatarWrap    = avatarBtn && avatarBtn.closest('.avatar-wrap');

    // Prevents double-wiring if another script (ex. auth-guard.js) on
    // this same page also sets up the dropdown toggle.
    if (avatarBtn && userDropdown && avatarWrap && !window.__yaw8DropdownWired) {
    window.__yaw8DropdownWired = true;

    avatarBtn.addEventListener('click', function(e) {
    e.stopPropagation();
    const isOpen = userDropdown.classList.contains('open');
    userDropdown.classList.toggle('open', !isOpen);
    avatarBtn.classList.toggle('open', !isOpen);
    });

    // Close dropdown when clicking anywhere outside the avatar wrapper
    document.addEventListener('click', function(e) {
    if (!avatarWrap.contains(e.target)) {
        userDropdown.classList.remove('open');
        avatarBtn.classList.remove('open');
    }
    });

    // Close after dropdown item is clicked
    userDropdown.addEventListener('click', function(e) {
    e.stopPropagation();
    setTimeout(() => {
        userDropdown.classList.remove('open');
        avatarBtn.classList.remove('open');
    }, 120);
    });
    }

    // ═══════════════════════════════════════════
    // SUBMIT GAME MODAL
    // ═══════════════════════════════════════════
    const submitGameModal  = document.getElementById('submit-game-modal');
    const submitModalClose = document.getElementById('submitModalClose');
    const submitCancelBtn  = document.getElementById('submitCancelBtn');
    const btnSubmitGame    = document.querySelector('.btn-submit-game');

    function openSubmitModal() {
    submitGameModal.classList.add('active');
    submitGameModal.scrollTop = 0;
    document.body.style.overflow = 'hidden';
    }

    function closeSubmitModal() {
    submitGameModal.classList.remove('active');
    document.body.style.overflow = 'auto';
    }

    if (btnSubmitGame) btnSubmitGame.addEventListener('click', openSubmitModal);
    submitModalClose.addEventListener('click', closeSubmitModal);
    submitCancelBtn.addEventListener('click', closeSubmitModal);
    submitGameModal.querySelector('.modal-overlay').addEventListener('click', closeSubmitModal);

    document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && submitGameModal.classList.contains('active')) {
        closeSubmitModal();
    }
    });

    // Also wire up footer "Submit Your Game" link if present
    document.querySelectorAll('a').forEach(link => {
    if (link.textContent.trim() === 'Submit Your Game') {
        link.addEventListener('click', function(e) {
        e.preventDefault();
        openSubmitModal();
        });
    }
    });

    // ═══════════════════════════════════════════
    // NOMINATE GAME MODAL
    // ═══════════════════════════════════════════
    const nominateGameModal  = document.getElementById('nominate-game-modal');
    const nominateModalClose = document.getElementById('nominateModalClose');
    const nominateCancelBtn  = document.getElementById('nominateCancelBtn');
    const btnNominate        = document.querySelector('.btn-nominate');

    function openNominateModal() {
    nominateGameModal.classList.add('active');
    nominateGameModal.scrollTop = 0;
    document.body.style.overflow = 'hidden';
    }

    function closeNominateModal() {
    nominateGameModal.classList.remove('active');
    document.body.style.overflow = 'auto';
    }

    if (btnNominate) btnNominate.addEventListener('click', openNominateModal);
    nominateModalClose.addEventListener('click', closeNominateModal);
    nominateCancelBtn.addEventListener('click', closeNominateModal);
    nominateGameModal.querySelector('.modal-overlay').addEventListener('click', closeNominateModal);

    document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && nominateGameModal.classList.contains('active')) {
        closeNominateModal();
    }
    });
    // ═══════════════════════════════════════════
    // LOADING SCREEN
    // Fills the progress bar, then fades out the
    // overlay once the page has fully loaded.
    // ═══════════════════════════════════════════
    (function() {
    const loadingScreen = document.getElementById('loading-screen');
    const loaderBarFill  = document.getElementById('loaderBarFill');
    const loaderText     = document.getElementById('loaderText');

    if (!loadingScreen) return;

    let progress = 0;
    const interval = setInterval(function() {
        // Random increment so it feels alive, capped at 90 until window load fires
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
        }, 500); //(less waiting once the page is actually ready).
    });

    // Prevent scrolling while loading
    document.body.style.overflow = 'hidden';
    })();
    // ═══════════════════════════════════════════
    // EXPLORE NOW — smooth scroll to Featured Games
    // Scrolls a bit short of the very top of the
    // section so the floating navbar doesn't cover it.
    // ═══════════════════════════════════════════
    const exploreLink = document.querySelector('.btn-primary a[href="#featured-games"]');
    const featuredSection = document.getElementById('featured-games');

    if (exploreLink && featuredSection) {
    exploreLink.addEventListener('click', function(e) {
        e.preventDefault();
        const offset = 100; // breathing room below the floating navbar
        const targetY = featuredSection.getBoundingClientRect().top + window.scrollY - offset;
        window.scrollTo({ top: targetY, behavior: 'smooth' });
    });
    }