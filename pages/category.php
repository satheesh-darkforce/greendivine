<?php
/**
 * Category Landing Page — clean URL /<slug> (the category's editable slug)
 *
 * One unique, SEO-rich landing page per category. Distinct from the
 * products listing (/products/<slug>) — that page is what the
 * "Explore All Products" button on this landing page points to.
 *
 * Globals set by router.php:
 *   $category — entry from $DATA['categories']
 *   $landing  — entry from $DATA['landing_pages']
 *
 * Sections rendered (top → bottom):
 *   1.  Breadcrumb + JSON-LD BreadcrumbList
 *   2.  Hero (image + headline + CTAs + USPs)
 *   3.  Intro / About this category
 *   4.  Key Benefits grid
 *   5.  Numbers strip (category-specific stats)
 *   6.  Featured Products from category + Explore All CTA
 *   7.  Ad slot #1 (run separate paid creatives here)
 *   8.  Use cases / Where it fits
 *   9.  How we install / process
 *  10.  Completed installations gallery
 *  11.  Ad slot #2
 *  12.  Category-specific FAQs
 *  13.  Final CTA strip (Buy on WhatsApp + Get a Quote)
 *  14.  JSON-LD ItemList of products in this category
 */

if (!isset($category) || !isset($landing)) {
    require __DIR__ . '/404.php';
    return;
}

$products      = gd_products_by_category($category['slug']);
$featured      = array_slice($products, 0, 3);
$site          = $DATA['site'];
$cat_url       = $site['base_url'] . '/' . $category['slug'];
$listing_url   = gd_url('/products/' . $category['slug']);

// WhatsApp quick-enquiry pre-fill (category-level)
$wa_msg     = "Hi Green Divine, I'm interested in your " . $category['name'] . " — $cat_url";
$wa_url     = 'https://wa.me/' . $site['whatsapp'] . '?text=' . rawurlencode($wa_msg);

// Hand category-specific FAQs to the global FAQ carousel (included after this template).
if (!empty($landing['faqs'])) {
    $GLOBALS['gd_page_faqs']         = $landing['faqs'];
    $GLOBALS['gd_page_faqs_eyebrow'] = 'FAQ';
    $GLOBALS['gd_page_faqs_title']   = e($category['name']) . ' — <span>your questions, answered</span>';
    $GLOBALS['gd_page_faqs_sub']     = 'Real questions our ' . strtolower($category['name']) . ' clients ask before they buy.';
}
?>

<!-- (No breadcrumb on landing pages by design.) -->

<!-- =================== HERO =================== -->
<?php
/* Hero layout switch. Per-page override via 'hero_variant' in the landing
 * data file; otherwise the global $DATA['config']['category_hero_variant'].
 *   1 = Split   2 = Showcase (banner + card)   3 = Immersive glass   4 = Festival */
$heroVariant = (int)($landing['hero_variant'] ?? ($DATA['config']['category_hero_variant'] ?? 1));
$catStats    = array_slice($landing['stats'] ?? [], 0, 4);

/* Light 4:3 WebP copy of a categories/ image (categories/slides/<name>.webp,
   made locally from the originals) when one exists; otherwise the original.
   Used by the hero slider and the gallery thumbnails. */
/* Hero secondary button: "Get Quote" (opens includes/hero-quote-modal.php)
   on landing pages with 'hero_quote_form' => true — currently only __sports —
   otherwise the usual "Free Site Visit" link to the contact page. */
$heroQuoteForm = !empty($landing['hero_quote_form']);
$heroSecondaryCta = function (string $btnClass) use ($heroQuoteForm): void {
    if ($heroQuoteForm): ?>
          <button type="button" class="btn <?= e($btnClass) ?> btn-lg" data-bs-toggle="modal" data-bs-target="#gd-quote-modal">
            <i class="bi bi-chat-square-quote"></i> Get Quote
          </button>
    <?php else: ?>
          <a href="<?= e(gd_url('/contact')) ?>" class="btn <?= e($btnClass) ?> btn-lg">
            <i class="bi bi-calendar-check"></i> Free Site Visit
          </a>
    <?php endif;
};

