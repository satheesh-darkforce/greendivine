<?php
require __DIR__ . '/inc/guard.php';

$adminTitle  = 'Dashboard';
$adminActive = 'dashboard';

$stats = [
    ['label' => 'Products',        'count' => count($DATA['products']),        'href' => 'modules/products.php',   'icon' => 'bi-box-seam-fill'],
    ['label' => 'Categories',      'count' => count($DATA['categories']),      'href' => 'modules/categories.php', 'icon' => 'bi-grid-3x3-gap-fill'],
    ['label' => 'Blog Posts',      'count' => count($DATA['blogs']),           'href' => 'modules/blogs.php',      'icon' => 'bi-journal-richtext'],
    ['label' => 'Reviews',         'count' => count($DATA['reviews']),         'href' => 'modules/reviews.php',    'icon' => 'bi-star-fill'],
    ['label' => 'Services',        'count' => count($DATA['services']),        'href' => 'modules/services.php',   'icon' => 'bi-tools'],
    ['label' => 'Team Members',    'count' => count($DATA['team']),            'href' => 'modules/team.php',       'icon' => 'bi-people-fill'],
];

$quickLinks = [
    ['label' => 'Site Settings',    'icon' => 'bi-gear-fill',       'href' => 'modules/settings.php'],
    ['label' => 'Products',         'icon' => 'bi-box-seam-fill',   'href' => 'modules/products.php'],
    ['label' => 'Categories',       'icon' => 'bi-grid-3x3-gap-fill','href' => 'modules/categories.php'],
    ['label' => 'Blogs',            'icon' => 'bi-journal-richtext','href' => 'modules/blogs.php'],
    ['label' => 'FAQs',             'icon' => 'bi-question-circle-fill', 'href' => 'modules/faqs.php'],
    ['label' => 'Page Copy',        'icon' => 'bi-file-text-fill',  'href' => 'modules/page-content.php'],
];

// "Needs attention" — cheap, useful checks computed fresh on every load
// (no stored state to keep in sync). Each one links straight to the fix.
$attention = [];
if (empty($DATA['config']['google_sheet_endpoint'])) {
    $attention[] = ['text' => 'No Google Sheet webhook configured yet — quote/contact forms fall back to opening an email draft, which many visitors won\'t follow through on.', 'href' => 'modules/settings.php', 'level' => 'danger'];
}
foreach ($DATA['categories'] as $c) {
    if (!gd_find_landing($c['slug'])) {
        $attention[] = ['text' => '"' . $c['name'] . '" has no landing page yet.', 'href' => 'modules/landing-page-edit.php?slug=' . $c['slug'], 'level' => 'warning'];
    }
}
foreach ($DATA['products'] as $p) {
    if (count($p['images'] ?? []) < 2) {
        $attention[] = ['text' => '"' . $p['name'] . '" has only ' . count($p['images'] ?? []) . ' image' . (count($p['images'] ?? []) === 1 ? '' : 's') . ' — most product cards look better with 2-3.', 'href' => 'modules/products.php', 'level' => 'info'];
    }
}
if (empty($DATA['config']['google_analytics_id']) && empty($DATA['config']['meta_pixel_id'])) {
    $attention[] = ['text' => 'No analytics configured — you have no visibility into site traffic yet.', 'href' => 'modules/settings.php', 'level' => 'info'];
}

require __DIR__ . '/inc/layout-top.php';
?>

<div class="gd-admin-toolbar">
  <div>
    <h1>Welcome back<?= $admin ? ', ' . e($admin['display_name']) : '' ?></h1>
    <p>A quick snapshot of your site's content.</p>
  </div>
</div>

<?php if ($attention): ?>
<div class="gd-admin-card">
  <h2><i class="bi bi-exclamation-triangle-fill text-warning"></i> Needs attention <span class="badge text-bg-secondary"><?= count($attention) ?></span></h2>
  <ul class="list-unstyled mb-0">
    <?php foreach ($attention as $a): ?>
      <li class="mb-2">
        <a href="<?= e(gd_admin_url('/' . $a['href'])) ?>" class="text-decoration-none">
          <i class="bi <?= $a['level'] === 'danger' ? 'bi-x-circle-fill text-danger' : ($a['level'] === 'warning' ? 'bi-exclamation-circle-fill text-warning' : 'bi-info-circle-fill text-secondary') ?> me-2"></i><?= e($a['text']) ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<div class="gd-admin-stat-grid">
  <?php foreach ($stats as $s): ?>
    <a href="<?= e(gd_admin_url('/' . $s['href'])) ?>" class="gd-admin-stat-card text-decoration-none">
      <div class="gd-admin-stat-num"><i class="bi <?= e($s['icon']) ?> me-1"></i><?= (int)$s['count'] ?></div>
      <div class="gd-admin-stat-label"><?= e($s['label']) ?></div>
    </a>
  <?php endforeach; ?>
</div>

<div class="gd-admin-card">
  <h2>Quick links</h2>
  <div class="gd-admin-quicklinks">
    <?php foreach ($quickLinks as $q): ?>
      <a href="<?= e(gd_admin_url('/' . $q['href'])) ?>" class="gd-admin-quicklink">
        <i class="bi <?= e($q['icon']) ?>"></i> <?= e($q['label']) ?>
      </a>
    <?php endforeach; ?>
  </div>
</div>

<div class="gd-admin-card">
  <h2>About this console</h2>
  <p class="text-muted mb-0">
    Every save here rewrites the matching file under <code>data/content/</code> (or
    <code>data/landing/</code> for category landing pages) instantly — the public
    site reads the same files on every request, so changes go live immediately,
    with no build step and no cache to clear. Every save also keeps a timestamped
    backup, so any change can be undone.
  </p>
</div>

<?php require __DIR__ . '/inc/layout-bottom.php'; ?>
