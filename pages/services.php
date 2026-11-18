<?php /* Services — detailed multi-section */ ?>

<!-- ============================ HERO ============================ -->
<section class="gd-subhero" style="background-image:url('<?= e(gd_img('grass-2.jpg')) ?>')">
  <div class="container">
    <span class="gd-eyebrow">What We Do</span>
    <h1>End-to-end <span>landscaping</span> under one roof</h1>
    <p class="lead opacity-90 mb-0">From premium turf to corporate biophilic design — designed, installed and maintained by our in-house team.</p>
  </div>
</section>

<!-- =========================== INTRO CARDS =========================== -->
<section class="py-5">
  <div class="container">
    <div class="text-center mb-5 gd-reveal">
      <span class="gd-eyebrow">Services Overview</span>
      <h2 class="gd-section-title">Everything Your Space <span>Needs</span></h2>
    </div>
    <div class="row g-4">
      <?php foreach ($DATA['services'] as $s): ?>
        <div class="col-md-6 col-lg-4 gd-reveal">
          <div class="gd-why-card h-100">
            <div class="gd-why-icon"><i class="bi <?= e($s['icon']) ?>"></i></div>
            <h3 class="h6 mt-3 mb-1"><?= e($s['title']) ?></h3>
            <p class="small text-muted mb-0"><?= e($s['desc']) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ==================== DETAILED SERVICE BLOCKS ==================== -->
<section class="py-5 gd-bg-soft">
  <div class="container">
    <div class="text-center mb-5 gd-reveal">
      <span class="gd-eyebrow">Deep Dive</span>
      <h2 class="gd-section-title">A Closer Look at <span>Each Service</span></h2>
    </div>

    <?php foreach ($DATA['service_details'] as $i => $sd): ?>
      <div class="row g-4 align-items-center gd-reveal mb-5 <?= $i % 2 ? 'flex-lg-row-reverse' : '' ?>">
        <div class="col-lg-6">
          <img class="img-fluid gd-rounded gd-service-image shadow-sm" src="<?= e(gd_img($sd['image'])) ?>" alt="<?= e($sd['title']) ?>" loading="lazy">
        </div>
        <div class="col-lg-6">
          <span class="gd-eyebrow"><i class="bi <?= e($sd['icon']) ?>"></i> Service</span>
          <h3 class="gd-section-title mt-2"><?= e($sd['title']) ?></h3>
          <p class="text-muted"><?= e($sd['desc']) ?></p>
          <ul class="list-unstyled gd-feature-list">
            <?php foreach ($sd['features'] as $f): ?>
              <li><i class="bi bi-check-circle-fill"></i> <?= e($f) ?></li>
            <?php endforeach; ?>
          </ul>
          <a class="btn btn-gd-primary mt-2" href="<?= e(gd_url('/contact')) ?>">
            Enquire <i class="bi bi-arrow-right ms-1"></i>
          </a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ============================ PROCESS ============================ -->
<section class="py-5">
  <div class="container">
    <div class="text-center mb-5 gd-reveal">
      <span class="gd-eyebrow">How We Work</span>
      <h2 class="gd-section-title">From <span>brief</span> to <span>handover</span></h2>
    </div>
    <?php $steps = $DATA['process']; include __DIR__ . '/../includes/process-steps.php'; ?>
  </div>
</section>

<!-- ====================== INSTALLATION GALLERY ====================== -->
<section class="py-5 gd-bg-soft">
  <div class="container">
    <div class="text-center mb-5 gd-reveal">
      <span class="gd-eyebrow">Recent Work</span>
      <h2 class="gd-section-title">A Glimpse of Our <span>Installations</span></h2>
    </div>
    <div class="row g-3 gd-install-gallery">
      <?php
        $gallery = [];
        foreach ($DATA['products'] as $p) {
            foreach (($p['installations'] ?? []) as $img) $gallery[] = $img;
        }
        $gallery = array_slice(array_unique($gallery), 0, 8);
        foreach ($gallery as $g):
      ?>
        <div class="col-6 col-md-4 col-lg-3 gd-reveal">
          <img src="<?= e(gd_img($g, 'installations')) ?>" alt="Green Divine installation" loading="lazy">
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============================ REVIEWS ============================ -->
<?php
$reviews_title = 'What our clients say';
$reviews_limit = 6;
include __DIR__ . '/../includes/reviews.php';
?>

<!-- ============================== CTA ============================== -->
<?php $servicesCta = $DATA['page_content']['services']['cta']; ?>
<section class="py-5 gd-cta-band">
  <div class="container">
    <div class="gd-cta-card text-center">
      <div class="gd-cta-icon"><i class="bi bi-chat-heart-fill"></i></div>
      <h2 class="mb-2"><?= e($servicesCta['title']) ?></h2>
      <p class="mb-4"><?= e($servicesCta['sub']) ?></p>
      <div class="gd-cta-actions">
        <a class="btn btn-light btn-lg" href="<?= e(gd_url('/contact')) ?>">
          <i class="bi bi-chat-dots-fill me-1"></i> Request a Quote
        </a>
        <a class="btn btn-outline-light btn-lg" href="https://wa.me/<?= e($DATA['site']['whatsapp']) ?>" target="_blank" rel="noopener">
          <i class="bi bi-whatsapp me-1"></i> Chat on WhatsApp
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
