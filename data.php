<?php
/**
 * =====================================================================
 * Green Divine — Master data file
 * ---------------------------------------------------------------------
 * NO database. All site content lives as PHP arrays under data/content/
 * (one file per domain) and data/landing/ (one file per category landing
 * page) — this file just loads them into $DATA and keeps every helper
 * function. Content is managed through the /admin console; every save
 * there rewrites the matching data/content/<key>.php file in place, so
 * this loader never needs to change when content changes.
 *
 * Images use absolute CDN URLs (Unsplash / Pravatar / placehold.co) so
 * the site renders without local image assets. Swap any URL for a local
 * file under /assets/images/<bucket>/ when you have one ready — the
 * gd_img() helper passes absolute URLs through and prefixes bare names.
 * =====================================================================
 */

/* ---------------------------------------------------------------------
 * Error visibility — fail SAFE by default.
 * ---------------------------------------------------------------------
 * A PHP warning/notice with display_errors on leaks full server file
 * paths and internal logic to anyone who can trigger one (see the admin
 * console's security audit — this exact leak was observed during
 * testing). Hosting defaults vary and aren't under this app's control,
 * so pin the behaviour here instead of trusting php.ini: show errors
 * only when the request is unambiguously local/dev; otherwise hide them
 * from output and log them to a file instead. Override by defining
 * GD_ENV ('development' or 'production') before this file loads, or via
 * an environment variable of the same name, if auto-detection ever
 * guesses wrong for a particular host.
 * ------------------------------------------------------------------- */
if (!defined('GD_ENV')) {
    $envOverride = getenv('GD_ENV');
    if ($envOverride === 'development' || $envOverride === 'production') {
        define('GD_ENV', $envOverride);
    } else {
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
        $isLocal = PHP_SAPI === 'cli'
            || in_array($host, ['localhost', '127.0.0.1', '::1'], true)
            || (bool)preg_match('/\.(localhost|local|test)(:\d+)?$/i', $host)
            || str_starts_with($host, '127.0.0.1:')
            || str_starts_with($host, 'localhost:');
        define('GD_ENV', $isLocal ? 'development' : 'production');
    }
}
if (GD_ENV === 'production') {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
} else {
    ini_set('display_errors', '1');
}
ini_set('log_errors', '1');
if (!ini_get('error_log')) {
    ini_set('error_log', __DIR__ . '/data/security/php-error.log');
}

/* ---------------------------------------------------------------------
 * Base-URL auto-detection (subfolder-safe + host-agnostic)
 * ---------------------------------------------------------------------
 * Nothing about the URL is hardcoded. Both the path prefix (GD_BASE) and
 * the absolute origin (GD_ORIGIN) are derived from the actual request, so
 * the site works unchanged at:
 *   - localhost subfolder   →  http://localhost/claude/advanced/greendivine-1
 *   - a Hostinger temp URL  →  https://yourname.hostingersite.com
 *   - the live domain root  →  https://greendivine.in   (public_html)
 *
 * To FORCE a canonical domain (recommended once the live domain is live),
 * set an environment variable GD_SITE_URL, or define('GD_SITE_URL', ...)
 * before this file loads. Leave it unset for fully automatic behaviour.
 * ------------------------------------------------------------------- */

