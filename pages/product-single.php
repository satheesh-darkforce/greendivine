<?php
/**
 * Single product page.
 * Expects: $product (set by router).
 */
$product = $GLOBALS['product'] ?? null;
if (!$product) { include __DIR__ . '/404.php'; return; }

$cat    = gd_find_category($product['category']);
$waUrl  = gd_whatsapp_link($product);
$rating = gd_rating($product['slug']);
$badge  = $product['badge'] ?? '';
?>

<!-- =========================== BREADCRUMB =========================== -->
<nav aria-label="breadcrumb" class="container pt-4">
  <ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="<?= e(gd_url('/')) ?>">Home</a></li>
    <li class="breadcrumb-item"><a href="<?= e(gd_url('/products')) ?>">Products</a></li>
    <?php if ($cat): ?>
      <li class="breadcrumb-item"><a href="<?= e(gd_url('/products/' . $cat['slug'])) ?>"><?= e($cat['name']) ?></a></li>
    <?php endif; ?>
    <li class="breadcrumb-item active" aria-current="page"><?= e($product['name']) ?></li>
  </ol>
</nav>

<!-- =========================== MAIN VIEW =========================== -->
<section class="py-4">
  <div class="container">
    <div class="row g-5">

      <!-- Image carousel -->
      <div class="col-lg-6">
        <div id="gdProdImgs" class="carousel slide gd-prod-gallery" data-bs-ride="carousel">
          <div class="carousel-inner">
            <?php foreach ($product['images'] as $i => $img): ?>
              <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                <img src="<?= e(gd_img($img, 'products')) ?>" alt="<?= e($product['name']) ?> view <?= $i + 1 ?>" <?= $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>>
              </div>
            <?php endforeach; ?>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#gdProdImgs" data-bs-slide="prev" aria-label="Previous image">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#gdProdImgs" data-bs-slide="next" aria-label="Next image">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
          </button>
          <div class="carousel-indicators">
            <?php foreach ($product['images'] as $i => $img): ?>
              <button type="button" data-bs-target="#gdProdImgs" data-bs-slide-to="<?= $i ?>"
                      class="<?= $i === 0 ? 'active' : '' ?>" aria-label="Image <?= $i + 1 ?>"></button>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Trust strip -->
        <div class="gd-trust-strip mt-3">
          <div><i class="bi bi-truck"></i> Free site visit</div>
          <div><i class="bi bi-shield-check"></i> Genuine guarantee</div>
          <div><i class="bi bi-credit-card-2-front"></i> EMI available</div>
        </div>
      </div>

      <!-- Details -->
      <div class="col-lg-6">
        <?php if ($cat): ?>
          <span class="gd-eyebrow"><?= e($cat['name']) ?></span>
        <?php endif; ?>
        <?php if ($badge): ?>
          <span class="badge bg-success-subtle text-success-emphasis ms-2"><?= e($badge) ?></span>
        <?php endif; ?>

        <h1 class="mt-2"><?= e($product['name']) ?></h1>

        <?php if ($rating['count'] > 0): ?>
          <div class="gd-rating-row mb-2">
            <span class="gd-stars text-warning"><?= gd_stars($rating['avg']) ?></span>
            <strong class="ms-2"><?= e((string)$rating['avg']) ?></strong>
            <a href="#reviews" class="ms-2 small text-muted">(<?= e((string)$rating['count']) ?> reviews)</a>
          </div>
        <?php endif; ?>

        <p class="lead text-muted"><?= e($product['short']) ?></p>

        <?php if (gd_show_price($product)): ?>
          <div class="my-3 d-flex flex-wrap align-items-baseline gap-2">
            <span class="h3 text-success mb-0"><?= e($product['price_label']) ?></span>
            <?php if (!empty($product['price_was'])): ?>
              <span class="gd-price-was fs-5"><?= e($product['price_was']) ?></span>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <?php /* Quote-only: say so plainly instead of leaving a gap where
                   the price was. The "Request a Quote" CTA below is the
                   action, so this line stays informational. */ ?>
          <p class="my-3 h5 text-success mb-0">Price on request</p>
        <?php endif; ?>

        <ul class="list-unstyled gd-feature-list">
          <?php foreach ($product['features'] as $f): ?>
            <li><i class="bi bi-check-circle-fill"></i> <?= e($f) ?></li>
          <?php endforeach; ?>
        </ul>

        <div class="d-flex flex-wrap align-items-center gap-2 mt-4">
          <a class="gd-buy-wa" href="<?= e($waUrl) ?>" target="_blank" rel="noopener">
            <i class="bi bi-whatsapp"></i> Buy on WhatsApp
          </a>
          <a class="btn btn-gd-primary" href="#quote-form">
            <i class="bi bi-clipboard-check"></i> Request a Quote
          </a>
          <a class="btn btn-gd-outline" href="tel:<?= e($DATA['site']['phone']) ?>">
            <i class="bi bi-telephone-fill"></i> Call us
          </a>
        </div>

        <!-- ===================== SHARE ===================== -->
        <?php
          $prodUrl = $DATA['site']['base_url'] . '/product/' . $product['slug'];
          $enc     = rawurlencode($prodUrl);
          $encTxt  = rawurlencode($product['name'] . ' — Green Divine');
        ?>
        <div class="gd-share">
          <span class="gd-share-label">Share this</span>
          <a class="gd-share-btn gd-share-wa" href="https://wa.me/?text=<?= $encTxt ?>%20<?= $enc ?>"
             target="_blank" rel="noopener" aria-label="Share on WhatsApp"><i class="bi bi-whatsapp"></i></a>
          <a class="gd-share-btn gd-share-fb" href="https://www.facebook.com/sharer/sharer.php?u=<?= $enc ?>"
             target="_blank" rel="noopener" aria-label="Share on Facebook"><i class="bi bi-facebook"></i></a>
          <a class="gd-share-btn gd-share-x" href="https://twitter.com/intent/tweet?url=<?= $enc ?>&text=<?= $encTxt ?>"
             target="_blank" rel="noopener" aria-label="Share on X"><i class="bi bi-twitter-x"></i></a>
          <a class="gd-share-btn gd-share-mail" href="mailto:?subject=<?= $encTxt ?>&body=<?= $enc ?>"
             aria-label="Share via email"><i class="bi bi-envelope-fill"></i></a>
          <button type="button" class="gd-share-btn gd-share-copy" data-gd-copy="<?= e($prodUrl) ?>" aria-label="Copy link">
            <i class="bi bi-link-45deg"></i><span class="gd-share-copy-text" data-gd-copy-label>Copy</span>
          </button>
        </div>

        <div class="mt-4 text-muted"><?= $product['description'] /* trusted HTML from $DATA */ ?></div>
      </div>
    </div>
  </div>