$slideSrc = function (string $img): string {
    $webp = 'slides/' . preg_replace('/\.[a-z0-9]+$/i', '', $img) . '.webp';
    return is_file(__DIR__ . '/../assets/images/categories/' . $webp)
        ? gd_img($webp, 'categories') : gd_img($img, 'categories');
};
?>

<?php if ($heroVariant === 2): /* ========= VARIANT 2 — SHOWCASE (banner + overlapping card) ========= */ ?>
<section class="gd-cat-showcase" aria-label="<?= e($category['name']) ?>">
  <div class="gd-cat-showcase-banner">
    <img src="<?= e(gd_img($landing['hero_image'], 'categories')) ?>"
         alt="<?= e($landing['hero_alt']) ?>" fetchpriority="high">
    <span class="gd-cat-showcase-flag"><i class="bi bi-patch-check-fill"></i> Trusted in Bangalore</span>
  </div>
  <div class="container">
    <div class="gd-cat-showcase-card gd-reveal">
      <span class="gd-eyebrow justify-content-center"><?= e($landing['eyebrow']) ?></span>
      <h1 class="gd-cat-h1"><?= e($landing['h1']) ?></h1>
      <p class="gd-cat-tagline"><?= e($landing['tagline']) ?></p>
      <p class="lead text-muted mx-auto"><?= e($landing['hero_sub']) ?></p>

      <?php if ($catStats): ?>
        <div class="gd-cat-showcase-stats">
          <?php foreach ($catStats as $st): ?>
            <div class="gd-cat-showcase-stat">
              <strong><?= (int)$st['number'] ?><?= e($st['suffix']) ?></strong>
              <span><?= e($st['label']) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="d-flex flex-wrap gap-2 mt-3 justify-content-center">
        <a href="<?= e($listing_url) ?>" class="btn btn-gd-primary btn-lg">
          Explore All Products <i class="bi bi-arrow-right ms-1"></i>
        </a>
        <?php $heroSecondaryCta('btn-gd-outline'); ?>
      </div>
    </div>
  </div>
</section>

<?php elseif ($heroVariant === 3): /* ========= VARIANT 3 — IMMERSIVE GLASS ========= */ ?>
<section class="gd-cat-glass" aria-label="<?= e($category['name']) ?>"
         style="background-image:url('<?= e(gd_img($landing['hero_image'], 'categories')) ?>')">
  <span class="gd-cat-glass-tint" aria-hidden="true"></span>
  <div class="container position-relative">
    <div class="gd-cat-glass-card gd-reveal">
      <span class="gd-eyebrow justify-content-center"><?= e($landing['eyebrow']) ?></span>
      <h1 class="gd-cat-h1"><?= e($landing['h1']) ?></h1>
      <p class="gd-cat-tagline gd-cat-tagline-light"><?= e($landing['tagline']) ?></p>
      <p class="lead mx-auto gd-cat-glass-sub"><?= e($landing['hero_sub']) ?></p>

      <div class="d-flex flex-wrap gap-2 mt-2 justify-content-center">
        <a href="<?= e($listing_url) ?>" class="btn btn-gd-gold btn-lg">
          Explore All Products <i class="bi bi-arrow-right ms-1"></i>
        </a>
        <?php $heroSecondaryCta('btn-gd-ghost-light'); ?>
      </div>

      <?php if ($catStats): ?>
        <div class="gd-cat-glass-stats">
          <?php foreach ($catStats as $st): ?>
            <div class="gd-cat-glass-stat">
              <strong><?= (int)$st['number'] ?><?= e($st['suffix']) ?></strong>
              <span><?= e($st['label']) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php elseif ($heroVariant === 4): /* ========= VARIANT 4 — FESTIVAL ========= */ ?>
<?php
/* The festive OFFER is site-wide, but the artwork must be this category's
 * own: without this every landing page rendered the same global festival
 * photo, so all six categories looked identical above the fold. A landing
 * file that defines its own 'festival' block (with its own image) keeps
 * full control and is left untouched. */
