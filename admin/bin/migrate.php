<?php
/**
 * One-time CLI migration: reads the CURRENT data.php (still array literals
 * at the point this is run) and writes each $DATA[...] domain out to
 * data/content/<key>.php via Store::save(). Not web-exposed — refuses to
 * run under anything but the CLI SAPI.
 *
 * Usage (run once, from the project root):
 *   php admin/bin/migrate.php
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only.'); }

require __DIR__ . '/../inc/store.php';
require __DIR__ . '/../../data.php'; // builds $DATA from the current literals

$keys = [
    'config', 'festival', 'quote_popup', 'hero', 'categories', 'products',
    'stats', 'clients', 'faqs', 'page_faqs', 'services', 'service_details',
    'why_us', 'process', 'team', 'certifications', 'reviews', 'blogs', 'nav',
];

// 'site' is special: 'domain' and 'base_url' are request-derived plumbing
// (computed fresh on every request from GD_SITE_URL), never persisted
// content — otherwise the CLI run's fallback URL would get baked in and
// break local/dev hosting after migration.
$site = $DATA['site'];
unset($site['domain'], $site['base_url']);
echo (Store::save('site', $site) ? "OK    " : "FAIL  ") . "site\n";

foreach ($keys as $key) {
    if (!array_key_exists($key, $DATA)) { echo "SKIP  $key (not found in \$DATA)\n"; continue; }
    echo (Store::save($key, $DATA[$key]) ? "OK    " : "FAIL  ") . $key . "\n";
}

echo "\nDone — inspect data/content/*.php, then point data.php at these files.\n";
