<?php
/**
 * Products listing page.
 * Supports an optional URL category filter: /products/<category-slug>
 */
$filter = $GLOBALS['filter'] ?? '';
$active_cat = $filter ?: 'all';
$active_cat_obj = $filter ? gd_find_category($filter) : null;
?>

<!-- ============================ PAGE HEAD ============================ -->
<section class="gd-page-head" style="background-image: radial-gradient(800px 420px at 92% -20%, rgba(199,154,58,.15), transparent 60%), linear-gradient(180deg, rgba(255,255,255,.90) 0%, rgba(250,247,240,.94) 100%), url('<?= e(gd_img('vg-8.jpg', 'categories')) ?>'); background-size: cover; background-position: center;">
  <span class="gd-hero-blob gd-hero-blob-1" aria-hidden="true"></span>
  <span class="gd-hero-blob gd-hero-blob-2" aria-hidden="true"></span>
  <div class="container position-relative text-center">
    <span class="gd-eyebrow justify-content-center">Catalogue</span>
    <h1 class="gd-section-title">
      <?php if ($active_cat_obj): ?>
        Our <span><?= e($active_cat_obj['name']) ?></span>
      <?php else: ?>
        Our <span>Products</span>
      <?php endif; ?>
    </h1>
    <p class="text-muted mb-0 mt-2 mx-auto" style="max-width:560px;">
      Hand-picked landscaping solutions — UV-stable, warrantied and built to last in Bangalore's climate.
    </p>
  </div>
</section>

<section class="py-5">
  <div class="container">

    <!-- Search -->
    <div class="gd-product-search mb-3 mx-auto">
      <i class="bi bi-search"></i>
      <input type="search" class="form-control" data-product-search
             placeholder="Search products…" aria-label="Search products">
    </div>

    <!-- Filter pills (horizontal scroll on mobile, centered wrap on desktop) -->
    <div class="gd-filter-wrap mb-4">
      <div class="gd-filter-bar" data-filter-bar role="tablist" aria-label="Filter products by category">
        <button class="gd-pill <?= $active_cat === 'all' ? 'active' : '' ?>" data-filter="all" role="tab" aria-selected="<?= $active_cat === 'all' ? 'true' : 'false' ?>">
          <i class="bi bi-grid"></i><span>All</span>
        </button>
        <?php foreach ($DATA['categories'] as $c): ?>
          <button class="gd-pill <?= $active_cat === $c['slug'] ? 'active' : '' ?>" data-filter="<?= e($c['slug']) ?>" role="tab" aria-selected="<?= $active_cat === $c['slug'] ? 'true' : 'false' ?>">
            <i class="bi <?= e($c['icon']) ?>"></i><span><?= e($c['name']) ?></span>
          </button>
        <?php endforeach; ?>
      </div>
    </div>

    <?php
      // Arriving via /products/<slug>: every product is still rendered so the
      // pills can switch back to "All" or another category, but the others
      // start hidden — no flash of the full catalogue before main.js runs.
      $visibleCount = 0;
      foreach ($DATA['products'] as $p) if (!$filter || $p['category'] === $filter) $visibleCount++;
    ?>
    <!-- Shown when a search/filter combination matches nothing -->
    <p class="text-center text-muted<?= $visibleCount ? ' d-none' : '' ?>" data-product-empty>No products found here yet — try another category, or <a href="<?= e(gd_url('/contact')) ?>">ask us for a custom quote</a>.</p>

    <!-- Grid -->
    <div class="row g-4">
      <?php foreach ($DATA['products'] as $product): ?>
        <div class="col-12 col-sm-6 col-lg-4 gd-reveal"<?= ($filter && $product['category'] !== $filter) ? ' style="display:none"' : '' ?>>
          <?php include __DIR__ . '/../includes/product-card.php'; ?>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>
