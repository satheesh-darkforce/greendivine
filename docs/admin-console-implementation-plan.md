# Admin Console — Implementation Plan

**Status:** Planning document only. Nothing in this plan has been built yet —
this is the design to review/approve before implementation starts.

**Scope:** A password-protected admin panel that can edit every piece of
dynamic content currently hardcoded in `data.php` (and a few strings that are
hardcoded directly in page templates), with no database — content persists as
PHP array files on disk, the same storage model the site already uses.

---

## 1. Goals

1. A login screen (username + password) gating access to `/admin/`.
2. Credentials stored in a PHP array, passwords hashed (never plaintext).
3. One admin screen per content type currently in `$DATA` — add / edit /
   delete / reorder — so editing content never requires touching PHP code.
4. Changes take effect on the live site immediately (no build step, no
   cache to bust beyond normal opcache behavior).
5. Server-side validation on every field, with clear inline error messages.
6. A layout that doesn't feel like a raw form dump — grouped navigation,
   consistent list/edit patterns, image previews, confirmation on destructive
   actions.

## 2. Constraints (as requested, and why they're workable here)

| Constraint | How this plan satisfies it |
|---|---|
| No backend / database | No MySQL, no external service. Content is read/written as PHP array files under `data/content/*.php`, exactly like `data.php` today — just split into one file per domain, with a small read/write layer instead of hand-editing. |
| "Use array to store username/password" | `admin/config/admin-users.php` returns a plain associative array: `username => ['password_hash' => ..., 'display_name' => ...]`. No table, no ORM. |
| "Use any hashing mechanism" | PHP's built-in `password_hash()` / `password_verify()` (bcrypt, `PASSWORD_DEFAULT`) — the standard, salted, adaptive-cost hashing function built into PHP itself. Nothing custom or reversible. |
| Everything dynamic should reflect in the admin panel | Section 8 inventories every single array in `data.php` (plus the per-category landing files, plus a few strings hardcoded in templates that need to move into arrays first) and gives each one an admin screen. |

---

## 3. Current State — What's Actually Dynamic Today

This is the full inventory, read directly from the live `data.php`, the six
`data/landing/<slug>.php` files, and the page templates. Two things stood out
while auditing:

- **Everything in `$DATA` is already a clean, admin-friendly array shape** —
  no refactor needed there, just a read/write layer around it.
- **A handful of content blocks are hardcoded directly in page templates**,
  not in `$DATA` at all, so they're invisible to any admin panel until moved:
  - `pages/about.php` — the "Our Story" eyebrow/title/lead/body/bullet list,
    and the Mission / Vision / Promise pillar copy.
  - `pages/home.php` — the "Why Green Divine" editorial section copy
    (eyebrow, "8 Years, One In-House Crew" heading, paragraph).
  - `pages/services.php` / `pages/about.php` — the CTA band heading + sub
    copy ("Have a project in mind?" etc.).
  - `pages/contact.php` — business hours text ("Mon–Sat · 9am–7pm") and the
    Google Maps embed query, both hand-typed into the template.

  These get a new `$DATA['page_content']` domain (Section 8.18) so the whole
  site's copy is genuinely admin-editable, not just the parts that happened
  to already be arrays.

### 3.1 `$DATA` domains (from `data.php`)

| Key | Shape | Used by |
|---|---|---|
| `site` | name, tagline, location, domain, phone, phone_label, whatsapp, email, address, socials{facebook,instagram,youtube,linkedin} | header, footer, contact, every WhatsApp/tel link |
| `config` | google_sheet_endpoint, logo, og_default, home_hero_variant, category_hero_variant | meta tags, forms, hero variant switch |
| `festival` | eyebrow, title, highlight, sub, coupon, valid, image, cta{label,href}, cta2 | Festival hero (home + category, when hero variant = 4) |
| `quote_popup` | enabled, delay, eyebrow, title, sub, cta | centered quote popup modal (every page) |
| `hero[]` | image, alt, heading, sub, cta{label,href} | home page hero slides |
| `categories[]` | slug, name, icon, image, desc | category grid, filters, footer, nav |
| `products[]` | slug, name, category, short, description, price_label, price_was, badge, is_deal, images[], features[], specs{}, installations[], meta_title, meta_desc | product listing, single product page, today's deal |
| `stats[]` | number, suffix, label | stats/counters band (home, about) |
| `clients[]` | name, logo | client-logo marquee |
| `faqs[]` | q, a | site-wide FAQ fallback |
| `page_faqs{route: []}` | q, a, keyed by home/products/services/about/blogs/contact | per-page FAQ accordion |
| `services[]` | icon, title, desc | services overview cards |
| `service_details[]` | slug, icon, title, image, desc, features[] | services page deep-dive blocks |
| `why_us[]` | icon, title, desc | about page "Why Green Divine" feature rows |
| `process[]` | icon, title, desc | shared "How It Works" stepper (home/about/services) |
| `team[]` | name, role, bio | about page team grid |
| `certifications[]` | name, logo (currently empty on purpose) | trust badges (not rendered until populated) |
| `reviews[]` | name, location, rating, product, date, avatar, body | testimonials carousel (site-wide + per-product) |
| `blogs[]` | slug, title, excerpt, tldr, body(HTML), image, date, author, tags[], read_min, key_takeaways[], pull_quote{text,author}, gallery[]{src,caption}, faqs[]{q,a}, references[]{title,url,source}, cta_category | blog listing + single post |
| `nav[]` | label, href, route | header + mobile nav |

### 3.2 Per-category landing pages (`data/landing/<slug>.php`)