// Path prefix: '' at document root, '/sub/folder' when in a subdirectory.
// Derived from WHERE THIS FILE LIVES relative to the web root, not from
// which script is currently executing — that keeps it correct whether the
// request came in through the root index.php or through admin/<anything>,
// which sits one (or more) folders deeper than the site root.
if (!defined('GD_BASE')) {
    $docRoot  = str_replace('\\', '/', rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/'));
    $siteRoot = str_replace('\\', '/', rtrim(__DIR__, '/'));
    if ($docRoot !== '' && str_starts_with($siteRoot, $docRoot)) {
        $rel = substr($siteRoot, strlen($docRoot));
        define('GD_BASE', ($rel === '' || $rel === '/') ? '' : $rel);
    } else {
        // Fallback (e.g. DOCUMENT_ROOT unavailable): only correct when the
        // entry script is at the site root, as with the public front controller.
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $dir    = str_replace('\\', '/', dirname($script));
        define('GD_BASE', ($dir === '/' || $dir === '.') ? '' : rtrim($dir, '/'));
    }
}

// Absolute origin (scheme://host) of the current request — proxy-aware.
if (!defined('GD_ORIGIN')) {
    $fwdProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
    $https = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
          || ((int)($_SERVER['SERVER_PORT'] ?? 0) === 443)
          || (strtolower($fwdProto) === 'https');
    $host  = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
    define('GD_ORIGIN', ($https ? 'https' : 'http') . '://' . $host);
}

// Full absolute base URL used for canonical / OG / JSON-LD / share links.
// Priority: explicit override (constant or env) → auto-detected request URL.
if (!defined('GD_SITE_URL')) {
    $override = getenv('GD_SITE_URL');
    if ($override === false || $override === '') {
        // CLI has no request context — fall back to the brand domain.
        $override = (PHP_SAPI === 'cli') ? 'https://greendivine.in' : GD_ORIGIN . GD_BASE;
    }
    define('GD_SITE_URL', rtrim($override, '/'));
}

function gd_url(string $path = '/'): string {
    if (preg_match('#^(?:https?:)?//|^data:#i', $path)) return $path;
    return GD_BASE . '/' . ltrim($path, '/');
}

function gd_img(string $name, string $bucket = ''): string {
    if (preg_match('#^(?:https?:)?//|^data:#i', $name)) return $name;
    $bucket = trim($bucket, '/');
    return gd_url('/assets/images/' . ($bucket ? $bucket . '/' : '') . ltrim($name, '/'));
}

/* ---------------------------------------------------------------------
 * Versioned CSS/JS URLs + deploy-integrity guard
 * ---------------------------------------------------------------------
 * Production once served a leftover Bootstrap 4 bootstrap.min.css that an
 * FTP sync never overwrote: every path returned 200, so nothing looked
 * wrong server-side, while the offcanvas nav, .visually-hidden-focusable
 * skip link and .form-select silently rendered unstyled. Three things stop
 * that from recurring, and gd_asset() is where two of them live:
 *
 *   1. Vendor libraries live under version-stamped folders
 *      (assets/vendor/bootstrap-5.3.3/...), so a stale copy from an older
 *      project can never occupy the same path. A missed upload becomes a
 *      loud 404 instead of a quietly wrong file.
 *   2. Every URL carries ?v=<mtime>-<size> of the file AS DEPLOYED, so the
 *      moment a corrected file lands, browsers and the Cloudflare edge
 *      fetch it again rather than replaying a cached copy.
 *   3. Each call stats the deployed file against data/asset-manifest.php
 *      (one stat(), no hashing) and records any size mismatch or missing
 *      file. includes/footer.php surfaces those; tools/asset-check.php
 *      does the full SHA-256 verification on demand.
 * ------------------------------------------------------------------- */

// Shipped sizes/hashes, written by tools/build-asset-manifest.php.
function gd_asset_manifest(): array {
    static $manifest = null;
    if ($manifest === null) {
        $file = __DIR__ . '/data/asset-manifest.php';
        $manifest = is_file($file) ? (array)require $file : [];
    }
    return $manifest;
}

// Deploy problems found while rendering this request, keyed by asset path.
function &gd_asset_problems(): array {
    static $problems = [];
    return $problems;
}

function gd_asset_flag(string $rel, string $why): void {
    $problems = &gd_asset_problems();
    if (isset($problems[$rel])) return;   // report each asset once per request
    $problems[$rel] = $why;
    error_log("[green-divine] deploy check FAILED - {$rel}: {$why}");
}

/**
 * Cache-busted URL for a first-party or vendored CSS/JS asset.
 * Falls back to a plain gd_url() when the file can't be stat'ed, so a
 * misconfigured path still renders a <link>/<script> the browser 404s
 * visibly rather than dropping the tag altogether.
 */
function gd_asset(string $path): string {
    if (preg_match('#^(?:https?:)?//|^data:#i', $path)) return $path;

    $rel  = ltrim($path, '/');
    $url  = gd_url($path);
    $stat = @stat(__DIR__ . '/' . $rel);

    if ($stat === false) {
        gd_asset_flag($rel, 'file is MISSING on this server - upload it');
        return $url;
    }

    $expected = gd_asset_manifest()[$rel] ?? null;
    if ($expected !== null && (int)$expected['size'] !== (int)$stat['size']) {
        gd_asset_flag($rel, sprintf(
            'WRONG FILE on this server - expected %d bytes, found %d. A stale or partial upload is being served.',
            (int)$expected['size'], (int)$stat['size']
        ));
    }

    return $url . (str_contains($url, '?') ? '&' : '?')
                . 'v=' . dechex((int)$stat['mtime']) . '-' . (int)$stat['size'];
}

// Only real, configured social links — filters out the '#' placeholder
// left for channels that haven't been set up yet (e.g. no YouTube yet).
function gd_socials(array $socials): array {
    return array_filter($socials, fn($url) => $url !== '' && $url !== '#');
}

// Up to two initials from a name — used for the team/author avatar in
// place of a stock headshot until a real staff photo is supplied.
function gd_initials(string $name): string {
    $words = preg_split('/\s+/', trim($name), -1, PREG_SPLIT_NO_EMPTY);
    $chars = array_map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)), array_slice($words, 0, 2));
    return implode('', $chars) ?: '?';
}

