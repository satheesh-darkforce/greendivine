<?php
/**
 * Green Divine — Routing layer
 *
 * Resolves the incoming ?route=... value (set by .htaccess) into:
 *   - the template path to load
 *   - the active nav key (so the header can highlight it)
 *   - a $pageMeta array used by includes/header.php for SEO tags
 *
 * Every other page-specific variable (e.g. $product, $category, $blog)
 * is also set on the global scope so the template can use it directly.
 */

if (!function_exists('gd_resolve_route')) {

/**
 * Old category slug -> its current one, or null if the slug wasn't renamed.
 *
 * Renamed categories keep their old URLs alive with a 301 so links already
 * shared, indexed or printed don't 404 — both /<slug> and /products/<slug>
 * go through here. Add a pair whenever a category slug changes; an entry
 * can be retired once the old URL stops showing up in traffic.
 */
function gd_renamed_category(string $slug): ?string {
    static $renamed = [
        'vertical-gardens'    => 'artificial-vertical-garden',
        'turff-installations' => '__sports',
        'turf-sports'         => '__sports',
    ];
    return $renamed[$slug] ?? null;
}

function gd_resolve_route(string $route): array {
    global $DATA;

    $route = trim($route, "/ \t\n\r\0\x0B");
    if ($route === '' || $route === 'index.php') $route = 'home';

    $site = $DATA['site'];

    switch ($route) {

        case 'home':
            return [
                'template' => __DIR__ . '/../pages/home.php',
                'active'   => 'home',
                'meta'     => [
                    'title'       => $site['name'] . ' — ' . $site['tagline'],
                    'description' => 'Green Divine, Bangalore: premium artificial grass, vertical gardens & landscaping. Transform your space with sustainable green solutions.',
                    'canonical'   => $site['base_url'] . '/',
                ],
            ];

        case 'products':
            $filter = isset($_GET['filter']) ? preg_replace('/[^a-z0-9_\-]/', '', $_GET['filter']) : '';
            if ($filter !== '' && ($newFilter = gd_renamed_category($filter))) {
                header('Location: ' . gd_url('/products/' . $newFilter), true, 301);
                exit;
            }
            $GLOBALS['filter'] = $filter;
            $title = 'Our Products';
            if ($filter && ($c = gd_find_category($filter))) {
                $title = $c['name'] . ' — Products';
            }
            return [
                'template' => __DIR__ . '/../pages/products.php',
                'active'   => 'products',
                'meta'     => [
                    'title'       => $title . ' | ' . $site['name'],
                    'description' => 'Browse Green Divine\'s catalogue of artificial grass, vertical gardens, landscaping and decor solutions.',
                    'canonical'   => $site['base_url'] . '/products' . ($filter ? "/$filter" : ''),
                ],
            ];

        case 'category':
            // Legacy /category/<slug> → 301 to the clean root-level URL /<slug>.
            $slug = isset($_GET['slug']) ? preg_replace('/[^a-z0-9_\-]/', '', $_GET['slug']) : '';
            if ($slug !== '' && gd_find_category($slug) && gd_find_landing($slug)) {
                header('Location: ' . gd_url('/' . $slug), true, 301);
                exit;
            }
            return gd_route_404();

        case 'product-single':
            $slug    = isset($_GET['slug']) ? preg_replace('/[^a-z0-9_\-]/', '', $_GET['slug']) : '';
            $product = gd_find_product($slug);
            if (!$product) return gd_route_404();
            $GLOBALS['product'] = $product;
            return [
                'template' => __DIR__ . '/../pages/product-single.php',
                'active'   => 'products',
                'meta'     => [
                    'title'       => $product['meta_title'] ?? ($product['name'] . ' | ' . $site['name']),
                    'description' => $product['meta_desc'] ?? $product['short'],
                    'canonical'   => $site['base_url'] . '/product/' . $product['slug'],
                    'image'       => gd_img($product['images'][0] ?? '', 'products'),
                ],
            ];

        case 'about':
            return [
                'template' => __DIR__ . '/../pages/about.php',
                'active'   => 'about',
                'meta'     => [
                    'title'       => 'About Us | ' . $site['name'],
                    'description' => 'Learn about Green Divine — Bangalore\'s trusted partner for sustainable landscaping since 2017.',
                    'canonical'   => $site['base_url'] . '/about',
                ],
            ];

        case 'services':
            return [
                'template' => __DIR__ . '/../pages/services.php',
                'active'   => 'services',
                'meta'     => [
                    'title'       => 'Services | ' . $site['name'],
                    'description' => 'Turf installation, vertical gardens, rooftop landscaping, maintenance and corporate projects.',
                    'canonical'   => $site['base_url'] . '/services',
                ],
            ];

        case 'blogs':
            return [
                'template' => __DIR__ . '/../pages/blogs.php',
                'active'   => 'blogs',
                'meta'     => [
                    'title'       => 'Blog | ' . $site['name'],
                    'description' => 'Tips, trends and case studies from the Green Divine landscaping team.',
                    'canonical'   => $site['base_url'] . '/blogs',
                ],
            ];

        case 'blog-single':
            $slug = isset($_GET['slug']) ? preg_replace('/[^a-z0-9_\-]/', '', $_GET['slug']) : '';
            $blog = gd_find_blog($slug);
            if (!$blog) return gd_route_404();
            $GLOBALS['blog'] = $blog;
            return [
                'template' => __DIR__ . '/../pages/blog-single.php',
                'active'   => 'blogs',
                'meta'     => [
                    // A post's own meta_title is used verbatim (it usually
                    // already carries the brand); otherwise "Title | Site".
                    'title'       => ($blog['meta_title'] ?? '') !== '' ? $blog['meta_title'] : $blog['title'] . ' | ' . $site['name'],
                    'description' => ($blog['meta_desc'] ?? '') !== '' ? $blog['meta_desc'] : $blog['excerpt'],
                    'canonical'   => $site['base_url'] . '/blog/' . $blog['slug'],
                    'image'       => gd_img($blog['image'] ?? '', 'blog'),
                ],
            ];

        case 'contact':
            return [
                'template' => __DIR__ . '/../pages/contact.php',
                'active'   => 'contact',
                'meta'     => [
                    'title'       => 'Contact Us | ' . $site['name'],
                    'description' => 'Get in touch with Green Divine for a free landscaping consultation in Bangalore.',
                    'canonical'   => $site['base_url'] . '/contact',
                ],
            ];

        case 'privacy':
            return [
                'template' => __DIR__ . '/../pages/privacy.php',
                'active'   => '',
                'meta'     => [
                    'title'       => 'Privacy Policy | ' . $site['name'],
                    'description' => 'How ' . $site['name'] . ' collects, uses and protects your personal information.',
                    'canonical'   => $site['base_url'] . '/privacy-policy',
                ],
            ];

        case 'terms':
            return [
                'template' => __DIR__ . '/../pages/terms.php',
                'active'   => '',
                'meta'     => [
                    'title'       => 'Terms of Service | ' . $site['name'],
                    'description' => 'The terms and conditions for using the ' . $site['name'] . ' website and services.',
                    'canonical'   => $site['base_url'] . '/terms',
                ],
            ];

        default:
            // Clean category landing URL: /<slug> (no /category/ prefix).
            // The category's editable `slug` in data.php IS the public URL.
            $slug     = preg_replace('/[^a-z0-9_\-]/', '', $route);

            if ($new = gd_renamed_category($slug)) {
                header('Location: ' . gd_url('/' . $new), true, 301);
                exit;
            }

            $category = gd_find_category($slug);
            $landing  = gd_find_landing($slug);
            if ($category && $landing) {
                $GLOBALS['category'] = $category;
                $GLOBALS['landing']  = $landing;
                return [
                    'template' => __DIR__ . '/../pages/category.php',
                    'active'   => 'products',
                    'meta'     => [
                        'title'       => $landing['meta_title'] ?? ($category['name'] . ' | ' . $site['name']),
                        'description' => $landing['meta_desc']  ?? $category['desc'],
                        'canonical'   => $site['base_url'] . '/' . $category['slug'],
                        'image'       => gd_img($landing['hero_image'] ?? $category['image'], 'categories'),
                    ],
                ];
            }
            return gd_route_404();
    }
}

/**
 * What to do with a URL that matches nothing.
 *
 * Default behaviour is a redirect to the home page rather than the 404
 * page, so a mistyped or retired link still lands somewhere useful.
 * Set 'unknown_url' => '404' in data/content/config.php to show the
 * styled pages/404.php instead — handy while hunting a broken link.
 *
 * The redirect is 302, never 301: a permanent redirect gets cached by
 * browsers and search engines, so a slug added later would keep bouncing
 * visitors to the home page long after the page existed.
 *
 * Requests that look like a FILE (anything with an extension — .css,
 * .js, .jpg…) always get a real 404. Answering those with an HTML page
 * would hide a missing asset behind a 200, which is exactly the kind of
 * silent failure that makes a bad deploy hard to spot.
 */
function gd_route_404(): array {
    global $DATA;

    $raw       = (string)($_GET['route'] ?? '');
    $looksFile = (bool)preg_match('/\.[a-z0-9]{2,5}$/i', $raw);
    $mode      = $DATA['config']['unknown_url'] ?? 'home';

    if ($mode !== '404' && !$looksFile) {
        header('Location: ' . gd_url('/'), true, 302);
        exit;
    }

    http_response_code(404);
    return [
        'template' => __DIR__ . '/../pages/404.php',
        'active'   => '',
        'meta'     => [
            'title'       => 'Page Not Found | ' . $DATA['site']['name'],
            'description' => 'The page you are looking for could not be found.',
            'canonical'   => $DATA['site']['base_url'] . '/',
        ],
    ];
}

} // function_exists guard