One file per category slug (6 today: artificial-grass, vertical-gardens,
artificial-plants, creepers-bunches, turff-installations,
commercial-installations). Each returns: eyebrow, h1, hero_sub, hero_image,
hero_alt, tagline, meta_title, meta_desc, intro (HTML), hero_points[],
benefits[]{icon,title,desc}, use_cases[]{icon,title,desc},
process[]{icon,title,desc} (optional override of the global process),
ad_slots[]{eyebrow,title,sub,image,cta}, gallery[] (image refs),
stats[]{number,suffix,label}, faqs[]{q,a}.

This is already exactly the "one file per record, admin writes the file"
pattern the rest of this plan uses — no schema change needed, just an editor
that targets `data/landing/<slug>.php` instead of a slice of one big array.

### 3.3 Routing / request-flow facts that shape the design

- `.htaccess` only rewrites requests for paths that **aren't** real
  files/directories. A real `/admin/` folder with real `.php` files is served
  directly by Apache — the admin panel needs no router changes at all.
- `index.php` loads `data.php` once per request; `data.php` builds `$DATA`
  from whatever the content files return. So the admin panel's only job is to
  read/write those files correctly — the public site already re-reads them
  on every request (no cache to invalidate).
- PHP 8.2 is running locally — modern enough for everything in this plan
  (typed properties, `password_hash`/`password_verify`, `match`, etc.).

---

## 4. Architecture Overview

### 4.1 New folder structure

```
greendivine-final/
├── admin/
│   ├── index.php                 # dashboard (auth-gated)
│   ├── login.php                 # login form + handler
│   ├── logout.php
│   ├── config/
│   │   └── admin-users.php       # returns ['username' => ['password_hash'=>..., 'display_name'=>...]]
│   ├── inc/
│   │   ├── auth.php              # session guard — require at top of every admin page
│   │   ├── csrf.php              # token generate/verify helpers
│   │   ├── store.php             # Store::load()/save()/backup() — the persistence layer
│   │   ├── validate.php          # reusable field validators
│   │   ├── uploads.php           # image upload + media-library helpers
│   │   ├── layout-top.php        # <head>, sidebar, topbar open
│   │   └── layout-bottom.php     # closing markup, admin JS
│   ├── modules/
│   │   ├── settings.php          # site/brand/socials/config/hours/map
│   │   ├── quote-popup.php
│   │   ├── festival.php
│   │   ├── nav.php
│   │   ├── hero-slides.php
│   │   ├── categories.php
│   │   ├── landing-pages.php     # one editor, per-slug
│   │   ├── products.php
│   │   ├── product-edit.php
│   │   ├── services.php
│   │   ├── service-details.php
│   │   ├── process-steps.php
│   │   ├── why-us.php
│   │   ├── team.php
│   │   ├── stats.php
│   │   ├── clients.php
│   │   ├── certifications.php
│   │   ├── reviews.php
│   │   ├── blogs.php
│   │   ├── blog-edit.php
│   │   ├── faqs.php              # site-wide + per-page tabs
│   │   ├── page-content.php      # the hardcoded-string migration (Section 8.18)
│   │   └── users.php             # change password / add admin
│   └── assets/
│       ├── css/
│       │   ├── admin-core.css    # shell: sidebar, topbar, flash banners, tables,
│       │   │                     #   form controls, buttons — loaded on every admin page
│       │   ├── login.css         # standalone; the login page never loads the shell
│       │   ├── products.css      # spec key/value rows, image-picker grid, deal toggle
│       │   ├── blogs.css         # gallery/FAQ/reference repeatable groups, rich-text toolbar
│       │   ├── landing-pages.css # per-slug tabs, ad-slot pair layout
│       │   └── dashboard.css
│       └── js/
│           ├── admin-core.js     # shared: repeatable add/remove rows, slug auto-suggest,
│           │                     #   image picker, flash auto-dismiss, sidebar toggle,
│           │                     #   drag/up-down reordering — every module needs at least this
│           ├── products.js       # is_deal single-select enforcement, specs rows
│           ├── blogs.js          # gallery/FAQ/reference row templates, rich-text toolbar
│           └── landing-pages.js  # per-slug tab switching, ad-slot count lock (exactly 2)
│

├── data/
│   ├── content/                  # NEW — one file per $DATA domain, admin-writable
│   │   ├── site.php
│   │   ├── config.php
│   │   ├── festival.php
│   │   ├── quote_popup.php
│   │   ├── hero.php
│   │   ├── categories.php
│   │   ├── products.php
│   │   ├── stats.php
│   │   ├── clients.php
│   │   ├── faqs.php
│   │   ├── page_faqs.php
│   │   ├── services.php
│   │   ├── service_details.php
│   │   ├── why_us.php
│   │   ├── process.php
│   │   ├── team.php
│   │   ├── certifications.php
│   │   ├── reviews.php
│   │   ├── blogs.php
│   │   ├── nav.php
│   │   └── page_content.php
│   ├── backups/                  # NEW — timestamped copies, pruned automatically
│   └── landing/                  # EXISTING — unchanged, admin writes these directly
│
├── data.php                       # becomes a thin loader (Section 5.3)
└── ...                             # everything else unchanged
```

**Why only 4 module-specific CSS/JS files, not 20** — Settings, Nav, Festival,
Quote Popup, Team, Stats, Clients, etc. are plain forms with fixed fields;
`admin-core.css`/`admin-core.js` already covers everything they need (form
layout, buttons, the odd toggle). Only **Products**, **Blogs**, and **Landing
Pages** have genuinely distinct interactive behavior (repeatable
image/spec/gallery/FAQ rows, a rich-text toolbar, per-slug tabs) that doesn't
belong in the shared file. Splitting purely "one file per module" regardless
of whether a module needs anything beyond the shared core would just recreate
the same problem in miniature — twenty near-empty files instead of one giant
one. The rule this plan actually follows, consistently, for CSS, JS, *and*
data, is: **one shared file for whatever 2+ pages/modules need, one dedicated
file for whatever only one page/module needs, and nothing gets a file of its
own just to have a file.**

