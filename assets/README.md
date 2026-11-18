# /assets/ — drop your local files here

The site uses **no CDNs**. Everything must be served from this folder.

## What to download into each subfolder

| Folder | What goes here | Where to get it |
|---|---|---|
| `vendor/bootstrap-5.3.3/bootstrap.min.css` | Bootstrap 5 compiled CSS | https://getbootstrap.com/docs/5.3/getting-started/download/ |
| `vendor/bootstrap-5.3.3/bootstrap.bundle.min.js` | Bootstrap 5 bundled JS (includes Popper) | same as above |
| `vendor/bootstrap-icons-1.11.3/bootstrap-icons.css` + `fonts/bootstrap-icons.woff2` | Bootstrap Icons font + CSS | https://icons.getbootstrap.com/#install (download release zip) |
| `fonts/` | A web font of your choice (e.g. Poppins) — both the .woff2 files and a small `fonts.css` that `@font-face`s them | https://gwfh.mranftl.com/fonts (download `Poppins`) |
| `images/logo/logo.png` + `favicon.png` | Brand logo (square, transparent) + favicon | your designer |
| `images/hero/hero-1.jpg`, `hero-2.jpg`, `hero-3.jpg` | Home page hero slides (≥1600 × 900) | photo shoots / stock |
| `images/categories/cat-*.jpg` | One image per category (filenames defined in `data.php`) | — |
| `images/products/*.jpg` | Product gallery shots (filenames in `data.php`) | — |
| `images/installations/inst-*.jpg` | "Completed Installations" gallery shots per product | — |
| `images/clients/client-*.png` | Client logos for the marquee | — |
| `images/blog/blog-*.jpg` | Blog thumbnails | — |
| `images/about/about-hero.jpg` | About-page hero image | — |
| `images/og-default.jpg` | Default social-share image (1200 × 630) | — |

## Loading order (already wired into `includes/header.php` and `footer.php`)

```html
<head>
  <link rel="stylesheet" href="/assets/vendor/bootstrap-5.3.3/bootstrap.min.css?v=…">
  <link rel="stylesheet" href="/assets/vendor/bootstrap-icons-1.11.3/bootstrap-icons.css?v=…">
  <link rel="stylesheet" href="/assets/css/style.css?v=…">
</head>
…
<script src="/assets/vendor/bootstrap-5.3.3/bootstrap.bundle.min.js?v=…"></script>
<script src="/assets/js/main.js?v=…"></script>
```

## Why vendor folders carry the version number

A previous deploy left a Bootstrap **4** `bootstrap.min.css` sitting at the old
unversioned path on the live server. The upload never overwrote it, every
request still returned `200`, and the site quietly rendered without
`.offcanvas`, `.visually-hidden-focusable` or `.form-select`. Putting the
version in the folder name means a stale copy can never occupy the path a
newer build uses — a missed upload becomes an obvious `404` instead.

**Upgrading Bootstrap therefore means:** create a new
`assets/vendor/bootstrap-<new-version>/` folder, update the three `gd_asset()`
paths in `includes/meta.php`, `includes/footer.php`, `admin/inc/layout-top.php`,
`admin/inc/layout-bottom.php` and `admin/login.php`, update
`tools/asset-list.php`, then run `php tools/build-asset-manifest.php`.
Delete the old folder only after the new one is live.

## Never edit assets straight on the server

`?v=` fingerprints come from the *deployed* file's mtime and size, and
`data/asset-manifest.php` records the size + SHA-256 of every shipped asset.
Edit here, rebuild the manifest, deploy both — see `docs/DEPLOYMENT.md`.

## Adding the font (optional but recommended)

Create `assets/fonts/fonts.css`:

```css
@font-face {
  font-family: 'Poppins';
  src: url('poppins-regular.woff2') format('woff2');
  font-weight: 400; font-style: normal; font-display: swap;
}
@font-face {
  font-family: 'Poppins';
  src: url('poppins-600.woff2') format('woff2');
  font-weight: 600; font-style: normal; font-display: swap;
}
```

Then add `<link rel="stylesheet" href="/assets/fonts/fonts.css">` near the top of `includes/meta.php`.
