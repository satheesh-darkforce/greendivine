<?php
/**
 * Reusable site header (sticky + mobile hamburger).
 * Expects: $DATA (global), $pageMeta (array), $active (string route key).
 */
$active = $active ?? '';
?>
<!doctype html>
<html lang="en">
<head>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-5KFF8KNB');</script>
<!-- End Google Tag Manager -->
<!-- Sets the hidden pre-animation state (see .gd-js .gd-reveal in style.css)
     only when JS actually runs, so a blocked/failed script can never leave
     page content permanently invisible. -->
<script>document.documentElement.classList.add('gd-js');</script>
<?php require_once __DIR__ . '/meta.php'; ?>
</head>
<body>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5KFF8KNB"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<a href="#main" class="visually-hidden-focusable skip-link">Skip to main content</a>

<header class="gd-header sticky-top">
  <nav class="navbar navbar-expand-lg gd-nav" aria-label="Primary navigation">
    <div class="container">

      <a class="navbar-brand d-flex align-items-center gap-2" href="<?= e(gd_url('/')) ?>">
        <img src="<?= e(gd_img($DATA['config']['logo'])) ?>" alt="Green Divine logo" height="56" width="70" loading="eager">
        <span class="gd-brand-text">
          <strong><?= e($DATA['site']['name']) ?></strong>
          <small class="d-none d-md-block"><?= e($DATA['site']['tagline']) ?></small>
        </span>
      </a>

      <button class="navbar-toggler border-0" type="button"
              data-bs-toggle="offcanvas" data-bs-target="#gdNav"
              aria-controls="gdNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="gd-hamburger">
          <span></span><span></span><span></span>
        </span>
      </button>

      <!-- Plain .offcanvas (not .offcanvas-lg) on purpose: Bootstrap's
           data-bs-dismiss="offcanvas" close-button shortcut only matches
           the bare .offcanvas class. Combined with .navbar-expand-lg on
           the <nav> above, Bootstrap's own CSS already merges this back
           into an inline nav row at "lg" and up — see .gd-mobile-nav in
           style.css for the phone-only slide-in panel styling. -->
      <div class="offcanvas offcanvas-end gd-mobile-nav" tabindex="-1" id="gdNav" aria-labelledby="gdNavLabel">
        <div class="offcanvas-header">
          <a class="d-flex align-items-center gap-2 text-decoration-none gd-mobile-nav-brand" href="<?= e(gd_url('/')) ?>" id="gdNavLabel">
            <img src="<?= e(gd_img($DATA['config']['logo'])) ?>" alt="" height="34" width="43">
            <strong><?= e($DATA['site']['name']) ?></strong>
          </a>
          <button type="button" class="gd-mobile-nav-close" data-bs-dismiss="offcanvas" aria-label="Close menu">
            <svg viewBox="0 0 16 16" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
              <path d="M3 3 L13 13 M13 3 L3 13"/>
            </svg>
          </button>
        </div>
        <div class="offcanvas-body">
          <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
            <?php foreach ($DATA['nav'] as $i => $item): ?>
              <li class="nav-item" style="--gd-i: <?= (int)$i ?>">
                <a class="nav-link <?= $active === $item['route'] ? 'active' : '' ?>"
                   href="<?= e(gd_url($item['href'])) ?>"
                   <?= $active === $item['route'] ? 'aria-current="page"' : '' ?>>
                  <?= e($item['label']) ?>
                  <i class="bi bi-arrow-right gd-mobile-nav-arrow" aria-hidden="true"></i>
                </a>
              </li>
            <?php endforeach; ?>

            <li class="nav-item ms-lg-2 gd-mobile-nav-cta">
              <a class="btn btn-gd-gold w-100" href="<?= e(gd_url('/contact')) ?>">Get a Quote</a>
            </li>
          </ul>

          <div class="gd-mobile-nav-foot d-lg-none">
            <a href="tel:<?= e($DATA['site']['phone']) ?>" class="gd-mobile-nav-contact">
              <span class="gd-mobile-nav-icon"><i class="bi bi-telephone-fill"></i></span>
              <?= e($DATA['site']['phone_label']) ?>
            </a>
            <a href="https://wa.me/<?= e($DATA['site']['whatsapp']) ?>" target="_blank" rel="noopener" class="gd-mobile-nav-contact gd-mobile-nav-contact-wa">
              <span class="gd-mobile-nav-icon gd-mobile-nav-icon-wa"><i class="bi bi-whatsapp"></i></span>
              Chat on WhatsApp
            </a>
            <div class="gd-mobile-nav-socials-row">
              <span class="gd-mobile-nav-socials-label">Follow us</span>
              <ul class="list-inline gd-socials mb-0">
                <?php foreach (gd_socials($DATA['site']['socials']) as $network => $url): ?>
                  <li class="list-inline-item"><a href="<?= e($url) ?>" target="_blank" rel="noopener" aria-label="<?= e(ucfirst($network)) ?>"><i class="bi bi-<?= e($network) ?>"></i></a></li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        </div>
      </div>

    </div>
  </nav>
</header>

<main id="main">
