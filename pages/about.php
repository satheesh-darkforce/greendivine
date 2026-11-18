<?php /* About — full multi-section narrative */ ?>

<!-- ============================ HERO ============================ -->
<section class="gd-subhero" style="background-image:url('<?= e(gd_img('vg-2.jpg')) ?>')">
  <div class="container">
    <span class="gd-eyebrow">About Us</span>
    <h1>Bangalore's green <span>specialists</span></h1>
    <p class="lead opacity-90 mb-0">Designing, building and caring for sustainable green spaces since 2017.</p>
  </div>
</section>

<!-- =========================== STORY =========================== -->
<?php $story = $DATA['page_content']['about']['story']; ?>
<section class="py-5">
  <div class="container">
    <div class="row g-5 align-items-center">
      <div class="col-lg-6 gd-reveal">
        <img class="img-fluid gd-rounded shadow-sm" src="<?= e(gd_img($story['image'])) ?>" alt="Green Divine team at work" loading="lazy">
      </div>
      <div class="col-lg-6 gd-reveal">
        <span class="gd-eyebrow"><?= e($story['eyebrow']) ?></span>
        <h2 class="gd-section-title"><?= e($story['title']) ?> <span><?= e($story['title_accent']) ?></span></h2>
        <p class="lead mt-2"><?= e($story['lead']) ?></p>
        <p><?= e($story['body']) ?></p>
        <ul class="list-unstyled gd-feature-list mt-3">
          <?php foreach ($story['bullets'] as $bullet): ?>
            <li><i class="bi bi-check-circle-fill"></i> <?= e($bullet) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- ====================== MISSION / VALUES ====================== -->
<section class="py-5 gd-bg-soft">
  <div class="container">
    <div class="gd-pillars">
      <?php foreach ($DATA['page_content']['about']['pillars'] as $pillar): ?>
        <div class="gd-pillar gd-reveal">
          <div class="gd-pillar-icon"><i class="bi <?= e($pillar['icon']) ?>"></i></div>
          <h3><?= e($pillar['title']) ?></h3>
          <p><?= e($pillar['desc']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============================ WHY US ============================ -->
<section class="py-5">
  <div class="container">
    <div class="text-center mb-5 gd-reveal">
      <span class="gd-eyebrow">Why Green Divine</span>
      <h2 class="gd-section-title">What sets us <span>apart</span></h2>
    </div>
    <div class="gd-feature-rows">
      <?php foreach ($DATA['why_us'] as $i => $w): ?>
        <div class="gd-feature-row gd-reveal">
          <span class="gd-feature-row-mark"><?= sprintf('%02d', $i + 1) ?></span>
          <div class="gd-feature-row-icon"><i class="bi <?= e($w['icon']) ?>"></i></div>
          <div class="gd-feature-row-body">
            <h3><?= e($w['title']) ?></h3>
            <p><?= e($w['desc']) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- =========================== STATS =========================== -->
<section class="gd-stats py-5">
  <div class="container">
    <div class="row text-center g-4">
      <?php foreach ($DATA['stats'] as $s): ?>
        <div class="col-6 col-md-3">
          <div class="gd-stat-num"><span data-counter="<?= (int)$s['number'] ?>">0</span><?= e($s['suffix']) ?></div>
          <div class="gd-stat-label"><?= e($s['label']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php /* ============================ TEAM (hidden) ============================
<section class="py-5">
  <div class="container">
    <div class="text-center mb-5 gd-reveal">
      <span class="gd-eyebrow">The Team</span>
      <h2 class="gd-section-title">People behind every <span>install</span></h2>
    </div>
    <div class="row g-4">
      <?php foreach ($DATA['team'] as $m): ?>
        <div class="col-6 col-md-3 gd-reveal">
          <article class="gd-team-card text-center h-100">
            <span class="gd-team-avatar" aria-hidden="true"><?= e(gd_initials($m['name'])) ?></span>
            <h3 class="h6 mt-3 mb-1"><?= e($m['name']) ?></h3>
            <div class="small text-success mb-2"><?= e($m['role']) ?></div>
            <p class="small text-muted mb-0"><?= e($m['bio']) ?></p>
          </article>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
*/ ?>

<!-- ============================ PROCESS ============================ -->
<section class="py-5 gd-bg-soft">
  <div class="container">
    <div class="text-center mb-5 gd-reveal">
      <span class="gd-eyebrow">How We Work</span>
      <h2 class="gd-section-title">A predictable <span>4-step</span> journey</h2>
    </div>
    <?php $steps = $DATA['process']; include __DIR__ . '/../includes/process-steps.php'; ?>
  </div>
</section>

<!-- ============================ REVIEWS ============================ -->
<?php
$reviews_title = 'What our clients say about us';
$reviews_limit = 6;
include __DIR__ . '/../includes/reviews.php';
?>

<!-- ============================== CTA ============================== -->
<?php $aboutCta = $DATA['page_content']['about']['cta']; ?>
<section class="py-5 gd-cta-band">
  <div class="container">
    <div class="gd-cta-card text-center">
      <div class="gd-cta-icon"><i class="bi bi-chat-heart-fill"></i></div>
      <h2 class="mb-2"><?= e($aboutCta['title']) ?></h2>
      <p class="mb-4"><?= e($aboutCta['sub']) ?></p>
      <div class="gd-cta-actions">
        <a class="btn btn-light btn-lg" href="<?= e(gd_url('/contact')) ?>">
          <i class="bi bi-chat-dots-fill me-1"></i> Get a Free Quote
        </a>
        <a class="btn btn-outline-light btn-lg" href="https://wa.me/<?= e($DATA['site']['whatsapp']) ?>" target="_blank" rel="noopener">
          <i class="bi bi-whatsapp me-1"></i> WhatsApp Us
        </a>
      </div>
      <ul class="gd-cta-trust list-unstyled mb-0">
        <li><i class="bi bi-check-circle-fill"></i> Free site visit</li>
        <li><i class="bi bi-check-circle-fill"></i> No obligation</li>
        <li><i class="bi bi-check-circle-fill"></i> 24-hr reply</li>
      </ul>
    </div>
  </div>
</section>
