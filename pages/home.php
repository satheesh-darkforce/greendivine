<?php
/**
 * Home page body.
 * Sections:
 *   1. Hero image carousel
 *   2. Categories grid
 *   3. Today's Deal
 *   4. Why Us
 *   5. Our Process
 *   6. Featured Products
 *   7. Stats counters
 *   8. Reviews
 *   9. Happy Clients marquee
 *  10. Request a Free Quote form
 */
$deal = gd_todays_deal();
?>

<!-- ============================ HERO ============================ -->
<?php
/* Hero layout switch — set $DATA['config']['home_hero_variant'] (1|2|3).
 *   1 = Split carousel   2 = Centered   3 = Immersive full-bleed image      */
$homeHero = (int)($DATA['config']['home_hero_variant'] ?? 1);
$s0       = $DATA['hero'][0] ?? ['heading' => '', 'sub' => '', 'image' => '', 'alt' => '', 'cta' => ['href' => '/products', 'label' => 'Explore']];
?>

<?php if ($homeHero === 2): /* ============== VARIANT 2 — CENTERED ============== */ ?>
<section class="gd-hero gd-hero-centered" aria-label="Welcome to Green Divine">
  <span class="gd-hero-blob gd-hero-blob-1" aria-hidden="true"></span>
  <span class="gd-hero-blob gd-hero-blob-2" aria-hidden="true"></span>
  <div class="container position-relative">
    <div class="gd-hero-center-inner text-center">
      <span class="gd-hero-pill mx-auto">
        <i class="bi bi-star-fill"></i>
        <span>4.9 / 5 · Trusted by 2,000+ Bangalore homes</span>
      </span>
      <h1 class="gd-hero-title"><?= e($s0['heading']) ?></h1>
      <p class="gd-hero-sub mx-auto"><?= e($s0['sub']) ?></p>
      <div class="gd-hero-ctas justify-content-center">
        <a href="<?= e(gd_url($s0['cta']['href'])) ?>" class="btn btn-gd-primary btn-lg">
          <?= e($s0['cta']['label']) ?> <i class="bi bi-arrow-right ms-1"></i>
        </a>
        <a href="tel:<?= e($DATA['site']['phone']) ?>" class="btn btn-gd-ghost btn-lg">
          <i class="bi bi-telephone-fill"></i> <?= e($DATA['site']['phone_label']) ?>
        </a>
      </div>
      <ul class="gd-hero-points list-unstyled justify-content-center">
        <li><i class="bi bi-check2-circle"></i> Free site visit in Bangalore</li>
        <li><i class="bi bi-check2-circle"></i> Genuine Guarantee</li>
        <li><i class="bi bi-check2-circle"></i> Written quote in 24h</li>
      </ul>
    </div>
    <div class="gd-hero-trust">
      <?php foreach ($DATA['stats'] as $s): ?>
        <div class="gd-hero-trust-item">
          <strong><?= (int)$s['number'] ?><?= e($s['suffix']) ?></strong>
          <span><?= e($s['label']) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php elseif ($homeHero === 3): /* ============== VARIANT 3 — IMMERSIVE ============== */ ?>
<section class="gd-hero-immersive" aria-label="Welcome to Green Divine"
         style="background-image:url('<?= e(gd_img($s0['image'], 'hero')) ?>')">
  <div class="container position-relative">
    <div class="gd-hero-immersive-inner text-center">
      <span class="gd-hero-pill gd-hero-pill-light mx-auto">
        <i class="bi bi-star-fill"></i>
        <span>4.9 / 5 · Trusted by 2,000+ Bangalore homes</span>
      </span>
      <h1 class="gd-hero-immersive-title"><?= e($s0['heading']) ?></h1>
      <p class="gd-hero-immersive-sub mx-auto"><?= e($s0['sub']) ?></p>
      <div class="gd-hero-ctas justify-content-center">
        <a href="<?= e(gd_url($s0['cta']['href'])) ?>" class="btn btn-gd-gold btn-lg">
          <?= e($s0['cta']['label']) ?> <i class="bi bi-arrow-right ms-1"></i>
        </a>
        <a href="tel:<?= e($DATA['site']['phone']) ?>" class="btn btn-gd-ghost-light btn-lg">
          <i class="bi bi-telephone-fill"></i> <?= e($DATA['site']['phone_label']) ?>
        </a>
      </div>
    </div>
  </div>