$fest = $landing['festival'] ?? ($DATA['festival'] ?? []);
if (empty($landing['festival']['image']) && !empty($landing['hero_image'])) {
    $fest['image']        = $landing['hero_image'];
    $fest['image_bucket'] = 'categories';
    $fest['image_alt']    = $landing['hero_alt'] ?? $category['name'];
}
include __DIR__ . '/../includes/festival-hero.php';
?>

<?php else: /* ============== VARIANT 1 — SPLIT (default) ============== */ ?>
<section class="gd-cat-hero" aria-label="<?= e($category['name']) ?>">
  <span class="gd-hero-blob gd-hero-blob-1" aria-hidden="true"></span>
  <span class="gd-hero-blob gd-hero-blob-2" aria-hidden="true"></span>

  <div class="container position-relative">
    <div class="row align-items-center g-4">
      <div class="col-lg-6 gd-reveal">
        <span class="gd-eyebrow"><?= e($landing['eyebrow']) ?></span>
        <h1 class="gd-cat-h1"><?= e($landing['h1']) ?></h1>
        <p class="gd-cat-tagline"><?= e($landing['tagline']) ?></p>
        <p class="lead text-muted"><?= e($landing['hero_sub']) ?></p>

        <ul class="list-unstyled gd-feature-list mt-3">
          <?php foreach ($landing['hero_points'] as $point): ?>
            <li><i class="bi bi-check-circle-fill"></i> <?= e($point) ?></li>
          <?php endforeach; ?>
        </ul>

        <div class="d-flex flex-wrap gap-2 mt-4">
          <a href="<?= e($listing_url) ?>" class="btn btn-gd-primary btn-lg">
            Explore All Products <i class="bi bi-arrow-right ms-1"></i>
          </a>
        <?php $heroSecondaryCta('btn-gd-outline'); ?>
        </div>
      </div>

      <div class="col-lg-6 gd-reveal">
        <?php
        /* Hero image slider: the hero image first, then this landing page's
           gallery photos (same images, so it stays on-topic for every service).
           Images come through $slideSrc (light WebP copy when one exists). */
        $heroSlides = array_values(array_unique(array_filter(array_merge(
            [$landing['hero_image'] ?? ''], $landing['gallery'] ?? []
        ), 'is_string')));
        ?>
        <div class="gd-cat-hero-image">
          <?php if (count($heroSlides) > 1): ?>
          <div id="gd-hero-slider" class="carousel slide carousel-fade gd-hero-slider" data-bs-ride="carousel"
               data-bs-interval="3500" data-bs-pause="hover" aria-label="<?= e($category['name']) ?> photos">
            <div class="carousel-inner">
              <?php foreach ($heroSlides as $i => $img): ?>
                <div class="carousel-item<?= $i === 0 ? ' active' : '' ?>">
                  <img src="<?= e($slideSrc($img)) ?>"
                       alt="<?= e($i === 0 ? $landing['hero_alt'] : $category['name'] . ' installation by Green Divine') ?>"
                       width="1000" height="750" decoding="async"
                       <?= $i === 0 ? 'fetchpriority="high"' : 'fetchpriority="low"' ?>>
                </div>
              <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#gd-hero-slider" data-bs-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Previous photo</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#gd-hero-slider" data-bs-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Next photo</span>
            </button>
            <div class="carousel-indicators">
              <?php foreach ($heroSlides as $i => $img): ?>
                <button type="button" data-bs-target="#gd-hero-slider" data-bs-slide-to="<?= $i ?>"
                        <?= $i === 0 ? 'class="active" aria-current="true"' : '' ?>
                        aria-label="Photo <?= $i + 1 ?>"></button>
              <?php endforeach; ?>
            </div>
          </div>
          <?php else: ?>
          <img src="<?= e(gd_img($landing['hero_image'], 'categories')) ?>"
               alt="<?= e($landing['hero_alt']) ?>" fetchpriority="high">
          <?php endif; ?>
          <span class="gd-cat-hero-badge">
            <i class="bi bi-patch-check-fill"></i> Trusted in Bangalore
          </span>
        </div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($heroQuoteForm) include __DIR__ . '/../includes/hero-quote-modal.php'; ?>

<?php
/* Install videos ("Real Videos") + YouTube Shorts. They normally sit after
   the Process section; a landing page with 'videos_above_about' => true
   (currently only __sports) shows them here, just above the About section. */
