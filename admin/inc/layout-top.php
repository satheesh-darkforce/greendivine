<?php
/**
 * Shared admin shell — <head>, sidebar, topbar, flash banners.
 * Expects (set by the including module before this file runs):
 *   $adminTitle  (string) page heading shown in the topbar + <title>
 *   $adminActive (string) sidebar key to highlight, e.g. "products"
 * Closed by layout-bottom.php.
 */
$adminTitle  = $adminTitle  ?? 'Admin';
$adminActive = $adminActive ?? '';
$admin       = current_admin();

$navGroups = [
    'Site'       => [
        ['key' => 'settings',     'icon' => 'bi-gear-fill',        'label' => 'Settings',        'href' => 'modules/settings.php'],
        ['key' => 'nav',          'icon' => 'bi-list',             'label' => 'Navigation Menu',  'href' => 'modules/nav.php'],
        ['key' => 'quote-popup',  'icon' => 'bi-chat-square-text', 'label' => 'Quote Popup',      'href' => 'modules/quote-popup.php'],
        ['key' => 'festival',     'icon' => 'bi-gift-fill',        'label' => 'Festival Banner',  'href' => 'modules/festival.php'],
        ['key' => 'page-content', 'icon' => 'bi-file-text-fill',   'label' => 'Page Copy',        'href' => 'modules/page-content.php'],
    ],
    'Catalogue'  => [
        ['key' => 'categories',     'icon' => 'bi-grid-3x3-gap-fill', 'label' => 'Categories',     'href' => 'modules/categories.php'],
        ['key' => 'products',       'icon' => 'bi-box-seam-fill',     'label' => 'Products',       'href' => 'modules/products.php'],
        ['key' => 'landing-pages',  'icon' => 'bi-window-stack',      'label' => 'Landing Pages',  'href' => 'modules/landing-pages.php'],
    ],
    'Content'    => [
        ['key' => 'services',        'icon' => 'bi-tools',            'label' => 'Services',        'href' => 'modules/services.php'],
        ['key' => 'process-steps',   'icon' => 'bi-signpost-split-fill','label' => 'Process Steps',  'href' => 'modules/process-steps.php'],
        ['key' => 'why-us',          'icon' => 'bi-award-fill',       'label' => 'Why Us',          'href' => 'modules/why-us.php'],
        ['key' => 'team',            'icon' => 'bi-people-fill',      'label' => 'Team',            'href' => 'modules/team.php'],
        ['key' => 'stats',           'icon' => 'bi-graph-up',         'label' => 'Stats',           'href' => 'modules/stats.php'],
        ['key' => 'clients',         'icon' => 'bi-building',         'label' => 'Clients',         'href' => 'modules/clients.php'],
        ['key' => 'certifications',  'icon' => 'bi-patch-check-fill', 'label' => 'Certifications',  'href' => 'modules/certifications.php'],
    ],
    'Blog & FAQ' => [
        ['key' => 'blogs',   'icon' => 'bi-journal-richtext', 'label' => 'Blogs',   'href' => 'modules/blogs.php'],
        ['key' => 'faqs',    'icon' => 'bi-question-circle-fill', 'label' => 'FAQs',    'href' => 'modules/faqs.php'],
        ['key' => 'reviews', 'icon' => 'bi-star-fill',        'label' => 'Reviews', 'href' => 'modules/reviews.php'],
    ],
    'Account'    => [
        ['key' => 'users',        'icon' => 'bi-person-lock',   'label' => 'Change Password',   'href' => 'modules/users.php'],
        ['key' => 'backups',      'icon' => 'bi-clock-history',  'label' => 'Backups & Restore',  'href' => 'modules/backups.php'],
        ['key' => 'activity-log', 'icon' => 'bi-list-check',     'label' => 'Activity Log',       'href' => 'modules/activity-log.php'],
    ],
];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= e($adminTitle) ?> · Green Divine Admin</title>
<link rel="stylesheet" href="<?= e(gd_asset('/assets/vendor/bootstrap-5.3.3/bootstrap.min.css')) ?>">
<link rel="stylesheet" href="<?= e(gd_asset('/assets/vendor/bootstrap-icons-1.11.3/bootstrap-icons.css')) ?>">
<link rel="stylesheet" href="<?= e(gd_asset('/admin/assets/css/admin-core.css')) ?>">
</head>
<body class="gd-admin">
<div class="gd-admin-shell">

  <div class="gd-admin-sidebar-backdrop" data-gd-sidebar-backdrop></div>
  <aside class="gd-admin-sidebar">
    <div class="gd-admin-sidebar-brand">
      <img src="<?= e(gd_img($DATA['config']['logo'])) ?>" alt="" width="38" height="30">
      <span>Green Divine<br><small style="font-weight:400;opacity:.7;font-size:.7rem;">Admin Console</small></span>
    </div>
    <nav class="gd-admin-nav">
      <div class="gd-admin-nav-group">
        <a href="<?= e(gd_admin_url('/index.php')) ?>" class="<?= $adminActive === 'dashboard' ? 'active' : '' ?>">
          <i class="bi bi-speedometer2"></i> Dashboard
        </a>
      </div>
      <?php foreach ($navGroups as $group => $items): ?>
        <div class="gd-admin-nav-group">
          <span class="gd-admin-nav-label"><?= e($group) ?></span>
          <?php foreach ($items as $item): ?>
            <a href="<?= e(gd_admin_url('/' . $item['href'])) ?>" class="<?= $adminActive === $item['key'] ? 'active' : '' ?>">
              <i class="bi <?= e($item['icon']) ?>"></i> <?= e($item['label']) ?>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </nav>
  </aside>

  <div class="gd-admin-main">
    <header class="gd-admin-topbar">
      <div class="d-flex align-items-center gap-2">
        <button type="button" class="gd-admin-sidebar-toggle" data-gd-sidebar-toggle aria-label="Toggle menu">
          <i class="bi bi-list"></i>
        </button>
        <h1 class="gd-admin-topbar-title d-none d-sm-block"><?= e($adminTitle) ?></h1>
      </div>
      <div class="gd-admin-topbar-right">
        <a href="<?= e(gd_url('/')) ?>" target="_blank" rel="noopener" class="text-muted">
          View site <i class="bi bi-box-arrow-up-right"></i>
        </a>
        <span class="text-muted d-none d-md-inline"><?= e($admin['display_name'] ?? 'Admin') ?></span>
        <a href="<?= e(gd_admin_url('/logout.php')) ?>" class="btn btn-sm btn-outline-secondary">
          <i class="bi bi-box-arrow-right"></i> Logout
        </a>
      </div>
    </header>

    <main class="gd-admin-content">
      <?php foreach (flash_pull() as $f): ?>
        <div class="gd-admin-flash gd-admin-flash-<?= e($f['type']) ?>">
          <i class="bi <?= $f['type'] === 'error' ? 'bi-exclamation-triangle-fill' : 'bi-check-circle-fill' ?>"></i>
          <span><?= e($f['message']) ?></span>
        </div>
      <?php endforeach; ?>