</section>
<div class="container">
  <div class="gd-hero-trust gd-hero-trust-float">
    <?php foreach ($DATA['stats'] as $s): ?>
      <div class="gd-hero-trust-item">
        <strong><?= (int)$s['number'] ?><?= e($s['suffix']) ?></strong>
        <span><?= e($s['label']) ?></span>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php elseif ($homeHero === 4): /* ============== VARIANT 4 — FESTIVAL ============== */ ?>
<?php $fest = $DATA['festival'] ?? []; include __DIR__ . '/../includes/festival-hero.php'; ?>

<?php else: /* ============== VARIANT 1 — SPLIT CAROUSEL (default) ============== */ ?>
<section class="gd-hero" aria-label="Welcome to Green Divine">
  <span class="gd-hero-blob gd-hero-blob-1" aria-hidden="true"></span>
  <span class="gd-hero-blob gd-hero-blob-2" aria-hidden="true"></span>

  <div class="container position-relative">
    <div id="gdHero" class="carousel slide carousel-fade gd-hero-carousel"
         data-bs-ride="carousel" data-bs-interval="6500" data-bs-pause="hover">
      <div class="carousel-inner">
        <?php foreach ($DATA['hero'] as $i => $slide): ?>
          <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
            <div class="gd-hero-row">

              <div class="gd-hero-text">
                <span class="gd-hero-pill">
                  <i class="bi bi-star-fill"></i>
                  <span>4.9 / 5 · Trusted by 2,000+ Bangalore homes</span>
                </span>

                <h1 class="gd-hero-title"><?= e($slide['heading']) ?></h1>
                <p class="gd-hero-sub"><?= e($slide['sub']) ?></p>

                <div class="gd-hero-ctas">
                  <a href="<?= e(gd_url($slide['cta']['href'])) ?>" class="btn btn-gd-primary btn-lg">
                    <?= e($slide['cta']['label']) ?> <i class="bi bi-arrow-right ms-1"></i>
                  </a>
                  <a href="tel:<?= e($DATA['site']['phone']) ?>" class="btn btn-gd-ghost btn-lg">
                    <i class="bi bi-telephone-fill"></i> <?= e($DATA['site']['phone_label']) ?>
                  </a>
                </div>

                <ul class="gd-hero-points list-unstyled">
                  <li><i class="bi bi-check2-circle"></i> Free site visit in Bangalore</li>
                  <li><i class="bi bi-check2-circle"></i> Genuine Guarantee on premium turf</li>
                  <li><i class="bi bi-check2-circle"></i> Transparent, written quote in 24h</li>
                </ul>
              </div>

              <div class="gd-hero-visual">
                <div class="gd-hero-image-card">
                  <img src="<?= e(gd_img($slide['image'], 'hero')) ?>"
                       alt="<?= e($slide['alt']) ?>"
                       <?= $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>>
                  <span class="gd-hero-badge">
                    <i class="bi bi-patch-check-fill"></i> Premium quality
                  </span>
                </div>

                <div class="gd-hero-floater">
                  <div class="gd-hero-floater-icon">
                    <i class="bi bi-tree-fill"></i>
                  </div>
                  <div class="gd-hero-floater-body">
                    <strong>500+</strong>
                    <span>installations &amp; counting</span>
                  </div>
                </div>
              </div>

            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="carousel-indicators gd-hero-dots">
        <?php foreach ($DATA['hero'] as $i => $slide): ?>
          <button type="button" data-bs-target="#gdHero" data-bs-slide-to="<?= $i ?>"
                  class="<?= $i === 0 ? 'active' : '' ?>"
                  aria-label="Go to slide <?= $i + 1 ?>"></button>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="gd-hero-trust">
      <?php foreach ($DATA['stats'] as $s): ?>
        <div class="gd-hero-trust-item">
          <strong><?= (int)$s['number'] ?><?= e($s['suffix']) ?></strong>
          <span><?= e($s['label']) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ========================== CATEGORIES ========================== -->