$DATA = [];

/* ---------------------------------------------------------------------
 * 1. SITE / BRAND — data/content/site.php (admin: Settings)
 * ---------------------------------------------------------------------
 * 'domain' and 'base_url' are NOT stored content — they're request-
 * derived plumbing recomputed on every load, same as before this file
 * was split. Persisting them would bake in whatever host happened to
 * run the last admin save.
 * ------------------------------------------------------------------- */
$DATA['site']             = require __DIR__ . '/data/content/site.php';
$DATA['site']['domain']   = parse_url(GD_SITE_URL, PHP_URL_HOST) ?: 'greendivine.in';
$DATA['site']['base_url'] = GD_SITE_URL;

/* ---------------------------------------------------------------------
 * 1b. CONFIG — data/content/config.php (admin: Settings)
 * ---------------------------------------------------------------------
 * google_sheet_endpoint: paste your deployed Apps Script Web App URL
 * here to record product enquiries to a Google Sheet. See
 * docs/google-sheets-setup.md for step-by-step instructions.
 * Leave empty to fall back to a mailto: link.
 *
 * HERO LAYOUT SWITCHES — home_hero_variant / category_hero_variant:
 *   1 = Split      (text + image, side-by-side)          [default]
 *   2 = Showcase   (banner image + overlapping content card)
 *   3 = Immersive  (full-bleed image + frosted-glass card)
 *   4 = Festival   (celebratory offer hero — see $DATA['festival'])
 * ------------------------------------------------------------------- */
$DATA['config'] = require __DIR__ . '/data/content/config.php';

/* ---------------------------------------------------------------------
 * 1c. FESTIVAL OFFER — data/content/festival.php (admin: Festival Banner)
 * ---------------------------------------------------------------------
 * Used when a hero variant is set to 4 (home or a landing page). A
 * landing page may override it with its own 'festival' => [...] array
 * inside data/landing/<slug>.php.
 * ------------------------------------------------------------------- */
$DATA['festival'] = require __DIR__ . '/data/content/festival.php';

