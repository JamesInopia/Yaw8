<!-- ══════════════════════════════════════════
    PAGE BODY — ABOUT PAGE
══════════════════════════════════════════ -->
<div class="page-wrap-single">

  <!-- ─────────────
      PAGE HEADER
  ───────────── -->
  <section class="page-header"> <!--Change the image/background-->
    <div class="page-header-content">
      <h1><span class="header-text">About</span><span class="accent"><img src="assets/images/Extra.png" alt="YA!W8" class="header-logo-img" /></span></h1>
      <p>The story behind the platform, the crew building it, and the community keeping it alive.</p>
    </div>
  </section>


  <!-- ─────────────
      DESCRIPTION
  ───────────── -->
  <section class="panel">
    <div class="section-header">
      <h2 class="section-title">
        <!-- Info icon -->
        <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
        Our Story
      </h2>
    </div>
    <p style="font-size: 13px; line-height: 1.9; color: var(--muted);">
      YA!W8 started as a hallway joke....
    </p>
  </section>


  <!-- ─────────────
      MEET THE TEAM
  ───────────── -->
  <section class="panel">
    <div class="section-header">
      <h2 class="section-title">
        <!-- People icon -->
        <svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
        Meet the Team
      </h2>
    </div>

    <div class="team-grid">

  <button type="button" class="team-card" data-dev-id="christine-arenal">
    <img src="assets/images/team/christine_pfp.jpg" alt="Christine Arenal" class="team-avatar-img" />
    <div class="team-name">Christine Arenal</div>
    <div class="team-role">Co-Founder · Front-end Developer</div>
    <div class="team-bio">Description</div>
  </button>

  <button type="button" class="team-card" data-dev-id="james-inopia">
    <img src="assets/images/team/james_pfp.jpg" alt="James Inopia" class="team-avatar-img" />
    <div class="team-name">James Inopia</div>
    <div class="team-role">Co-Founder · Backend Developer</div>
    <div class="team-bio">Description</div>
  </button>

  <button type="button" class="team-card" data-dev-id="noah-lonoy">
    <img src="assets/images/team/jae_posting_noy.jpg" alt="Noah Lonoy" class="team-avatar-img" />
    <div class="team-name">Noah Lonoy</div>
    <div class="team-role">Co-Founder · Backend Developer</div>
    <div class="team-bio">Description</div>
  </button>

  <button type="button" class="team-card" data-dev-id="harvey-ablen">
    <img src="assets/images/team/harvey_pfp.jpg" alt="Harvey Ablen" class="team-avatar-img" />
    <div class="team-name">Harvey Ablen</div>
    <div class="team-role">Co-Founder · Backend Developer</div>
    <div class="team-bio">Description</div>
  </button>

</div>

  </section>

<!-- ══════════════════════════════════════════
    DEVELOPER PROFILE MODAL (About Page)
══════════════════════════════════════════ -->
<div class="modal" id="aboutDevModal">
  <div class="modal-overlay" id="aboutDevModalOverlay"></div>
  <div class="modal-content" style="max-width: 560px;">
    <button class="modal-close" id="aboutDevModalClose">
      <svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
    </button>
    <div class="modal-inner" id="aboutDevModalInner">
      <!-- populated by about.js -->
    </div>
  </div>
</div>

</div><!-- end .page-wrap-single -->