<section class="py-5" aria-labelledby="cat-title">
  <div class="container">
    <div class="text-center mb-5 gd-reveal">
      <span class="gd-eyebrow">Explore</span>
      <h2 id="cat-title" class="gd-section-title">Shop by <span>Category</span></h2>
      <p class="text-muted mb-0">Find the perfect green solution for your space.</p>
    </div>

    <div class="row g-4">
      <?php foreach ($DATA['categories'] as $cat): ?>
        <div class="col-6 col-lg-4 gd-reveal">
          <?php // Landing page at /<slug> when one exists, else the filtered product listing. ?>
          <a class="gd-cat-card" href="<?= e(gd_url((gd_find_landing($cat['slug']) ? '/' : '/products/') . $cat['slug'])) ?>">
            <div class="gd-cat-media">
              <img src="<?= e(gd_img($cat['image'], 'categories')) ?>" alt="<?= e($cat['name']) ?>" loading="lazy">
              <div class="gd-cat-overlay">
                <span class="gd-cat-icon"><i class="bi <?= e($cat['icon']) ?>"></i></span>
                <h3 class="gd-cat-name"><?= e($cat['name']) ?></h3>
              </div>
            </div>
            <p class="gd-cat-desc"><?= e($cat['desc']) ?></p>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ========================= TODAY'S DEAL ========================= -->
<?php if ($deal): $dealRating = gd_rating($deal['slug']); ?>
<section class="py-5 gd-bg-soft" aria-labelledby="deal-title">
  <div class="container">
    <div class="text-center mb-4 gd-reveal">
      <span class="gd-eyebrow">Limited Time</span>
      <h2 id="deal-title" class="gd-section-title">Today's <span>Deal</span></h2>
    </div>

    <div class="gd-deal row g-0 align-items-stretch gd-reveal">
      <div class="col-lg-6 gd-deal-img">
        <img src="<?= e(gd_img($deal['images'][0] ?? '', 'products')) ?>" alt="<?= e($deal['name']) ?>" loading="lazy">
        <span class="gd-deal-badge"><i class="bi bi-lightning-charge-fill"></i> Limited Time</span>
      </div>
      <div class="col-lg-6 gd-deal-body d-flex flex-column justify-content-center">
        <span class="gd-eyebrow mb-2">Featured</span>
        <h3 class="mb-2"><?= e($deal['name']) ?></h3>

        <?php if ($dealRating['count'] > 0): ?>
          <div class="gd-rating-row small mb-2">
            <span class="gd-stars text-warning"><?= gd_stars($dealRating['avg']) ?></span>
            <span class="text-muted ms-2"><?= e((string)$dealRating['avg']) ?> · <?= e((string)$dealRating['count']) ?> reviews</span>
          </div>
        <?php endif; ?>

        <p class="text-muted"><?= e($deal['short']) ?></p>

        <ul class="list-unstyled gd-feature-list gd-feature-grid mb-3">
          <?php foreach (array_slice($deal['features'], 0, 4) as $f): ?>
            <li><i class="bi bi-check-circle-fill"></i> <?= e($f) ?></li>
          <?php endforeach; ?>
        </ul>

        <?php if (gd_show_price($deal)): ?>
          <span class="h4 mb-3 text-success d-block">
            <?= e($deal['price_label']) ?>
            <?php if (!empty($deal['price_was'])): ?>
              <small class="gd-price-was ms-1"><?= e($deal['price_was']) ?></small>
            <?php endif; ?>
          </span>
        <?php else: ?>
          <span class="h5 mb-3 text-success d-block">Price on request</span>
        <?php endif; ?>
        <div class="gd-deal-cta">
          <a class="btn btn-gd-primary" href="<?= e(gd_url('/product/' . $deal['slug'])) ?>">
            View Product <i class="bi bi-arrow-right ms-1"></i>
          </a>
          <a class="gd-buy-wa" href="<?= e(gd_whatsapp_link($deal)) ?>" target="_blank" rel="noopener">
            <i class="bi bi-whatsapp"></i> Buy on WhatsApp
          </a>
        </div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ============================ WHY US (editorial, not another icon grid) ============================ -->
<?php
  $whyEditorial = $DATA['page_content']['home']['why_us_editorial'];
  $topWhy = array_slice($DATA['why_us'], 0, 4); $wStat = $DATA['stats'][1] ?? null; /* 500+ Turf Installations */