$videosAboveAbout = !empty($landing['videos_above_about']);
$renderVideos = function () use ($landing, $category) {
    $videos      = $landing['videos'] ?? [];
    $videosTitle = 'Watch our <span>' . e(strtolower($category['name'])) . '</span> installs';
    $videosLede  = 'Short, unedited walkthroughs of finished projects across Bangalore.';
    include __DIR__ . '/../includes/video-showcase.php';

    // YouTube Shorts — only shown when this landing page has a 'youtube_shorts' list.
    $shorts = $landing['youtube_shorts'] ?? [];
    include __DIR__ . '/../includes/youtube-shorts.php';
};

/* "Completed Installations" gallery — normally after the videos further down;
   'gallery_above_about' => true (currently only __sports) shows it here instead. */
$galleryAboveAbout = !empty($landing['gallery_above_about']);
$renderGallery = function () use ($landing, $category, $slideSrc) {
    if (empty($landing['gallery'])) return;
    ?>
<section class="py-5" aria-labelledby="cat-gallery">
  <div class="container">
    <div class="text-center mb-5 gd-reveal">
      <span class="gd-eyebrow">Real installs</span>
      <h2 id="cat-gallery" class="gd-section-title">Completed <span>Installations</span></h2>
      <p class="text-muted mb-0">A few recent projects from our <?= e(strtolower($category['name'])) ?> range.</p>
    </div>

    <div class="row g-3">
      <?php foreach ($landing['gallery'] as $img): ?>
        <div class="col-6 col-md-3 gd-reveal">
          <a href="<?= e(gd_img($img, 'categories')) ?>" class="gd-gallery-tile"
             target="_blank" rel="noopener" aria-label="View installation photo">
            <img src="<?= e($slideSrc($img)) ?>" alt="<?= e($category['name']) ?> installation" loading="lazy" decoding="async">
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
    <?php
};

/* Customer reviews carousel (includes/reviews.php) — only on landing pages
   with 'show_reviews' => true (currently only __sports). Reviews tied to this
   category's products come first, then the site-wide ones. Shown near the
   final CTA, or up here above About when 'reviews_above_about' => true. */
$reviewsAboveAbout = !empty($landing['reviews_above_about']);
$renderReviews = function () use ($category) {
    global $DATA;
    $catProducts = array_column(array_filter($DATA['products'] ?? [],
        fn($p) => ($p['category'] ?? '') === $category['slug']), 'slug');
    $own = $general = [];
    foreach ($DATA['reviews'] ?? [] as $r) {
        if (in_array($r['product'] ?? '', $catProducts, true)) $own[] = $r;
        elseif (empty($r['product'])) $general[] = $r;
    }
    $reviews_list  = array_merge($own, $general);
    $reviews_limit = 8;
    $reviews_title = 'What Our Customers Say';
    include __DIR__ . '/../includes/reviews.php';
};

if ($videosAboveAbout)  $renderVideos();
if ($galleryAboveAbout) $renderGallery();
if (!empty($landing['show_reviews']) && $reviewsAboveAbout) $renderReviews();
?>

<!-- =================== INTRO =================== -->
<section class="py-5" aria-labelledby="cat-about">
  <div class="container">
    <div class="row justify-content-center text-center">
      <div class="col-lg-9 gd-reveal">
        <span class="gd-eyebrow">About</span>
        <h2 id="cat-about" class="gd-section-title">
          What makes our <span><?= e($category['name']) ?></span> different
        </h2>
        <div class="gd-cat-intro mt-3 text-muted">
          <?= $landing['intro'] /* trusted markup in data.php */ ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- =================== BENEFITS =================== -->
<section class="py-5 gd-bg-soft" aria-labelledby="cat-benefits">
  <div class="container">
    <div class="text-center mb-5 gd-reveal">
      <span class="gd-eyebrow">Why choose us</span>
      <h2 id="cat-benefits" class="gd-section-title">Built-in <span>Benefits</span></h2>
    </div>

    <div class="row g-4">
      <?php foreach ($landing['benefits'] as $b): ?>
        <div class="col-6 col-lg-4 gd-reveal">
          <div class="gd-why-card h-100">
            <div class="gd-why-icon"><i class="bi <?= e($b['icon']) ?>"></i></div>
            <h3 class="h6 mt-3 mb-1"><?= e($b['title']) ?></h3>
            <p class="small text-muted mb-0"><?= e($b['desc']) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- =================== STATS STRIP =================== -->