### 4.2 How this coexists with the public site

- The public site's `$DATA` structure, every helper function
  (`gd_find_product`, `gd_rating`, etc.), and every template stay **exactly**
  as they are. Only *where `data.php` gets its array literals from* changes.
- The admin panel is a second, independent front controller — it doesn't go
  through `includes/router.php` at all, and never touches `$pageMeta` /
  the public header/footer.
- No JS framework, no build step — the admin UI reuses the Bootstrap 5 +
  Bootstrap Icons already vendored under `/assets/`, so there's zero new
  front-end dependency.

### 4.3 Splitting the *existing* `style.css` (3,260 lines) and `main.js` (452 lines)

The same "don't dump everything in one file" correction applies to the site's
current assets, not just the new admin ones — they're monolithic today and
should be split by page before (or alongside) the admin build, so the two
efforts share one consistent file-organization principle instead of the
admin being clean and the public site staying a single giant file.

I read through every section of both files to find out what's actually
page-exclusive versus genuinely shared. The result: **most of `style.css` is
shared** — header, mobile nav, buttons, typography, footer, FAQ, quote popup,
reveal-on-scroll, the review carousel, the process stepper, the product-card
component, and the mobile responsive overrides for all of those are all used
on **multiple** pages. Only a minority of rules belong to exactly one page.
Splitting "purely by page" without a shared file would mean copy-pasting that
shared 70% into every single page file — so this follows the identical
core-plus-specifics pattern as 4.1 and the data layer, not a naive 1-file-per-
page split.

**Proposed `assets/css/` split:**

| File | Owns | Used by |
|---|---|---|
| `base.css` | `:root` tokens, reset, buttons, `.gd-eyebrow`/`.gd-section-title`, header, mobile nav, footer, floating actions, FAQ accordion, reveal-on-scroll, quote popup, review carousel, process stepper, product-card, CTA band, sub-hero, `.gd-why-card`, form thank-you, the shared "mobile — minimal padding" overrides | every page |
| `home.css` | hero variants 1–4 *(shared with category — see below)*, categories grid, today's deal, "why us" editorial split, clients marquee, quote-lede | home |
| `products.css` | editorial page-head, filter pills | products listing |
| `product-single.css` | image gallery, specs table, trust strip, product share, price-was strikethrough | product-single |
| `category.css` | category-landing hero/benefits/use-cases/ad-slots/gallery | category landing pages |
| `about.css` | pillars, feature-rows, team cards | about |
| `contact.css` | contact tiles, contact-info, map embed | contact |
| `blogs.css` | masthead, featured cover, mosaic tiles | blogs listing |
| `blog-single.css` | share row, author card, body typography, newsletter band | blog-single |
| *(services)* | no page-exclusive rules currently found — services.php runs entirely on `base.css` + the shared component classes | services |

Hero variants 2–4 (Showcase/Immersive/Festival) are explicitly documented in
the CSS itself as "home + category" — used by both, so they live in
`home.css` and `category.php` loads that file too rather than duplicating it.

**Proposed `assets/js/` split** (452 lines total, so the shared portion is
almost everything):

| File | Owns | Used by |
|---|---|---|
| `core.js` | header scroll-shadow, mobile-nav sync, reveal-on-scroll, "How It Works" stepper, animated counters, forms handler + validation, reviews carousel, quote popup, close-mobile-menu-on-link-click | every page that has any of these (i.e. nearly every page) |
| `home.js` | marquee duplication (the only page that has a marquee) | home |
| `products.js` | category filter pills | products listing |
| `blog-single.js` | copy-link-to-clipboard share button | blog-single |

**How pages would declare what they need** — mirrors the existing
`$pageMeta` pattern (each route already sets page-specific metadata before
`header.php` runs). Each `pages/*.php` template sets, at its top:

```php
$GLOBALS['gd_page_assets'] = ['css' => ['home.css'], 'js' => ['home.js']];
```

