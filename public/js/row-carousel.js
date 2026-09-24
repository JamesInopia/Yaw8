// ═══════════════════════════════════════════════════
// ROW-CAROUSEL.JS
// Left / right arrow buttons for the single-row sections on the Games page
// (Top Played, Top Rated, Trending). Each arrow press moves one "page" of
// cards, i.e. the same number that is visible at once (4, or 2 on small screens).
//
// Deliberately does NOT touch the "All Games" grid: it only looks for the two
// wrapper classes below, which All Games doesn't use.
// ═══════════════════════════════════════════════════
(function initRowCarousels() {
  const ROW_SELECTOR =
    ".games-grid > .top-played-games-all-time, .games-grid > .top-played-games-weekly";

  const CHEVRON_LEFT =
    '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15.41 7.41 14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>';
  const CHEVRON_RIGHT =
    '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8.59 16.59 10 18l6-6-6-6-1.41 1.41L13.17 12z"/></svg>';

  const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  function makeArrow(direction) {
    const btn = document.createElement("button");
    btn.type = "button";
    btn.className = "row-arrow row-arrow-" + direction;
    btn.setAttribute("aria-label", direction === "prev" ? "Scroll left" : "Scroll right");
    btn.innerHTML = direction === "prev" ? CHEVRON_LEFT : CHEVRON_RIGHT;
    return btn;
  }

  function setUp(row) {
    // Wrap the row (moving it keeps every listener already bound to its cards)
    const carousel = document.createElement("div");
    carousel.className = "row-carousel";
    row.parentNode.insertBefore(carousel, row);
    carousel.appendChild(row);

    const prev = makeArrow("prev");
    const next = makeArrow("next");
    carousel.appendChild(prev);
    carousel.appendChild(next);

    // Distance to move for one press: (cards visible) x (one card + gap)
    function pageDistance() {
      const cards = row.querySelectorAll(":scope > .game-card");
      if (cards.length < 2) return row.clientWidth;

      const stride = cards[1].offsetLeft - cards[0].offsetLeft;
      const perView =
        parseFloat(getComputedStyle(row).getPropertyValue("--row-cols")) || 4;

      return stride * perView;
    }

    function scrollRow(direction) {
      row.scrollBy({
        left: direction * pageDistance(),
        behavior: prefersReducedMotion ? "auto" : "smooth",
      });
    }

    // Vertically center the arrows on the thumbnail image (not the whole card),
    // so they never cover the title row. offsetTop ignores the hover lift.
    function positionArrows() {
      const card = row.querySelector(":scope > .game-card");
      const thumb = card && card.querySelector(".game-thumb");
      if (!thumb) return;

      const centerY = card.offsetTop + thumb.offsetTop + thumb.offsetHeight / 2;
      carousel.style.setProperty("--arrow-top", centerY + "px");
    }

    function updateArrows() {
      positionArrows();
      const maxScroll = row.scrollWidth - row.clientWidth;

      carousel.classList.toggle("no-overflow", maxScroll <= 1);
      prev.disabled = row.scrollLeft <= 1;
      next.disabled = row.scrollLeft >= maxScroll - 1;
    }

    let queued = false;
    function queueUpdate() {
      if (queued) return;
      queued = true;
      requestAnimationFrame(function () {
        queued = false;
        updateArrows();
      });
    }

    prev.addEventListener("click", function () { scrollRow(-1); });
    next.addEventListener("click", function () { scrollRow(1); });
    row.addEventListener("scroll", queueUpdate, { passive: true });
    window.addEventListener("resize", queueUpdate);
    if (typeof ResizeObserver === "function") {
      new ResizeObserver(queueUpdate).observe(row);
    }

    updateArrows();
  }

  document.querySelectorAll(ROW_SELECTOR).forEach(setUp);
})();
