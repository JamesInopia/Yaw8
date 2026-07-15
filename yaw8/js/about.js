// ═══════════════════════════════════════════════════
    // ABOUT.JS
    // Logic used only on about.html.
// ═══════════════════════════════════════════════════

    // ═══════════════════════════════════════════
    // SCROLL REVEAL
    // Fades + slides team cards and comment cards
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
    }, { threshold: 0.15 });

    targets.forEach((el, i) => {
        // Small stagger so cards in the same row don't all pop at once
        el.style.transitionDelay = `${(i % 5) * 60}ms`;
        observer.observe(el);
    });
    })();