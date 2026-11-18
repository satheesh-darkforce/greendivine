<?php
/**
 * Reusable product card with badge + rating stars.
 * Expects: $product (single item from $DATA['products']).
 */
if (!isset($product) || !is_array($product)) return;

$cat    = gd_find_category($product['category']);
$img    = $product['images'][0] ?? '';
$url    = gd_url('/product/' . $product['slug']);
$badge  = $product['badge'] ?? ($product['is_deal'] ?? false ? "Today's Deal" : '');
$rating = gd_rating($product['slug']);
?>
<article class="gd-product-card h-100" data-category="<?= e($product['category']) ?>">
  <a href="<?= e($url) ?>" class="gd-product-media" aria-label="<?= e($product['name']) ?>">
    <img src="<?= e(gd_img($img, 'products')) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
    <?php if ($badge): ?>
      <span class="gd-badge-deal"><?= e($badge) ?></span>
    <?php endif; ?>
  </a>

  <div class="gd-product-body">
    <?php if ($cat): ?>
      <span class="gd-product-cat"><?= e($cat['name']) ?></span>
    <?php endif; ?>

    <h3 class="gd-product-title">
      <a href="<?= e($url) ?>"><?= e($product['name']) ?></a>
    </h3>

    <?php if ($rating['count'] > 0): ?>
      <div class="gd-rating-row small">
        <span class="gd-stars" aria-label="<?= e($rating['avg']) ?> out of 5">
          <?= gd_stars($rating['avg']) ?>
        </span>
        <span class="text-muted ms-1">(<?= e((string)$rating['count']) ?>)</span>
      </div>
    <?php endif; ?>

    <p class="gd-product-short"><?= e($product['short']) ?></p>

    <div class="gd-product-foot">
      <?php if (gd_show_price($product)): ?>
        <span class="gd-product-price">
          <?= e($product['price_label']) ?>
          <?php if (!empty($product['price_was'])): ?>
            <small class="gd-price-was"><?= e($product['price_was']) ?></small>
          <?php endif; ?>
        </span>
      <?php else: ?>
        <?php /* Quote-only product: the price slot becomes the primary CTA
                 rather than collapsing, so the card keeps its two-column
                 footer and the row of cards stays aligned. Goes straight to
                 WhatsApp with the product name pre-filled — fewer steps than
                 sending them to a form. */ ?>
        <a href="<?= e(gd_whatsapp_link($product, "I'd like a quote for")) ?>"
           class="btn btn-gd-primary btn-sm gd-product-quote"
           target="_blank" rel="noopener"
           aria-label="Get a quote for <?= e($product['name']) ?> on WhatsApp">
          <i class="bi bi-whatsapp"></i> Get Quote
        </a>
      <?php endif; ?>
      <a href="<?= e($url) ?>" class="btn btn-gd-outline btn-sm">
        View <i class="bi bi-arrow-right"></i>
      </a>
    </div>
  </div>
</article>