<?php if (!empty($landing['stats'])): ?>
<section class="gd-cat-stats py-5" aria-label="Numbers">
  <div class="container">
    <?php /* justify-content-center so a landing page with three stats
             stays centred instead of hanging off the left edge. */ ?>
    <div class="row text-center g-4 justify-content-center">
      <?php foreach ($landing['stats'] as $s): ?>
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
<?php endif; ?>

<!-- =================== FEATURED PRODUCTS =================== -->
<section class="py-5" aria-labelledby="cat-products">
  <div class="container">
    <div class="text-center mb-5 gd-reveal">
      <span class="gd-eyebrow">Shop</span>
      <h2 id="cat-products" class="gd-section-title">
        Top picks in <span><?= e($category['name']) ?></span>
      </h2>
      <p class="text-muted mb-0">Hand-picked products from this range. Tap a card to view full specs.</p>
    </div>

    <?php if (!empty($featured)): ?>
      <div class="row g-4">
        <?php foreach ($featured as $product): ?>
          <div class="col-12 col-sm-6 col-lg-4 gd-reveal">
            <?php include __DIR__ . '/../includes/product-card.php'; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="text-center mt-5">
        <a class="btn btn-gd-primary btn-lg" href="<?= e($listing_url) ?>">
          Explore All <?= e($category['name']) ?> <i class="bi bi-arrow-right ms-1"></i>
        </a>
      </div>
    <?php else: ?>
      <div class="text-center text-muted">
        Catalogue refresh in progress — meanwhile, <a href="<?= e(gd_url('/contact')) ?>">request a quote</a>.
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- =================== AD SLOT #1 =================== -->
<?php if (!empty($landing['ad_slots'][0])): $ad = $landing['ad_slots'][0]; ?>
<section class="py-5 gd-bg-soft" aria-label="Featured offer">
  <div class="container">
    <div class="gd-ad-banner gd-reveal">
      <div class="gd-ad-media">
        <img src="<?= e(gd_img($ad['image'], 'categories')) ?>" alt="<?= e($ad['title']) ?>" loading="lazy">
      </div>
      <div class="gd-ad-body">
        <span class="gd-eyebrow"><?= e($ad['eyebrow']) ?></span>
        <h2 class="h3 mt-2 mb-2"><?= e($ad['title']) ?></h2>
        <p class="text-muted mb-3"><?= e($ad['sub']) ?></p>
        <a class="btn btn-gd-primary" href="<?= e(gd_url($ad['cta']['href'])) ?>">
          <?= e($ad['cta']['label']) ?> <i class="bi bi-arrow-right ms-1"></i>
        </a>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- =================== USE CASES =================== -->
<?php if (!empty($landing['use_cases'])): ?>
<section class="py-5" aria-labelledby="cat-uses">
  <div class="container">
    <div class="text-center mb-5 gd-reveal">
      <span class="gd-eyebrow">Where it fits</span>
      <h2 id="cat-uses" class="gd-section-title">Perfect for <span>every space</span></h2>
    </div>

    <div class="row g-4">
      <?php foreach ($landing['use_cases'] as $u): ?>
        <div class="col-6 col-md-4 gd-reveal">
          <div class="gd-usecase-card h-100">
            <i class="bi <?= e($u['icon']) ?>"></i>
            <h3 class="h6 mt-2 mb-1"><?= e($u['title']) ?></h3>
            <p class="small text-muted mb-0"><?= e($u['desc']) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- =================== PROCESS =================== -->
<?php if (!empty($landing['process'])): ?>
<section class="py-5 gd-bg-soft" aria-labelledby="cat-process">
  <div class="container">
    <div class="text-center mb-5 gd-reveal">
      <span class="gd-eyebrow">How it works</span>
      <h2 id="cat-process" class="gd-section-title">From enquiry to <span>hand-over</span></h2>
    </div>

    <?php $steps = $landing['process']; include __DIR__ . '/../includes/process-steps.php'; ?>
  </div>