?>
<section class="py-5 gd-why-editorial" aria-labelledby="why-title">
  <div class="container">
    <div class="row g-5 align-items-center">
      <div class="col-lg-6 gd-reveal-left gd-reveal">
        <div class="gd-why-media">
          <img src="<?= e(gd_img($whyEditorial['image'], 'installations')) ?>" alt="An artificial turf lawn Green Divine installed in Bangalore" loading="lazy">
          <?php if ($wStat): ?>
            <div class="gd-why-media-stat">
              <strong><?= (int)$wStat['number'] ?><?= e($wStat['suffix']) ?></strong>
              <span><?= e($wStat['label']) ?></span>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-lg-6 gd-reveal-right gd-reveal">
        <span class="gd-eyebrow"><?= e($whyEditorial['eyebrow']) ?></span>
        <h2 id="why-title" class="gd-section-title"><?= e($whyEditorial['title']) ?> <span><?= e($whyEditorial['title_accent']) ?></span></h2>
        <p class="text-muted mt-2"><?= e($whyEditorial['lead']) ?></p>
        <ul class="list-unstyled gd-why-list mt-4">
          <?php foreach ($topWhy as $w): ?>
            <li>
              <i class="bi <?= e($w['icon']) ?>"></i>
              <div>
                <strong><?= e($w['title']) ?></strong>
                <span><?= e($w['desc']) ?></span>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- ============================ PROCESS ============================ -->
<section class="py-5 gd-bg-soft" aria-labelledby="proc-title">
  <div class="container">
    <div class="text-center mb-5 gd-reveal">
      <span class="gd-eyebrow">How It Works</span>
      <h2 id="proc-title" class="gd-section-title">Our <span>4-Step Process</span></h2>
      <p class="text-muted mb-0">From your first call to a finished install — predictably, on time.</p>
    </div>
    <?php $steps = $DATA['process']; include __DIR__ . '/../includes/process-steps.php'; ?>
  </div>
</section>

<!-- ======================== FEATURED PRODUCTS ======================== -->
<section class="py-5" aria-labelledby="feat-title">
  <div class="container">
    <div class="text-center mb-5 gd-reveal">
      <span class="gd-eyebrow">Shop</span>
      <h2 id="feat-title" class="gd-section-title">Featured <span>Products</span></h2>
    </div>
    <div class="row g-4">
      <?php foreach (array_slice($DATA['products'], 0, 3) as $product): ?>
        <div class="col-12 col-sm-6 col-lg-4 gd-reveal">
          <?php include __DIR__ . '/../includes/product-card.php'; ?>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-4">
      <a class="btn btn-gd-outline" href="<?= e(gd_url('/products')) ?>">
        View All Products <i class="bi bi-arrow-right ms-1"></i>
      </a>
    </div>
  </div>
</section>

