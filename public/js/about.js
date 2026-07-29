// ═══════════════════════════════════════════
// DEVELOPER DATABASE
// ═══════════════════════════════════════════
const aboutDevDatabase = {
    'christine-arenal': {
        name:     'Christine Arenal',
        role:     'Front-end Developer',
        avatar:   'ta-1',
        initials: 'CA',
        quote:    '"Good design is invisible — the player should feel the game, not the interface."',
        rating:   '4.4',
        bio:      'Christine handles the visual layer of YA!W8 — layouts, animations, and the design systems that tie everything together. She believes a great UI should get out of the way and let the game speak.',
        games:    ['color-clash', 'wobble-bao']
    },
    'james-inopia': {
        name:     'James Inopia',
        role:     'Co-Founder · Backend Developer',
        avatar:   'ta-2',
        initials: 'JI',
        quote:    '"Build it fast, then build it right. The players will tell you which parts need fixing."',
        rating:   '4.6',
        bio:      'James co-founded YA!W8 and leads backend infrastructure. He built the routing and data systems that keep the platform running, and has a weakness for racing games with ridiculous drift physics.',
        games:    ['pixel-drift', 'space-cleanup', 'wobble-bao']
    },
    'noah-lonoy': {
        name:     'Noah Lonoy',
        role:     'Co-Founder · Backend Developer',
        avatar:   'ta-3',
        initials: 'NL',
        quote:    '"Every bug is just a feature nobody asked for yet."',
        rating:   '4.6',
        bio:      'Noah co-founded YA!W8 and focuses on game logic and server-side architecture. When he is not debugging, he is probably adding one more mechanic to Box Jumper that nobody asked for.',
        games:    ['box-jumper', 'cyber-cell', 'wobble-bao']
    },
    'harvey-ablen': {
        name:     'Harvey Ablen',
        role:     'Co-Founder · Backend Developer',
        avatar:   'ta-4',
        initials: 'HA',
        quote:    '"Strategy games taught me that the best move is usually the one your opponent does not expect."',
        rating:   '4.7',
        bio:      'Harvey co-founded YA!W8 and specialises in game systems and balance. Tower Tactics started as a weekend experiment and somehow became the most played strategy game on the platform.',
        games:    ['tower-tactics', 'wobble-bao']
    }
};

// ═══════════════════════════════════════════
// HELPER
// ═══════════════════════════════════════════
function getGameName(gameId) {
    if (typeof gameDatabase !== 'undefined' && gameDatabase[gameId]) {
        return gameDatabase[gameId].name;
    }
    return gameId.replace(/-/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
}

// ═══════════════════════════════════════════
// MODAL ELEMENTS
// ═══════════════════════════════════════════
const aboutDevModal      = document.getElementById('aboutDevModal');
const aboutDevModalClose = document.getElementById('aboutDevModalClose');
const aboutDevModalInner = document.getElementById('aboutDevModalInner');
const aboutDevOverlay    = document.getElementById('aboutDevModalOverlay');

// ═══════════════════════════════════════════
// MODAL OPEN / CLOSE FUNCTIONS
// Must be defined before the click listener below
// ═══════════════════════════════════════════
function openAboutDevModal(devId) {
    const dev = aboutDevDatabase[devId];
    if (!dev || !aboutDevModal) return;

    const gamesHTML = dev.games.map(id => `
        <div class="adev-game-pill">
            <svg viewBox="0 0 24 24" style="width:12px;height:12px;fill:var(--cyan);flex-shrink:0;">
                <path d="M15 7.5V2H9v5.5l3 3 3-3zM7.5 9H2v6h5.5l3-3-3-3zM9 16.5V22h6v-5.5l-3-3-3 3zM16.5 9l-3 3 3 3H22V9h-5.5z"/>
            </svg>
            ${getGameName(id)}
        </div>
    `).join('');

    aboutDevModalInner.innerHTML = `
        <div style="display:flex; align-items:center; gap:16px; margin-bottom:20px;">
            <div class="team-avatar ${dev.avatar}" style="width:56px;height:56px;font-size:18px;flex-shrink:0;">
                ${dev.initials}
            </div>
            <div>
                <h2 style="font-size:18px;font-weight:800;color:var(--white);margin:0 0 4px;">${dev.name}</h2>
                <div style="font-size:10px;font-weight:700;color:var(--cyan);text-transform:uppercase;letter-spacing:.05em;">${dev.role}</div>
            </div>
        </div>
        <div class="adev-quote">
            <svg viewBox="0 0 24 24" style="width:16px;height:16px;fill:var(--cyan);flex-shrink:0;margin-top:2px;">
                <path d="M6 17h3l2-4V7H5v6h3zm8 0h3l2-4V7h-6v6h3z"/>
            </svg>
            <span>${dev.quote}</span>
        </div>
        <div class="adev-row">
            <span class="adev-label">Rating</span>
            <span class="adev-rating">★ ${dev.rating}</span>
        </div>
        <div class="adev-row" style="flex-direction:column;align-items:flex-start;gap:6px;">
            <span class="adev-label">About</span>
            <p style="font-size:12px;color:var(--muted);line-height:1.8;margin:0;">${dev.bio}</p>
        </div>
        <div class="adev-row" style="flex-direction:column;align-items:flex-start;gap:8px;">
            <span class="adev-label">Developed Games</span>
            <div style="display:flex;flex-wrap:wrap;gap:8px;">${gamesHTML}</div>
        </div>
    `;

    aboutDevModal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeAboutDevModal() {
    if (!aboutDevModal) return;
    aboutDevModal.classList.remove('active');
    document.body.style.overflow = 'auto';
}

// ═══════════════════════════════════════════
// TEAM CARD CLICKS
// team-card is now a plain <div data-dev-id="..."> with no href, so there's
// no default browser navigation to prevent and nothing else on the page to
// race against — a simple delegated bubble-phase listener is all we need.
// ═══════════════════════════════════════════
document.addEventListener('click', function(e) {
    const card = e.target.closest('.team-card[data-dev-id]');
    if (!card) return;

    openAboutDevModal(card.getAttribute('data-dev-id'));
});

// Close listeners
if (aboutDevModalClose) aboutDevModalClose.addEventListener('click', closeAboutDevModal);
if (aboutDevOverlay)    aboutDevOverlay.addEventListener('click', closeAboutDevModal);
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && aboutDevModal && aboutDevModal.classList.contains('active')) {
        closeAboutDevModal();
    }
});

// ═══════════════════════════════════════════
// SCROLL REVEAL — last so it doesn't block clicks
// ═══════════════════════════════════════════
(function initScrollReveal() {
    const targets = document.querySelectorAll('.team-card, .comment-card');
    if (!targets.length) return;

    if (!('IntersectionObserver' in window)) {
        targets.forEach(el => el.classList.add('revealed'));
        return;
    }

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0 });

    targets.forEach((el, i) => {
        el.style.transitionDelay = `${(i % 5) * 60}ms`;
        observer.observe(el);
        setTimeout(() => el.classList.add('revealed'), 100 + (i * 60));
    });
})();