<?php
/**
 * Reusable customer reviews section (carousel).
 *
 * Optional context (set BEFORE including):
 *   $reviews_slug  = '<product-slug>'  // filter to that product
 *   $reviews_limit = 6                  // cap visible reviews
 *   $reviews_title = 'What clients say' // override heading
 *   $reviews_list  = [ ...reviews ]     // ready-made list (e.g. a category page's
 *                                       // own reviews first); overrides $reviews_slug
 */
$slug   = isset($reviews_slug)  ? (string)$reviews_slug  : '';
$limit  = isset($reviews_limit) ? (int)$reviews_limit    : 6;
$title  = isset($reviews_title) ? (string)$reviews_title : 'Real Reviews From Real Customers';

if (isset($reviews_list) && is_array($reviews_list)) {
    $list = array_values($reviews_list);
    // rating summary from exactly the reviews shown
    $sum  = array_sum(array_map(fn($r) => (int)($r['rating'] ?? 0), $list));
    $agg  = ['avg' => $list ? round($sum / count($list), 1) : 0, 'count' => count($list)];
} else {
    $list = $slug !== '' ? gd_product_reviews($slug) : ($DATA['reviews'] ?? []);
    $agg  = gd_rating($slug);
}
if (empty($list)) return;
$list   = array_slice($list, 0, $limit);

$cid    = 'gdRev_' . substr(md5(($slug ?: 'all') . '|' . $title), 0, 8);
?>
<section class="gd-reviews-section py-5" aria-labelledby="<?= e($cid) ?>-title">
  <div class="container">
    <div class="text-center mb-5 gd-reveal">
      <span class="gd-eyebrow">Reviews</span>
      <h2 id="<?= e($cid) ?>-title" class="gd-section-title">
        <?= e($title) ?>
      </h2>
      <?php if ($agg['count'] > 0): ?>
        <div class="gd-rating-row justify-content-center mt-2">
          <span class="gd-stars text-warning fs-5"><?= gd_stars($agg['avg']) ?></span>
          <strong class="ms-2"><?= e((string)$agg['avg']) ?> / 5</strong>
          <span class="text-muted ms-2 small">(<?= e((string)$agg['count']) ?> verified reviews)</span>
        </div>
      <?php endif; ?>
    </div>

    <div class="gd-reviews-carousel gd-reveal" data-gd-reviews id="<?= e($cid) ?>">
      <button class="gd-rev-nav gd-rev-prev" type="button" aria-label="Previous reviews" data-gd-rev-prev>
        <i class="bi bi-chevron-left"></i>
      </button>

      <div class="gd-reviews-track" data-gd-rev-track>
        <?php foreach ($list as $r): ?>
          <article class="gd-review-card">
            <i class="bi bi-quote gd-review-quote" aria-hidden="true"></i>
            <div class="d-flex align-items-center mb-3 gap-3">
              <?php if (!empty($r['avatar'])): ?>
                <img src="<?= e(gd_img($r['avatar'])) ?>" alt="<?= e($r['name']) ?>" class="gd-review-avatar" loading="lazy" width="52" height="52"
                     onerror="this.style.display='none'; this.nextElementSibling.style.removeProperty('display');">
                <span class="gd-review-avatar gd-review-avatar-initials" style="display:none;" aria-hidden="true"><?= e(gd_initials($r['name'])) ?></span>
              <?php else: ?>
                <span class="gd-review-avatar gd-review-avatar-initials" aria-hidden="true"><?= e(gd_initials($r['name'])) ?></span>
              <?php endif; ?>
              <div class="flex-grow-1">
                <div class="fw-semibold"><?= e($r['name']) ?></div>
                <div class="small text-muted"><?= e($r['location'] ?? '') ?></div>
              </div>
              <div class="gd-stars text-warning small"><?= gd_stars((float)($r['rating'] ?? 5)) ?></div>
            </div>
            <p class="gd-review-body mb-3">&ldquo;<?= e($r['body']) ?>&rdquo;</p>
            <div class="d-flex justify-content-between align-items-center small text-muted mt-auto">
              <span><?= e(date('M Y', strtotime($r['date'] ?? 'today'))) ?></span>
              <?php if (!empty($r['product'])): $p = gd_find_product($r['product']); if ($p): ?>
                <a href="<?= e(gd_url('/product/' . $p['slug'])) ?>" class="link-secondary text-decoration-none">
                  <i class="bi bi-bag-check"></i> <?= e($p['name']) ?>
                </a>
              <?php endif; endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <button class="gd-rev-nav gd-rev-next" type="button" aria-label="Next reviews" data-gd-rev-next>
        <i class="bi bi-chevron-right"></i>
      </button>

      <div class="gd-rev-dots" data-gd-rev-dots aria-hidden="true"></div>
    </div>
  </div>
</section>