/* ---------------------------------------------------------------------
 * 1d. QUOTE POPUP — data/content/quote_popup.php (admin: Quote Popup)
 * ---------------------------------------------------------------------
 * Phone-only lead, centered modal (every page). Appears once per
 * visitor: shown after a delay, then never again — tracked in
 * localStorage, not sessionStorage. See includes/quote-popup.php.
 * enabled = false → popup never renders. Submissions post to the same
 * Google Sheet endpoint as every other form, tagged source "quote-bar".
 * ------------------------------------------------------------------- */
$DATA['quote_popup'] = require __DIR__ . '/data/content/quote_popup.php';

/* ---------------------------------------------------------------------
 * 1e. PAGE COPY BLOCKS — data/content/page_content.php (admin: Page Copy)
 * ---------------------------------------------------------------------
 * Small, purpose-specific narrative blocks that used to be hand-typed
 * directly into pages/about.php, home.php, services.php and contact.php
 * (the About "Our Story" + pillars, Home's "Why Green Divine" editorial
 * + quote intro, Services' CTA band, Contact's hero). Moved here so the
 * whole site's copy — not just the parts that already happened to be
 * arrays — is admin-editable.
 * ------------------------------------------------------------------- */
$DATA['page_content'] = require __DIR__ . '/data/content/page_content.php';

/* ---------------------------------------------------------------------
 * 2. HERO SLIDES — data/content/hero.php (admin: Hero Slides)
 * ------------------------------------------------------------------- */
$DATA['hero'] = require __DIR__ . '/data/content/hero.php';

/* ---------------------------------------------------------------------
 * 3. CATEGORIES — data/content/categories.php (admin: Categories)
 * ------------------------------------------------------------------- */
$DATA['categories'] = require __DIR__ . '/data/content/categories.php';

/* ---------------------------------------------------------------------
 * 4. PRODUCTS — data/content/products.php (admin: Products)
 * ------------------------------------------------------------------- */
$DATA['products'] = require __DIR__ . '/data/content/products.php';

/* ---------------------------------------------------------------------
 * 4b. CATEGORY LANDING PAGES
 * ---------------------------------------------------------------------
 * Each landing page has its OWN data file under data/landing/<slug>.php
 * (one file per category slug), managed by admin: Landing Pages. The
 * single reusable view pages/category.php renders whichever file
 * gd_find_landing() loads. To add a category landing page: add the
 * category via admin: Categories, then create its landing page from
 * the Landing Pages module (auto-scaffolded with blank defaults).
 * ------------------------------------------------------------------- */

/* ---------------------------------------------------------------------
 * 5. STATS — data/content/stats.php (admin: Stats / Counters)
 * ------------------------------------------------------------------- */
$DATA['stats'] = require __DIR__ . '/data/content/stats.php';

/* ---------------------------------------------------------------------
 * 6. CLIENT MARQUEE — data/content/clients.php (admin: Clients)
 * ------------------------------------------------------------------- */
$DATA['clients'] = require __DIR__ . '/data/content/clients.php';

/* ---------------------------------------------------------------------
 * 7. FAQS — data/content/faqs.php (admin: FAQs — site-wide tab)
 * ------------------------------------------------------------------- */
$DATA['faqs'] = require __DIR__ . '/data/content/faqs.php';

/* ---------------------------------------------------------------------
 * 7b. PAGE-SPECIFIC FAQS — data/content/page_faqs.php (admin: FAQs — per-page tabs)
 * ---------------------------------------------------------------------
 * Keyed by the router's "active" key (home, products, services, about,
 * blogs, contact). The FAQ component (includes/faq-carousel.php) picks
 * the matching set; falls back to $DATA['faqs'] when no key matches.
 * Override per-page by setting $GLOBALS['gd_page_faqs'] in a template
 * (the category landing page does this).
 * ------------------------------------------------------------------- */
$DATA['page_faqs'] = require __DIR__ . '/data/content/page_faqs.php';

