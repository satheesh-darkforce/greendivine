<?php
/**
 * XML sitemap — generated fresh on every request from the live $DATA, so
 * it's never stale after an admin adds/removes a product, category or
 * blog post. robots.txt points crawlers at /sitemap.xml (rewritten here
 * by .htaccess).
 */
require __DIR__ . '/data.php';

header('Content-Type: application/xml; charset=UTF-8');

$base = $DATA['site']['base_url'];
$today = date('Y-m-d');

/** @var list<array{loc:string,changefreq:string,priority:string}> $urls */
$urls = [
    ['loc' => $base . '/',         'changefreq' => 'weekly',  'priority' => '1.0'],
    ['loc' => $base . '/products', 'changefreq' => 'weekly',  'priority' => '0.9'],
    ['loc' => $base . '/about',    'changefreq' => 'monthly', 'priority' => '0.6'],
    ['loc' => $base . '/services', 'changefreq' => 'monthly', 'priority' => '0.7'],
    ['loc' => $base . '/blogs',    'changefreq' => 'weekly',  'priority' => '0.7'],
    ['loc' => $base . '/contact',  'changefreq' => 'monthly', 'priority' => '0.6'],
    ['loc' => $base . '/privacy-policy', 'changefreq' => 'yearly', 'priority' => '0.2'],
    ['loc' => $base . '/terms',          'changefreq' => 'yearly', 'priority' => '0.2'],
];

foreach ($DATA['categories'] as $c) {
    if (gd_find_landing($c['slug'])) {
        $urls[] = ['loc' => $base . '/' . $c['slug'], 'changefreq' => 'weekly', 'priority' => '0.8'];
    }
}
foreach ($DATA['products'] as $p) {
    $urls[] = ['loc' => $base . '/product/' . $p['slug'], 'changefreq' => 'weekly', 'priority' => '0.8'];
}
foreach ($DATA['blogs'] as $b) {
    $urls[] = ['loc' => $base . '/blog/' . $b['slug'], 'changefreq' => 'monthly', 'priority' => '0.6'];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
    echo "  <url>\n";
    echo '    <loc>' . e($u['loc']) . "</loc>\n";
    echo '    <lastmod>' . $today . "</lastmod>\n";
    echo '    <changefreq>' . $u['changefreq'] . "</changefreq>\n";
    echo '    <priority>' . $u['priority'] . "</priority>\n";
    echo "  </url>\n";
}
echo '</urlset>' . "\n";