</section>
<?php endif; ?>

<!-- =================== INSTALL VIDEOS =================== -->
<?php if (!$videosAboveAbout) $renderVideos(); ?>

<!-- =================== GALLERY =================== -->
<?php if (!$galleryAboveAbout) $renderGallery(); ?>

<!-- =================== AD SLOT #2 =================== -->
<?php if (!empty($landing['ad_slots'][1])): $ad2 = $landing['ad_slots'][1]; ?>
<section class="py-5 gd-bg-soft" aria-label="Featured offer">
  <div class="container">
    <div class="gd-ad-banner gd-ad-banner-flip gd-reveal">
      <div class="gd-ad-media">
        <img src="<?= e(gd_img($ad2['image'], 'categories')) ?>" alt="<?= e($ad2['title']) ?>" loading="lazy">
      </div>
      <div class="gd-ad-body">
        <span class="gd-eyebrow"><?= e($ad2['eyebrow']) ?></span>
        <h2 class="h3 mt-2 mb-2"><?= e($ad2['title']) ?></h2>
        <p class="text-muted mb-3"><?= e($ad2['sub']) ?></p>
        <a class="btn btn-gd-primary" href="<?= e(gd_url($ad2['cta']['href'])) ?>">
          <?= e($ad2['cta']['label']) ?> <i class="bi bi-arrow-right ms-1"></i>
        </a>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- (Category FAQs are rendered by the global FAQ component, fed via $GLOBALS['gd_page_faqs'].) -->

<!-- =================== TESTIMONIALS =================== -->
<?php if (!empty($landing['show_reviews']) && !$reviewsAboveAbout) $renderReviews(); ?>

<!-- =================== FINAL CTA STRIP =================== -->
<section class="py-5 gd-cat-cta" aria-label="Get a quote">
  <div class="container">
    <div class="gd-cat-cta-card gd-reveal">
      <div class="gd-cat-cta-text">
        <h2 class="h3 mb-2">Ready to start your <?= e(strtolower($category['name'])) ?> project?</h2>
        <p class="mb-0 opacity-90">
          Free site visit in Bangalore. Transparent, written quote within 24 hours.
        </p>
      </div>
      <div class="gd-cat-cta-actions">
        <a class="btn btn-light btn-lg" href="<?= e(gd_url('/contact')) ?>">
          <i class="bi bi-calendar-check"></i> Get a Free Quote
        </a>
        <a class="btn btn-outline-light btn-lg" href="<?= e($wa_url) ?>" target="_blank" rel="noopener">
          <i class="bi bi-whatsapp"></i> Chat on WhatsApp
        </a>
      </div>
    </div>
  </div>
</section>

<!-- =================== JSON-LD: ItemList + FAQPage =================== -->
<?php if (!empty($products)): ?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "ItemList",
  "name": "<?= e($category['name']) ?> — Green Divine",
  "itemListElement": [
    <?php foreach ($products as $i => $p):
      $purl = $site['base_url'] . '/product/' . $p['slug'];
    ?>
    {
      "@type": "ListItem",
      "position": <?= $i + 1 ?>,
      "url": "<?= e($purl) ?>",
      "name": "<?= e($p['name']) ?>"
    }<?= $i === count($products) - 1 ? '' : ',' ?>
    <?php endforeach; ?>
  ]
}
</script>
<?php endif; ?>

<?php if (!empty($landing['faqs'])): ?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    <?php foreach ($landing['faqs'] as $i => $f): ?>
    {
      "@type": "Question",
      "name": <?= json_encode($f['q'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
      "acceptedAnswer": {
        "@type": "Answer",
        "text": <?= json_encode($f['a'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
      }
    }<?= $i === count($landing['faqs']) - 1 ? '' : ',' ?>
    <?php endforeach; ?>
  ]
}
</script>
<?php endif; ?>


<?php /* Phone-only quote popup (renders only if enabled in data.php) */ include __DIR__ . "/../includes/quote-popup.php"; ?>