</section>

<!-- =========================== SPECS / TABS =========================== -->
<?php if (!empty($product['specs'])): ?>
<section class="py-4 gd-bg-soft">
  <div class="container">
    <h2 class="gd-section-title mb-4">Product <span>Specifications</span></h2>
    <div class="row">
      <div class="col-lg-8">
        <table class="table gd-spec-table mb-0">
          <tbody>
            <?php foreach ($product['specs'] as $k => $v): ?>
              <tr>
                <th scope="row" class="w-25"><?= e($k) ?></th>
                <td><?= e($v) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ====================== QUOTE FORM (Google Sheets) ====================== -->
<section id="quote-form" class="py-5">
  <div class="container">
    <div class="row align-items-stretch g-4">

      <div class="col-lg-5 gd-reveal">
        <span class="gd-eyebrow">Get a Quote</span>
        <h2 class="gd-section-title">Get a quote for <span><?= e($product['name']) ?></span></h2>
        <p class="text-muted mt-2">
          Drop your details — our team will share a transparent, line-item quote within 24 hours.
          Submissions are saved straight to our team's Google Sheet.
        </p>
        <ul class="list-unstyled gd-feature-list mt-3">
          <li><i class="bi bi-check-circle-fill"></i> Free site visit in Bangalore</li>
          <li><i class="bi bi-check-circle-fill"></i> Transparent, written quote</li>
          <li><i class="bi bi-check-circle-fill"></i> No spam, no follow-up calls</li>
        </ul>
      </div>

      <div class="col-lg-7 gd-reveal">
        <form class="gd-quote" data-gd-form
              data-gs-endpoint="<?= e($DATA['config']['google_sheet_endpoint']) ?>"
              data-source="product-<?= e($product['slug']) ?>"
              data-fallback-email="<?= e($DATA['site']['email']) ?>"
              novalidate>
          <input type="hidden" name="product" value="<?= e($product['name']) ?>">
          <input type="hidden" name="product_slug" value="<?= e($product['slug']) ?>">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="qp-name">Your name</label>
              <input class="form-control" id="qp-name" name="name" type="text"
                     placeholder="e.g. Priya Sharma" autocomplete="name" required>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="qp-phone">Phone</label>
              <input class="form-control" id="qp-phone" name="phone" type="tel"
                     placeholder="e.g. 98450 12345" autocomplete="tel"
                     inputmode="tel" required>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="qp-email">Email <span class="text-muted fw-normal">(optional)</span></label>
              <input class="form-control" id="qp-email" name="email" type="email"
                     placeholder="you@example.com" autocomplete="email" inputmode="email">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="qp-area">Approx. area (sqft)</label>
              <input class="form-control" id="qp-area" name="area" type="text"
                     inputmode="numeric" placeholder="e.g. 120">
            </div>
            <div class="col-12">
              <label class="form-label" for="qp-msg">Message</label>
              <textarea class="form-control" id="qp-msg" name="message" rows="4" placeholder="Tell us about the space, timeline, location..."></textarea>
            </div>
            <div class="col-12 d-flex flex-wrap align-items-center gap-2">
              <button class="btn btn-gd-primary btn-lg" type="submit">
                Send Enquiry <i class="bi bi-send ms-1"></i>
              </button>
              <span class="small text-success d-none" data-gd-success>
                <i class="bi bi-check-circle-fill"></i> Saved! We'll reach out shortly.
              </span>
              <span class="small text-danger d-none" data-gd-error>
                <i class="bi bi-exclamation-triangle-fill"></i> Could not save — please try again or call us.
              </span>
            </div>
          </div>
        </form>
      </div>

    </div>
  </div>