/* ---------------------------------------------------------------------
 * 8. SERVICES (short cards) — data/content/services.php (admin: Services)
 * ------------------------------------------------------------------- */
$DATA['services'] = require __DIR__ . '/data/content/services.php';

/* ---------------------------------------------------------------------
 * 8b. SERVICE DETAILS — data/content/service_details.php (admin: Service Details)
 * ------------------------------------------------------------------- */
$DATA['service_details'] = require __DIR__ . '/data/content/service_details.php';

/* ---------------------------------------------------------------------
 * 8c. WHY US — data/content/why_us.php (admin: Why Us)
 * ------------------------------------------------------------------- */
$DATA['why_us'] = require __DIR__ . '/data/content/why_us.php';

/* ---------------------------------------------------------------------
 * 8d. PROCESS — 4-step "How we work" — data/content/process.php (admin: Process Steps)
 * ------------------------------------------------------------------- */
$DATA['process'] = require __DIR__ . '/data/content/process.php';

/* ---------------------------------------------------------------------
 * 8e. TEAM (about page) — data/content/team.php (admin: Team)
 * ---------------------------------------------------------------------
 * No 'photo' field on purpose — until real staff photos are supplied,
 * templates render an initials avatar instead of a stock headshot (a
 * placeholder face is worse for trust than no face at all).
 * ------------------------------------------------------------------- */
$DATA['team'] = require __DIR__ . '/data/content/team.php';

/* ---------------------------------------------------------------------
 * 8f. CERTIFICATIONS / TRUST BADGES — data/content/certifications.php (admin: Certifications)
 * ---------------------------------------------------------------------
 * Intentionally empty by default. An unverified compliance claim is a
 * worse look than no badge at all — only add one with a real logo and a
 * credential that can be verified on request.
 * ------------------------------------------------------------------- */
$DATA['certifications'] = require __DIR__ . '/data/content/certifications.php';

/* ---------------------------------------------------------------------
 * 8g. REVIEWS — data/content/reviews.php (admin: Reviews), optionally tied to a product slug
 * ------------------------------------------------------------------- */
$DATA['reviews'] = require __DIR__ . '/data/content/reviews.php';

/* ---------------------------------------------------------------------
 * 9. BLOGS — data/content/blogs.php (admin: Blogs)
 * ------------------------------------------------------------------- */
$DATA['blogs'] = require __DIR__ . '/data/content/blogs.php';

/* ---------------------------------------------------------------------
 * 10. NAVIGATION — data/content/nav.php (admin: Navigation Menu)
 * ------------------------------------------------------------------- */
$DATA['nav'] = require __DIR__ . '/data/content/nav.php';

/* =====================================================================
 * Helpers — pure functions, no DB needed
 * =================================================================== */

function gd_find_product(string $slug): ?array {
    global $DATA;
    foreach ($DATA['products'] as $p) if ($p['slug'] === $slug) return $p;
    return null;
}

function gd_todays_deal(): ?array {
    global $DATA;
    foreach ($DATA['products'] as $p) if (!empty($p['is_deal'])) return $p;
    return $DATA['products'][0] ?? null;
}

function gd_find_category(string $slug): ?array {
    global $DATA;
    foreach ($DATA['categories'] as $c) if ($c['slug'] === $slug) return $c;
    return null;
}

/**
 * Landing-page content for a category slug.
 * Each landing page lives in its own file: data/landing/<slug>.php, which
 * returns a plain array. This keeps every landing page self-contained and
 * reusable, and is rendered by the single view pages/category.php.
 * Returns null if no data file exists for the slug.
 */