`includes/meta.php` always emits `base.css` plus whatever `css` keys are
present; `includes/footer.php` always emits `core.js` plus whatever `js`
keys are present. Pages that need nothing extra (services, currently) simply
don't set the global — `base.css`/`core.js` alone is a complete, working
page, matching how the site renders correctly today even before any reveal
animation JS runs (the same "must never depend on JS" principle already
documented in `style.css`'s own `.gd-reveal` comments).

**This is a live-site refactor, not just a plan artifact** — unlike the
admin console (which doesn't exist yet), `style.css`/`main.js` are working,
already pixel-verified across every page. Doing this split means re-testing
every page at mobile and desktop widths afterward to confirm nothing shifted
— worth scoping as its own explicit step (Section 9's Phase 0) rather than
something that happens silently as a side effect of the admin work, so a
regression here is never confused with an admin-related bug.

---

## 5. Data Persistence Layer

### 5.1 Why per-domain PHP array files (not JSON, not SQLite)

- Matches the project's existing, explicitly-stated architecture
  ("NO DB. All data lives in PHP arrays") — this plan is a continuation of
  that decision, not a departure from it.
- `require 'file.php'` returning an array is opcache-cached automatically —
  no parsing cost beyond what `data.php` already pays today.
- `var_export()` (used to regenerate a file on save) correctly escapes
  quotes, backslashes, unicode (₹, emoji, etc.) — it's built for exactly
  this "serialize a PHP value back to valid PHP source" job.
- Splitting one 970-line file into ~20 focused files means saving the
  "Products" screen only ever rewrites `products.php`, never risks touching
  unrelated data.

### 5.2 The `Store` API (design — illustrative, not final code)

```php
// admin/inc/store.php
final class Store
{
    private const DIR     = __DIR__ . '/../../data/content/';
    private const BACKUPS = __DIR__ . '/../../data/backups/';
    private const KEEP    = 20; // backups retained per domain

    public static function load(string $key): array
    {
        $file = self::DIR . $key . '.php';
        if (!is_file($file)) return [];
        $data = require $file;
        return is_array($data) ? $data : [];
    }

    /** Atomic write: temp file + rename, plus a timestamped backup first. */
    public static function save(string $key, array $data): bool
    {
        self::backup($key);
        $file = self::DIR . $key . '.php';
        $tmp  = $file . '.' . bin2hex(random_bytes(4)) . '.tmp';
        $php  = "<?php\n/* Managed by admin — last saved " . date('c') . " */\nreturn "
              . var_export($data, true) . ";\n";
        if (file_put_contents($tmp, $php, LOCK_EX) === false) return false;
        return rename($tmp, $file); // atomic on the same filesystem
    }

    private static function backup(string $key): void
    {
        $file = self::DIR . $key . '.php';
        if (!is_file($file)) return;
        copy($file, self::BACKUPS . "{$key}-" . date('Ymd-His') . '.php');
        self::prune($key);
    }

    private static function prune(string $key): void
    {
        $files = glob(self::BACKUPS . "{$key}-*.php") ?: [];
        rsort($files); // newest first
        foreach (array_slice($files, self::KEEP) as $old) unlink($old);
    }
}
```

Every module's save handler becomes: validate `$_POST` → build the clean
array → `Store::save('products', $data)` → redirect with a flash message.
Every list/edit screen's read becomes `Store::load('products')`.

### 5.3 Migrating `data.php`

`data.php` keeps its helper functions (`gd_url`, `gd_img`, `gd_find_product`,
etc.) and the base-URL auto-detection exactly as-is. Only the array
*literals* move out, replaced with loads from `data/content/`:

```php
// Before (today):
$DATA['site'] = [ 'name' => 'Green Divine', ... ];

// After:
$DATA['site'] = require __DIR__ . '/data/content/site.php';
```

**Migration step, one-time:** a small CLI script (`admin/bin/migrate.php`,
run once from the command line, not web-exposed) reads the *current*
`data.php`, and for each `$DATA[...]` key writes the matching
`data/content/<key>.php` file via `Store::save()`. After this runs once and
is verified, `data.php`'s literals are deleted and replaced with the
`require` lines above. The site's rendered output is identical before and
after — this is a storage migration, not a content change.

### 5.4 Category landing pages

No new pattern needed — `data/landing/<slug>.php` already *is* "one file,
returns an array." The Landing Pages admin module (8.5) just points
`Store::load()`/`save()` at `data/landing/{$slug}.php` instead of
`data/content/{$key}.php`. Creating a new category auto-scaffolds a landing
file with sensible blank defaults; deleting a category prompts to also
archive (rename, not delete) its landing file.

### 5.5 Backups & recovery

- Every save keeps the last 20 versions of that one file in
  `data/backups/`, named `{key}-{timestamp}.php`.
- A "Restore" action on each module's list screen shows recent backups for
  that domain and lets an admin roll back with one click (copies the backup
  over the live file, itself preceded by a fresh backup of the current
  state — so a bad restore is never a dead end either).
- This is the safety net that a database transaction/version history would
  normally provide, adapted to flat files.

---

## 6. Authentication & Security

### 6.1 Credential storage

```php
// admin/config/admin-users.php
return [
    'admin' => [
        'password_hash' => '$2y$10$....',   // password_hash('...', PASSWORD_DEFAULT)
        'display_name'  => 'Administrator',
        'created_at'    => '2026-07-10',
    ],
];
```

- A plain PHP array, as requested — supports more than one admin later just
  by adding another key.
- **No default/seeded password ships silently.** First-run setup generates
  one random password, prints it once to the terminal during migration, and
  forces a password change on first login (see 6.6). This avoids the classic
  "admin/admin123 left on a live site" mistake.

### 6.2 Login flow (`admin/login.php`)

1. Form: username, password, CSRF token (hidden field).
2. On submit: verify CSRF → look up username in the array → `password_verify()`
   against the stored hash → on success, `session_regenerate_id(true)` (stops
   session-fixation) → set `$_SESSION['admin_user']` → redirect to
   `admin/index.php`.
3. On any failure, the same generic "Invalid username or password" message
   (never reveal which field was wrong) plus the rate-limit check below.
4. Failed attempts are throttled (6.5) *before* the `password_verify()` call
   even runs, so a lockout can't be timing-attacked into revealing whether a
   username exists.

### 6.3 Session guard (`admin/inc/auth.php`)

Required at the very top of every file under `admin/` except `login.php`:

```php
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'cookie_secure'   => !empty($_SERVER['HTTPS']),
]);
if (empty($_SESSION['admin_user'])) {
    header('Location: ' . gd_admin_url('/login.php'));
    exit;
}
// idle timeout — 2 hours of inactivity signs the admin out
if (time() - ($_SESSION['admin_last_seen'] ?? 0) > 7200) {
    session_destroy();
    header('Location: ' . gd_admin_url('/login.php?timeout=1'));
    exit;
}
$_SESSION['admin_last_seen'] = time();
```

### 6.4 CSRF protection

Every form (login, and every save/delete action in every module) includes a
per-session token; the handler rejects the submission if it's missing or
doesn't match. Simple, no external library needed:

```php
function csrf_field(): string {
    $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">';
}
function csrf_check(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(403);
        exit('Session expired — go back and try again.');
    }
}
```

### 6.5 Login throttling

Since there's no database for a proper attempts table, a small file
(`data/security/login-attempts.php`, array keyed by IP) tracks failed
attempts with timestamps. After 5 failures from the same IP within 15
minutes, that IP is locked out for 15 minutes regardless of whether the
password it's now sending is correct. This is intentionally simple (file +
array, consistent with the rest of the project) rather than a rate-limiting
service — sufficient for a single-admin site, not intended to withstand a
distributed attack.

### 6.6 Logout & password change

- `admin/logout.php` destroys the session and redirects to the login page.
- `admin/modules/users.php` lets the logged-in admin change their own
  password (current password required to confirm) and, optionally, add a
  second admin user. Saves rewrite `admin/config/admin-users.php` via the
  same `Store`-style atomic write (kept in `admin/config/`, not
  `data/content/`, since it's credentials, not site content).

### 6.7 Hardening checklist

- `data/`, `admin/config/`, `admin/inc/` each get a `.htaccess` with
  `Require all denied` (defense in depth — these `.php` files have no public
  output anyway since they only `return` arrays, but this stops them being
  served as plaintext if the PHP handler is ever misconfigured).
- `admin/` gets a `<meta name="robots" content="noindex,nofollow">` in its
  shared layout head, plus a `Disallow: /admin/` line in `robots.txt`.
- Session cookies: `HttpOnly`, `SameSite=Lax`, `Secure` when served over
  HTTPS (auto-detected the same way `GD_ORIGIN` already is in `data.php`).
- All admin output goes through the existing `e()` escaping helper —
  nothing new to write there, just discipline to use it in every new
  template.
- Rich-text fields (blog `body`, product `description`) are **admin-authored
  HTML by design** — the same trust model as the current `data.php` (an
  admin can already put arbitrary HTML in these fields; only an
  authenticated admin can reach that form). No sanitizer is required for
  security, but the blog/product editors will still use a constrained
  rich-text control (see 7.3) so admins don't need to hand-write HTML tags.

---

## 7. Admin UI/UX Design

### 7.1 Visual language

Reuses the site's own Bootstrap 5 bundle and Bootstrap Icons (already
vendored, zero new asset weight) with `admin-core.css` (Section 4.1) for the
sidebar/topbar chrome — a plain, calm "back office" look (not trying to
reuse the public site's editorial theme), so admins never mistake the
console for the live site.

### 7.2 Shell layout

- **Sidebar** (collapsible on mobile), grouped:
  - Dashboard
  - **Site** — Settings, Navigation Menu, Quote Popup, Festival Banner, Page Copy
  - **Catalogue** — Categories, Products, Landing Pages
  - **Content** — Services, Process Steps, Why Us, Team, Stats, Clients
  - **Blog & FAQ** — Blogs, FAQs, Reviews
  - **Account** — Change Password, (Add Admin)
- **Topbar** — logged-in display name, "View site ↗" link, Logout.
- **Flash messages** — session-based one-shot success/error banners after
  every save/delete, shown at the top of the next page load.

### 7.3 Shared patterns (built once, reused everywhere)

- **List screen** — table with columns relevant to that domain (e.g.
  Products: thumbnail, name, category, price, deal badge), Edit/Delete row
  actions, an "Add new" button, and up/down (or drag-handle) reordering for
  every array where display order matters (categories, services, process
  steps, hero slides, nav, why-us, team) — reordering just re-indexes the
  array and saves.
- **Edit screen** — a form, grouped into cards for long records (e.g. a
  Product edit groups Basics / Pricing / Images / Features / Specs /
  Installations / SEO into separate collapsible cards rather than one huge
  scroll).
- **Repeatable groups** (features list, specs key/value pairs, FAQ q/a
  pairs, gallery images) — a small vanilla-JS "add row / remove row"
  pattern, no framework: each row is `name="features[]"` style, so PHP
  reassembles the array naturally on submit; removing a row just removes
  its DOM node before submit.
- **Slug fields** — auto-suggested from the title/name via JS as you type,
  always editable, always re-validated server-side (lowercase, `a-z0-9-`
  only, uniqueness checked against the other records in that domain before
  saving).
- **Image fields** — a small picker: browse existing files already in the
  matching `assets/images/<bucket>/` folder (thumbnail grid), **or** upload
  a new one (validated by real content sniffing, not just the extension;
  max size; re-saved under a sanitized filename into the correct bucket),
  **or** paste an external URL (since `gd_img()` already passes absolute
  URLs through unchanged — keeps today's Unsplash-URL content working
  without forcing a re-upload).
- **Rich text fields** (blog body, product description) — a lightweight
  contentEditable-based toolbar (bold/italic/link/heading/list) that
  serializes to the same simple HTML these fields already contain — not a
  heavyweight embedded editor, kept dependency-free like the rest of the
  site's JS.

### 7.4 Validation approach

- **Client-side:** HTML5 `required`/`pattern`/`type` attributes for instant
  feedback — a courtesy, never the only check.
- **Server-side (authoritative):** every module's save handler validates
  before calling `Store::save()`; on any failure, redisplay the form with
  the submitted values intact and inline messages next to each invalid
  field (never lose what the admin typed). Section 8 lists the concrete
  rules per field.

---

## 8. Module Specifications

Each module below: what it manages, the field list with type + validation
rule, and anything non-obvious about the UI.

### 8.1 Site Settings (`data/content/site.php` + relevant `config.php` keys)

| Field | Type | Validation |
|---|---|---|
| name | text | required, ≤ 60 chars |
| tagline | text | required, ≤ 120 chars |
| location | text | required |
| phone | text | required, digits/+/spaces only, normalized to `+91XXXXXXXXXX` |
| phone_label | text | required (display form, e.g. `+91 99999 99999`) |
| whatsapp | text | required, digits only (no `+`), 10–15 digits |
| email | email | required, valid email format |
| address | textarea | required |
| business_hours | text | required (new — moves the hardcoded contact-page string here) |
| map_query or map_embed_src | text/url | required — used to build the Google Maps embed URL (moves the hardcoded contact-page iframe src here) |
| socials.facebook/instagram/youtube/linkedin | url | optional each, but if present must be a valid absolute URL (or `#` to mean "not linked yet", matching current placeholder convention) |
| logo | image | required, picker/upload/URL as in 7.3 |
| google_sheet_endpoint | url | optional; if present must start with `https://script.google.com/` (matches the documented Apps Script pattern) — validated with a helpful inline note linking to `docs/google-sheets-setup.md` |
| og_default | image | optional |
| home_hero_variant / category_hero_variant | select (1–4) | required, one of the four documented hero styles |

### 8.2 Quote Popup (`quote_popup.php`)

| Field | Type | Validation |
|---|---|---|
| enabled | toggle | boolean |
| delay | number | required, integer, 1–120 (seconds) |
| eyebrow / title / cta | text | required, ≤ 60/80/40 chars |
| sub | textarea | required, ≤ 200 chars |

### 8.3 Festival Banner (`festival.php`)

| Field | Type | Validation |
|---|---|---|
| eyebrow / title / highlight / coupon / valid | text | required |
| sub | textarea | required |
| image | image | required |
| cta.label / cta.href | text / url-or-path | required |
| cta2.label / cta2.href | text / url-or-path | cta2.href may be left blank (template falls back to the WhatsApp link) |

### 8.4 Navigation Menu (`nav.php`)

| Field | Type | Validation |
|---|---|---|
| label | text | required, ≤ 20 chars |
| href | text | required, must start with `/` |
| route | text | required, must match an existing router case (validated against a fixed list: home, products, services, about, blogs, contact, or a known category slug) |

Reorderable list; a "restore defaults" action re-seeds the 6 standard items
if someone deletes them all by mistake.

### 8.5 Categories (`categories.php`) + Landing Pages (`data/landing/*.php`)

| Field | Type | Validation |
|---|---|---|
| slug | text | required, `a-z0-9-`, unique, not a reserved route word (`products`, `contact`, etc.) |
| name | text | required, ≤ 40 chars |
| icon | text (icon picker) | required, must be a real Bootstrap Icons class (validated against the vendored icon font's class list) |
| image | image | required |
| desc | textarea | required, ≤ 140 chars (keeps the category-card design's single-line constraints from the earlier redesign intact) |

Deleting a category with products still assigned to it is **blocked** with a
clear message ("6 products use this category — reassign them first") rather
than silently orphaning products.

The **Landing Pages** module is a separate, per-slug editor (only shown for
categories that have one) covering: eyebrow, h1, hero_sub, hero_image,
hero_alt, tagline, meta_title, meta_desc, intro (rich text), hero_points[]
(simple string list), benefits[]/use_cases[]/process[] (icon+title+desc
repeatable groups), ad_slots[] (exactly 2, per the existing template
contract — enforced, not just suggested), gallery[] (image picker,
multi-select), stats[] (number+suffix+label repeatable), faqs[] (q/a
repeatable). Creating a new category offers "Create landing page" as a
one-click scaffold with empty-but-valid defaults.

### 8.6 Products (`products.php`)

| Field | Type | Validation |
|---|---|---|
| slug | text | required, unique, `a-z0-9-` |
| name | text | required, ≤ 80 chars |
| category | select (from Categories) | required, must reference an existing category slug |
| short | textarea | required, ≤ 140 chars |
| description | rich text | required |
| price_label | text | required (free-form, e.g. "Starting ₹85 / sqft" — matches current display-string convention rather than forcing a currency field, since some products use "Custom quote") |
| price_was | text | optional |
| badge | text | optional, ≤ 20 chars |
| is_deal | toggle | boolean; **enforced single-select** — turning this on for one product automatically turns it off for any other (the template's `gd_todays_deal()` picks the first one flagged, so the admin UI keeps that invariant true rather than letting two products silently fight for it) |
| images[] | image list | at least 1 required |
| features[] | string list | at least 1 required |
| specs{} | key/value repeatable | optional |
| installations[] | image list | optional |
| meta_title / meta_desc | text / textarea | optional, with recommended-length hints (≤60 / ≤160 chars) shown live |

### 8.7 Services (`services.php`) + Service Details (`service_details.php`)

Services (short cards): icon, title (≤40), desc (≤120) — required, reorderable.

Service Details (deep-dive): slug (unique, links back conceptually — used
for anchor consistency, not routed directly), icon, title, image, desc (rich
text), features[] (string list, min 1).

### 8.8 Process Steps (`process.php`)

Shared across home/about/services. icon, title (≤30), desc (≤100) —
required. The list must contain **exactly 4** items (the template's stepper
UI, both the tab row and the fallback stacked view, is designed for a
4-step sequence) — the editor enforces this rather than allowing 3 or 5.

### 8.9 Why Us / Feature Rows (`why_us.php`)

icon, title (≤30), desc (≤100) — required, reorderable, no fixed count
(currently 6; the 2-column row layout handles any even or odd number
gracefully).

### 8.10 Team (`team.php`)

name, role, bio — required, ≤120 chars for bio. No photo field by design
(matches the existing initials-avatar decision documented in `data.php`) —
the admin form doesn't offer a photo upload here to avoid quietly
reintroducing a stock-photo look; a future "real photos are ready" change
would be a deliberate follow-up, not a default option.

### 8.11 Stats / Counters (`stats.php`)

number (integer, required), suffix (≤3 chars, e.g. "+", "yr"), label
(required, ≤30 chars). Exactly 4 items expected by the current layout
(2×2 on mobile, 4-across on desktop) — same "enforce the count" treatment
as Process Steps.

### 8.12 Clients / Marquee (`clients.php`)

name (required), logo (image, required). No slug/order-sensitivity beyond
simple reordering (the marquee duplicates and scrolls the list either way).

### 8.13 Certifications (`certifications.php`)

name, logo — both required per entry. Currently empty on purpose (see the
existing code comment about not shipping unverified badges) — the admin
screen carries the same warning inline: "Only add a certification you can
verify on request."

### 8.14 Reviews / Testimonials (`reviews.php`)

| Field | Type | Validation |
|---|---|---|
| name | text | required |
| location | text | required |
| rating | number | required, integer 1–5 |
| product | select (from Products) or blank | optional — blank means "general/site-wide" review |
| date | date picker | required, not in the future |
| avatar | image or URL | optional (falls back to a generated initials circle if blank — mirrors the Team pattern) |
| body | textarea | required, ≤ 400 chars |

### 8.15 Blogs (`blogs.php`)

The richest record in the system. Grouped into edit-screen cards:

- **Basics** — slug (unique), title (required, ≤100), excerpt (required,
  ≤160), author (required — free text, doesn't have to match a Team member),
  date (required), read_min (integer, required), image (required).
- **Body** — tldr (rich text, required), body (rich text, required),
  pull_quote.text / pull_quote.author (optional, but both required together).
- **Key Takeaways** — string list, min 1.
- **Gallery** — repeatable {image, caption} pairs.
- **FAQs** — repeatable {q, a} pairs (this post's own FAQ block, separate
  from the site-wide/per-page FAQ system in 8.16).
- **References** — repeatable {title, url, source}; url validated as a real
  absolute URL.
- **Related category** — `cta_category` — select from Categories, optional.
- (`tags[]` field is kept in storage for continuity/future use, but per the
  earlier redesign the tag pills are no longer rendered on the listing page
  — the editor still exposes it as a simple tag-input for completeness,
  clearly labeled "internal / not currently displayed".)

### 8.16 FAQs (`faqs.php` + `page_faqs.php`)

Two tabs in one module:

- **Site-wide fallback** — the `faqs[]` list used when a page has no
  specific set.
- **Per-page** — a sub-tab per route (Home, Products, Services, About,
  Blogs, Contact), each its own q/a list. Deleting all FAQs for a page
  reverts that page to the site-wide fallback automatically (matches the
  existing template logic in `faq-carousel.php` exactly — no special-casing
  needed).

Each entry: q (required, ≤150), a (required, ≤500).

### 8.17 Category select lists everywhere

Any field that references a category (Products.category, Contact form's
interest dropdown indirectly via Categories, Blogs.cta_category, Reviews'
implicit product→category chain) is rendered as a `<select>` populated live
from the current Categories list — never a free-text field — so it's
structurally impossible to reference a category that doesn't exist.

### 8.18 Page Copy Blocks (`page_content.php`) — NEW domain

This is the piece that doesn't exist yet in `$DATA` at all. Migrating these
hardcoded template strings into an editable array:

| Key | Fields | Currently hardcoded in |
|---|---|---|
| `about.story` | eyebrow, title, lead, body, bullets[] | `pages/about.php` "Our Story" section |
| `about.pillars` | 3× {icon, title, desc} (Mission/Vision/Promise) | `pages/about.php` |
| `about.cta` | title, sub | `pages/about.php` CTA band |
| `home.why_us_editorial` | eyebrow, title, lead, bullets[] (the split-image "8 Years, One In-House Crew" block — distinct from the `why_us[]` grid on the About page) | `pages/home.php` |
| `home.quote_section` | eyebrow, title, lede | `pages/home.php` "Request a Free Quote" intro |
| `services.cta` | title, sub | `pages/services.php` CTA band |
| `contact.hero` | eyebrow, title, sub | `pages/contact.php` hero |

Each is a small, purpose-specific form (not a generic key-value text blob) —
grouped in the sidebar under **Site → Page Copy** with one sub-section per
row above. This is the last piece needed before the claim "everything
dynamic reflects in the admin panel" is actually true end-to-end.

### 8.19 Users / Change Password (`admin/config/admin-users.php`)

Change-password form: current password (verified), new password (confirmed
twice, minimum 10 characters, checked against a small deny-list of
obviously-weak values like `password`/`admin123` rather than a heavyweight
complexity policy). Optional "Add another admin" form, same rules, for
teams larger than one editor.

---

## 9. Migration & Rollout Plan (phased)

Building all of Section 8 at once is a lot of surface area to get right in
one pass. Suggested phases — each one leaves the site in a fully working
state, so work can pause between phases without anything being half-broken:

| Phase | Delivers | Depends on |
|---|---|---|
| **0a — Asset split** | `style.css` → `base.css` + 8 page files, `main.js` → `core.js` + 3 page files (Section 4.3), `$gd_page_assets` wiring in `meta.php`/`footer.php`, full visual re-verification of every page at mobile + desktop | nothing — can start immediately, independent of the rest |
| **0b — Foundation** | `data/content/*.php` split + `Store` class, `data.php` migrated to loaders, login/logout/session guard, admin shell (sidebar/topbar/flash) using `admin-core.css`/`admin-core.js`, dashboard (counts + quick links) | nothing |
| **1 — Site-wide** | Settings, Navigation, Quote Popup, Festival Banner, Page Copy Blocks | Phase 0b |
| **2 — Catalogue** | Categories, Products (incl. image upload/picker), Landing Pages | Phase 0b |
| **3 — Content sections** | Services + Service Details, Process Steps, Why Us, Team, Stats, Clients | Phase 0b |
| **4 — Blog** | Blogs (full editor incl. gallery/FAQ/references sub-sections) | Phase 0b, 2 (category select) |
| **5 — FAQ & Reviews** | Site-wide + per-page FAQs, Reviews/testimonials | Phase 0b, 2 (product select) |
| **6 — Polish & hardening** | Change password / add-admin, backup/restore UI, login throttling, final `.htaccess`/robots hardening pass, full validation review | all prior phases |

Each phase is independently shippable and testable — nothing in a later
phase is required for the public site or an earlier admin module to keep
working.

---

## 10. Testing & QA Checklist (per module, repeated each phase)

- [ ] Add a new record → appears correctly on the live public page.
- [ ] Edit a record → change reflects immediately (no cache to clear).
- [ ] Delete a record → removed from the live page; blocked cleanly if
      something else still references it (e.g. a category with products).
- [ ] Reorder → new order reflected on the live page.
- [ ] Submit the edit form with every required field blank → inline errors,
      no partial save, previously-entered values preserved for the fields
      that *were* filled in.
- [ ] Submit a duplicate slug → rejected with a clear message.
- [ ] Upload an oversized / wrong-type file → rejected before touching disk.
- [ ] Kill the request mid-save (simulate) → the content file is never left
      truncated/corrupted (atomic rename guarantees this) and a backup
      exists to restore from regardless.
- [ ] Log out, then hit an admin URL directly → redirected to login.
- [ ] Submit any admin form with a stale/missing CSRF token → rejected.
- [ ] 6 failed logins in a row → 7th attempt is blocked even with the
      correct password, for the lockout window.

---

## 11. Non-Goals (explicitly out of scope for this plan)

- **No role/permission tiers** beyond "is an admin" — every admin user can
  edit everything. (Multi-role access can be a later addition if needed.)
- **No multi-language / i18n** — content stays single-language, matching
  the current site.
- **No revision/version history UI beyond the backup-and-restore
  mechanism** in 5.5 — this isn't a full CMS version-control system, just
  enough safety net to undo a bad save.
- **Button/label chrome text** ("Read article", "View Product →", etc.)
  stays in templates — only page *content* (the words specific to Green
  Divine's business) becomes admin-editable, not generic UI microcopy. This
  keeps the scope to "content a business owner would actually want to
  change," not "every string in the codebase."
- **No WYSIWYG page-builder / drag-drop section reordering** — the admin
  edits the *data* each section already renders from; it doesn't let an
  admin rearrange which sections exist on a page or add brand-new section
  types. That would be a much larger, different project.

---

## 12. Decisions Needed From You Before Implementation Starts

1. **Seed admin username** — what should the first account be called
   (e.g. `admin`, or a real name)? A random initial password will be
   generated and shown once during setup either way.
2. **Image handling for v1** — is the picker (browse existing
   `assets/images/*`) + external-URL paste enough for the first phase, or
   is file **upload** a must-have from day one? (Both are in this plan;
   upload is more work to get validation right, so confirming priority
   helps sequence Phase 2.)
3. **Single admin vs. multiple** — is this genuinely one person, or should
   Phase 6's "add another admin" be pulled forward/prioritized?
4. **Hosting write permissions** — this will run on the same PHP host as
   the public site; confirm the web server user can write to `data/` and
   `assets/images/` (this is normally true on shared PHP hosting, but worth
   a quick check on wherever this ultimately deploys, since XAMPP locally
   will differ from the eventual production host).

---

## 13. File Manifest (everything new, for reference once implementation starts)

```
admin/index.php, login.php, logout.php
admin/config/admin-users.php
admin/inc/auth.php, csrf.php, store.php, validate.php, uploads.php,
          layout-top.php, layout-bottom.php
admin/modules/settings.php, quote-popup.php, festival.php, nav.php,
              hero-slides.php, categories.php, landing-pages.php,
              products.php, product-edit.php, services.php,
              service-details.php, process-steps.php, why-us.php,
              team.php, stats.php, clients.php, certifications.php,
              reviews.php, blogs.php, blog-edit.php, faqs.php,
              page-content.php, users.php
admin/assets/css/admin-core.css, login.css, products.css, blogs.css,
                 landing-pages.css, dashboard.css
admin/assets/js/admin-core.js, products.js, blogs.js, landing-pages.js
admin/bin/migrate.php          (one-time, CLI-only, not web-exposed)

data/content/*.php             (20 files, see Section 4.1)
data/backups/                  (created empty, populated on first save)
data/security/login-attempts.php

.htaccess additions            (deny-all inside data/, admin/config/, admin/inc/)
robots.txt                     (Disallow: /admin/)

# --- existing-site asset split (Section 4.3 — Phase 0a, independent of the
#     admin console itself) ---
assets/css/base.css, home.css, products.css, product-single.css,
                 category.css, about.css, contact.css, blogs.css,
                 blog-single.css                    (replaces style.css)
assets/js/core.js, home.js, products.js, blog-single.js
                                                     (replaces main.js)
```

`data.php` itself shrinks from ~970 lines of array literals to a short file
of `require`s plus its existing helper functions — no template file
(`pages/*.php`, `includes/*.php`) needs to change at all, since `$DATA`'s
shape is identical before and after. `style.css`/`main.js` are retired in
favor of the split files above once Phase 0a's re-verification passes.