<!-- ===================== EXPERIENCE & EXCELLENCE ===================== -->
<section class="gd-stats py-5" aria-labelledby="stats-title">
  <div class="container">
    <div class="text-center mb-4">
      <h2 id="stats-title" class="gd-section-title text-white">Eight Years of Bangalore Installs</h2>
      <p class="mb-0 opacity-75">No franchises, no sub-contractors — every number below is one crew's work.</p>
    </div>
    <div class="row text-center g-4 mt-2">
      <?php foreach ($DATA['stats'] as $s): ?>
        <div class="col-6 col-md-3">
          <div class="gd-stat-num">
            <span data-counter="<?= (int)$s['number'] ?>">0</span><?= e($s['suffix']) ?>
          </div>
          <div class="gd-stat-label"><?= e($s['label']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============================ REVIEWS ============================ -->
<?php
$reviews_title = 'Loved by 2000+ Bangalore homes & offices';
$reviews_limit = 6;
include __DIR__ . '/../includes/reviews.php';
?>

<!-- ====================== HAPPY CLIENTS MARQUEE ====================== -->
<section class="py-5 gd-bg-soft" aria-labelledby="clients-title">
  <div class="container">
    <div class="text-center mb-4 gd-reveal">
      <span class="gd-eyebrow">Trusted by</span>
      <h2 id="clients-title" class="gd-section-title">Our <span>Happy Clients</span></h2>
      <p class="text-muted mb-0">Hover to pause.</p>
    </div>
  </div>
  <div class="gd-marquee">
    <div class="gd-marquee-track">
      <?php foreach ($DATA['clients'] as $c): ?>
        <div class="gd-marquee-item" title="<?= e($c['name']) ?>">
          <img src="<?= e(gd_img($c['logo'], 'clients')) ?>" alt="<?= e($c['name']) ?> logo" loading="lazy">
          <span><?= e($c['name']) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ====================== REQUEST A FREE QUOTE ====================== -->
<?php $quoteSection = $DATA['page_content']['home']['quote_section']; ?>
<section class="py-5" aria-labelledby="quote-title">
  <div class="container">
    <div class="row align-items-stretch g-4">

      <div class="col-lg-5 gd-reveal">
        <span class="gd-eyebrow"><?= e($quoteSection['eyebrow']) ?></span>
        <h2 id="quote-title" class="gd-section-title"><?= e($quoteSection['title']) ?> <span><?= e($quoteSection['title_accent']) ?></span></h2>
        <p class="gd-quote-lede mt-2">
          <?= e($quoteSection['lede']) ?>
        </p>
        <ul class="list-unstyled gd-feature-list mt-3">
          <li><i class="bi bi-check-circle-fill"></i> Free site visit in Bangalore</li>
          <li><i class="bi bi-check-circle-fill"></i> Transparent, written quote</li>
          <li><i class="bi bi-check-circle-fill"></i> Genuine Guarantee on premium turf</li>
        </ul>
        <div class="d-flex flex-wrap gap-2 mt-3">
          <a class="btn btn-gd-outline" href="tel:<?= e($DATA['site']['phone']) ?>">
            <i class="bi bi-telephone-fill"></i> <?= e($DATA['site']['phone_label']) ?>
          </a>
          <a class="btn btn-gd-outline" href="mailto:<?= e($DATA['site']['email']) ?>">
            <i class="bi bi-envelope-fill"></i> Email Us
          </a>
        </div>
      </div>

      <div class="col-lg-7 gd-reveal">
        <form class="gd-quote" data-gd-form
              data-gs-endpoint="<?= e($DATA['config']['google_sheet_endpoint']) ?>"
              data-source="home-quote"
              data-fallback-email="<?= e($DATA['site']['email']) ?>" novalidate>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="q-name">Your name</label>
              <input class="form-control" id="q-name" name="name" type="text"
                     placeholder="e.g. Priya Sharma" autocomplete="name" required>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="q-phone">Phone</label>
              <input class="form-control" id="q-phone" name="phone" type="tel"
                     placeholder="e.g. 98450 12345" autocomplete="tel"
                     inputmode="tel" required>
            </div>
            <!-- On phones this stays collapsed until tapped — name + phone is
                 enough to submit, everything else is optional detail. Desktop
                 shows it open by default (see .gd-quote-more in style.css). -->
            <div class="col-12">
              <details class="gd-quote-more">
                <summary>Add more details <span>(optional — helps us quote faster)</span></summary>
                <div class="row g-3 mt-0">
                  <div class="col-md-6">
                    <label class="form-label" for="q-email">Email <span class="text-muted fw-normal">(optional)</span></label>
                    <input class="form-control" id="q-email" name="email" type="email"
                           placeholder="you@example.com" autocomplete="email"
                           inputmode="email">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="q-interest">Interested in</label>
                    <select class="form-control" id="q-interest" name="interest">
                      <?php foreach ($DATA['categories'] as $c): ?>
                        <option value="<?= e($c['slug']) ?>"><?= e($c['name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-12">
                    <label class="form-label" for="q-msg">Tell us about your space</label>
                    <textarea class="form-control" id="q-msg" name="message" rows="4" placeholder="Approximate area, location, timeline..."></textarea>
                  </div>
                </div>
              </details>
            </div>
            <div class="col-12 d-flex flex-wrap align-items-center gap-2">
              <button class="btn btn-gd-primary btn-lg" type="submit">
                Send Request <i class="bi bi-send ms-1"></i>
              </button>
              <span class="small text-success d-none" data-gd-success>
                <i class="bi bi-check-circle-fill"></i> Thanks — we'll reach out shortly.
              </span>
              <span class="small text-danger d-none" data-gd-error>
                <i class="bi bi-exclamation-triangle-fill"></i> Something went wrong. Please try again.
              </span>
            </div>
          </div>
        </form>
      </div>

    </div>
  </div>
</section>


<?php /* Phone-only quote popup (renders only if enabled in data.php) */ include __DIR__ . "/../includes/quote-popup.php"; ?>