function gd_find_landing(string $slug): ?array {
    static $cache = [];
    // Underscores are allowed (the '__sports' category uses them); '.' and
    // '/' stay excluded so the slug can never escape data/landing/.
    $slug = preg_replace('/[^a-z0-9_\-]/', '', strtolower($slug));
    if ($slug === '') return null;
    if (array_key_exists($slug, $cache)) return $cache[$slug];

    $file = __DIR__ . '/data/landing/' . $slug . '.php';
    $data = is_file($file) ? require $file : null;
    return $cache[$slug] = (is_array($data) ? $data : null);
}

/** Products belonging to a category, optionally limited to N items. */
function gd_products_by_category(string $slug, int $limit = 0): array {
    global $DATA;
    $out = [];
    foreach ($DATA['products'] as $p) {
        if (($p['category'] ?? '') === $slug) $out[] = $p;
    }
    return $limit > 0 ? array_slice($out, 0, $limit) : $out;
}

function gd_find_blog(string $slug): ?array {
    global $DATA;
    foreach ($DATA['blogs'] as $b) if ($b['slug'] === $slug) return $b;
    return null;
}

/**
 * Should this product publish a price?
 *
 * Driven by the per-product 'show_price' flag in data/content/products.php
 * (toggled from the admin product editor). The default is DELIBERATELY
 * false: most of the catalogue is quoted per site rather than sold at a
 * list price, so a product that has never been reviewed stays quote-only
 * instead of leaking a stale figure the moment it is added.
 *
 * Every surface that renders a price goes through this — product cards,
 * the product page, the home "today's deal" panel and the Product JSON-LD
 * — so a product can never show a price in one place and hide it in
 * another. When it returns false the price is replaced by a "Get Quote"
 * button that opens WhatsApp via gd_whatsapp_link().
 */
function gd_show_price(array $product): bool {
    return !empty($product['show_price']) && trim((string)($product['price_label'] ?? '')) !== '';
}

/**
 * Pre-filled WhatsApp chat about one product. $intent replaces the opening
 * clause so a "Get Quote" button asks for a quote while "Buy on WhatsApp"
 * keeps the original wording — both land in the same inbox with the product
 * name and its URL already typed out.
 */
function gd_whatsapp_link(array $product, string $intent = "I'm interested in"): string {
    global $DATA;
    $url = $DATA['site']['base_url'] . '/product/' . $product['slug'];
    $msg = "Hi Green Divine, {$intent} {$product['name']} — {$url}";
    return 'https://wa.me/' . $DATA['site']['whatsapp'] . '?text=' . rawurlencode($msg);
}

/**
 * Reviews for a single product slug. Pass '' to get site-wide reviews
 * (those with no specific product attached).
 */
function gd_product_reviews(string $slug): array {
    global $DATA;
    $out = [];
    foreach ($DATA['reviews'] as $r) {
        if ($slug === '' ? empty($r['product']) : (($r['product'] ?? '') === $slug)) {
            $out[] = $r;
        }
    }
    return $out;
}

/**
 * Average rating + count. If $slug is empty, aggregates across ALL reviews.
 * Returns ['avg' => float, 'count' => int].
 */
function gd_rating(string $slug = ''): array {
    global $DATA;
    $sum = 0; $n = 0;
    foreach ($DATA['reviews'] as $r) {
        if ($slug !== '' && ($r['product'] ?? '') !== $slug) continue;
        $sum += (int)($r['rating'] ?? 0);
        $n++;
    }
    return ['avg' => $n ? round($sum / $n, 1) : 0, 'count' => $n];
}

/** Render star icons (filled / half / empty) for a 0–5 rating. */
function gd_stars(float $rating): string {
    $full  = (int)floor($rating);
    $half  = ($rating - $full) >= 0.5 ? 1 : 0;
    $empty = 5 - $full - $half;
    $html  = str_repeat('<i class="bi bi-star-fill"></i>', $full);
    $html .= $half ? '<i class="bi bi-star-half"></i>' : '';
    $html .= str_repeat('<i class="bi bi-star"></i>', $empty);
    return $html;
}

/** Safe HTML-escape shortcut. */
function e(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