</section>

<!-- ==================== INSTALL VIDEOS ==================== -->
<?php
$videos      = $product['videos'] ?? [];
$videosTitle = 'See it <span>installed</span>';
$videosLede  = 'Real ' . $product['name'] . ' installs, filmed on our Bangalore sites.';
include __DIR__ . '/../includes/video-showcase.php';
?>

<!-- ============== COMPLETED INSTALLATIONS GALLERY ============== -->
<?php if (!empty($product['installations'])): ?>
<section class="py-5 gd-bg-soft">
  <div class="container">
    <div class="text-center mb-4 gd-reveal">
      <span class="gd-eyebrow">Real Projects</span>
      <h2 class="gd-section-title">Completed <span>Installations</span></h2>
      <p class="text-muted">A few spaces we've transformed using <?= e($product['name']) ?>.</p>
    </div>

    <div class="row g-3 gd-install-gallery">
      <?php foreach ($product['installations'] as $img): ?>
        <div class="col-6 col-md-4 gd-reveal">
          <img src="<?= e(gd_img($img, 'installations')) ?>" alt="<?= e($product['name']) ?> installation" loading="lazy">
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ============================ REVIEWS ============================ -->
<div id="reviews"></div>
<?php
$reviews_slug  = $product['slug'];
$reviews_title = 'Reviews for ' . $product['name'];
$reviews_limit = 6;
include __DIR__ . '/../includes/reviews.php';
?>

<!-- ===================== RELATED PRODUCTS ===================== -->
<section class="py-5">
  <div class="container">
    <div class="text-center mb-4 gd-reveal">
      <h2 class="gd-section-title">You may also <span>like</span></h2>
    </div>
    <div class="row g-4">
      <?php
        $shown = 0;
        foreach ($DATA['products'] as $rel) {
            if ($rel['slug'] === $product['slug']) continue;
            if ($rel['category'] !== $product['category']) continue;
            $product_save = $product;
            $product = $rel;
            echo '<div class="col-12 col-sm-6 col-lg-4 gd-reveal">';
            include __DIR__ . '/../includes/product-card.php';
            echo '</div>';
            $product = $product_save;
            if (++$shown >= 3) break;
        }
        if ($shown === 0) echo '<p class="text-center text-muted">More coming soon.</p>';
      ?>
    </div>
  </div>
</section>

<!-- =================== JSON-LD: Product =================== -->
<?php
// price_label is a free-form display string ("Starting ₹85 / sqft",
// "Custom quote") not a fixed number, so pull out a leading numeric value
// to report as a starting price — and simply omit the offer entirely
// when there isn't one, rather than publish a made-up price.
// A product with show_price off is quote-only, so publish no offer at all —
// Google would otherwise surface a price in search that the page itself
// deliberately withholds.
$priceNum = null;
if (gd_show_price($product)
    && preg_match('/[\d,]+(?:\.\d+)?/', $product['price_label'] ?? '', $pm)) {
    $priceNum = (float)str_replace(',', '', $pm[0]);
}
?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": <?= json_encode($product['name'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
  "description": <?= json_encode($product['short'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
  "image": [<?php foreach ($product['images'] as $i => $img): ?><?= $i ? ',' : '' ?><?= json_encode(gd_img($img, 'products'), JSON_UNESCAPED_SLASHES) ?><?php endforeach; ?>],
  "sku": <?= json_encode($product['slug']) ?>,
  "brand": { "@type": "Brand", "name": <?= json_encode($DATA['site']['name']) ?> }
  <?php if ($priceNum !== null): ?>,
  "offers": {
    "@type": "Offer",
    "priceCurrency": "INR",
    "price": <?= json_encode($priceNum) ?>,
    "availability": "https://schema.org/InStock",
    "url": <?= json_encode($DATA['site']['base_url'] . '/product/' . $product['slug']) ?>
  }
  <?php endif; ?>
  <?php if ($rating['count'] > 0): ?>,
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": <?= json_encode($rating['avg']) ?>,
    "reviewCount": <?= json_encode($rating['count']) ?>
  }
  <?php endif; ?>
}
</script>
