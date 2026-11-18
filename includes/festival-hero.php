<?php
/**
 * Reusable Festival Offer hero (hero variant 4) — premium split edition.
 * Left: offer copy + CTAs.  Right: framed festive image with a gold seal.
 * Expects: $fest (array) — falls back to $DATA['festival'].
 *
 * Optional keys a caller can set so the same festive copy can carry
 * different artwork per page:
 *   'image_bucket' — subfolder under /assets/images/ (e.g. 'categories')
 *   'image_alt'    — alt text; defaults to the offer title
 * Category landing pages use both to show their OWN hero photo instead of
 * the one global festive image.
 */
$fest = $fest ?? ($DATA['festival'] ?? []);
if (!$fest) return;

$fImgBucket = (string)($fest['image_bucket'] ?? '');
$fImgAlt    = (string)($fest['image_alt'] ?? ($fest['title'] ?? 'Festive offer'));

$fcta  = $fest['cta']  ?? ['label' => 'Claim the Offer', 'href' => '/contact'];
$fcta2 = $fest['cta2'] ?? null;
$fWa   = 'https://wa.me/' . $DATA['site']['whatsapp'] . '?text=' .
         rawurlencode("Hi Green Divine, I'd like to claim the " . ($fest['title'] ?? 'festive offer') . '.');
$fcta2href = (!empty($fcta2['href'])) ? gd_url($fcta2['href']) : $fWa;
$fcta2ext  = empty($fcta2['href']);
?>
<section class="gd-festival-hero" aria-label="<?= e($fest['title'] ?? 'Festival offer') ?>">
  <span class="gd-festival-glow" aria-hidden="true"></span>

  <div class="container position-relative">
    <div class="gd-festival-split">

      <div class="gd-festival-content gd-reveal gd-reveal-left">
        <span class="gd-festival-ribbon">
          <i class="bi bi-stars"></i> <?= e($fest['eyebrow'] ?? 'Festive Offer') ?>
        </span>

        <h1 class="gd-festival-title"><?= e($fest['title'] ?? '') ?></h1>

        <?php if (!empty($fest['highlight'])): ?>
          <div class="gd-festival-offer">
            <span class="gd-festival-offer-text"><?= e($fest['highlight']) ?></span>
          </div>
        <?php endif; ?>

        <?php if (!empty($fest['sub'])): ?>
          <p class="gd-festival-sub"><?= e($fest['sub']) ?></p>
        <?php endif; ?>

        <?php if (!empty($fest['coupon']) || !empty($fest['valid'])): ?>
          <div class="gd-festival-meta">
            <?php if (!empty($fest['coupon'])): ?>
              <span class="gd-festival-coupon"><i class="bi bi-tag-fill"></i> Code <strong><?= e($fest['coupon']) ?></strong></span>
            <?php endif; ?>
            <?php if (!empty($fest['valid'])): ?>
              <span class="gd-festival-valid"><i class="bi bi-clock-history"></i> <?= e($fest['valid']) ?></span>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <div class="gd-festival-ctas">
          <a href="<?= e(gd_url($fcta['href'])) ?>" class="btn btn-gd-gold btn-lg">
            <?= e($fcta['label']) ?> <i class="bi bi-arrow-right ms-1"></i>
          </a>
          <?php if ($fcta2): ?>
            <a href="<?= e($fcta2href) ?>" class="btn btn-gd-ghost-light btn-lg"
               <?= $fcta2ext ? 'target="_blank" rel="noopener"' : '' ?>>
              <i class="bi bi-whatsapp"></i> <?= e($fcta2['label']) ?>
            </a>
          <?php endif; ?>
        </div>
      </div>

      <?php if (!empty($fest['image'])): ?>
        <div class="gd-festival-media gd-reveal gd-reveal-right">
          <div class="gd-festival-media-frame">
            <img src="<?= e(gd_img($fest['image'], $fImgBucket)) ?>" alt="<?= e($fImgAlt) ?>" fetchpriority="high">
          </div>
          <span class="gd-festival-seal" aria-hidden="true">
            <span class="gd-festival-seal-in">
              <i class="bi bi-stars"></i>
              <b>FESTIVE</b>
              <small>OFFER</small>
            </span>
          </span>
        </div>
      <?php endif; ?>

    </div>
  </div>
</section>
