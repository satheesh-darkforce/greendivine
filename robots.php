<?php
/**
 * robots.txt — generated dynamically (not a static file) purely so the
 * Sitemap: line can be a correct, fully-qualified URL on whatever host
 * this is actually running on, matching the rest of the site's "never
 * hardcode the domain" approach (see GD_SITE_URL in data.php).
 */
require __DIR__ . '/data.php';

header('Content-Type: text/plain; charset=UTF-8');

echo "User-agent: *\n";
echo "Disallow: /admin/\n";
echo "\n";
echo 'Sitemap: ' . $DATA['site']['base_url'] . "/sitemap.xml\n";
