<?php
/**
 * =====================================================================
 * Green Divine — Front Controller
 * ---------------------------------------------------------------------
 * .htaccess rewrites every request to: index.php?route=<slug>[&...]
 * This file:
 *   1. Loads data + router
 *   2. Resolves the route -> template
 *   3. Renders: header -> page body -> FAQ carousel -> floating actions -> footer
 * =====================================================================
 */
require_once __DIR__ . '/data.php';
require_once __DIR__ . '/includes/router.php';

$route    = $_GET['route'] ?? 'home';
$resolved = gd_resolve_route($route);

$pageMeta = $resolved['meta'];
$active   = $resolved['active'];
$template = $resolved['template'];

require_once __DIR__ . '/includes/header.php';

if (is_file($template)) {
    require $template;
} else {
    require __DIR__ . '/pages/404.php';
}

require_once __DIR__ . '/includes/faq-carousel.php';
require_once __DIR__ . '/includes/floating-actions.php';
require_once __DIR__ . '/includes/footer.php';
