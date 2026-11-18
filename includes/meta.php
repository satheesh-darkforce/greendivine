<?php
/**
 * Outputs the <head> meta tags from $pageMeta and $DATA['site'].
 * Included by includes/header.php.
 */
$site     = $DATA['site'];
$title    = $pageMeta['title']       ?? $site['name'];
$desc     = $pageMeta['description'] ?? $site['tagline'];
$canon    = $pageMeta['canonical']   ?? $site['base_url'];
$ogImage  = $pageMeta['image']       ?? ($DATA['config']['og_default'] ?? '');
// If $ogImage is absolute already, use as-is; else prefix base_url so
// social crawlers get a fully-qualified URL.
$ogAbsolute = preg_match('#^https?://#i', $ogImage) ? $ogImage : $site['base_url'] . $ogImage;
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#2e7d32">

<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<link rel="canonical" href="<?= e($canon) ?>">

<!-- Open Graph -->
<meta property="og:type"        content="website">
<meta property="og:site_name"   content="<?= e($site['name']) ?>">
<meta property="og:title"       content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:url"         content="<?= e($canon) ?>">
<meta property="og:image"       content="<?= e($ogAbsolute) ?>">

<!-- Twitter -->
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="<?= e($title) ?>">
<meta name="twitter:description" content="<?= e($desc) ?>">
<meta name="twitter:image"       content="<?= e($ogAbsolute) ?>">

<!-- Local assets (NO CDNs) -->
<link rel="icon" type="image/png" href="<?= e(gd_img($DATA['config']['logo'])) ?>">
<link rel="stylesheet" href="<?= e(gd_asset('/assets/vendor/bootstrap-5.3.3/bootstrap.min.css')) ?>">
<link rel="stylesheet" href="<?= e(gd_asset('/assets/vendor/bootstrap-icons-1.11.3/bootstrap-icons.css')) ?>">
<link rel="stylesheet" href="<?= e(gd_asset('/assets/css/style.css')) ?>">

<!-- JSON-LD Organization -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "<?= e($site['name']) ?>",
  "url":  "<?= e($site['base_url']) ?>",
  "logo": "<?= e(gd_img($DATA['config']['logo'])) ?>",
  "address": { "@type": "PostalAddress", "addressLocality": "Bangalore", "addressCountry": "IN" },
  "contactPoint": [{
      "@type": "ContactPoint",
      "telephone": "<?= e($site['phone']) ?>",
      "contactType": "customer service"
  }]
}
</script>

<?php
/**
 * Analytics — opt-in only. Nothing loads unless an ID is actually
 * configured (Settings > Analytics), so a fresh install never phones
 * home anywhere. GA4/Meta Pixel both require loading their own script
 * from Google's/Meta's servers by design (that's how the tracking data
 * reaches them) — the only "no CDN" exception on this site, and only
 * when the site owner deliberately turns it on.
 */
$gaId  = trim($DATA['config']['google_analytics_id'] ?? '');
$fbId  = trim($DATA['config']['meta_pixel_id'] ?? '');
?>
<?php if ($gaId !== ''): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($gaId) ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?= e($gaId) ?>');
</script>
<?php endif; ?>
<?php if ($fbId !== ''): ?>
<script>
  !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
  n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
  n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
  t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
  document,'script','https://connect.facebook.net/en_US/fbevents.js');
  fbq('init', '<?= e($fbId) ?>');
  fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none" alt=""
  src="https://www.facebook.com/tr?id=<?= e($fbId) ?>&ev=PageView&noscript=1"/>
</noscript>
<?php endif; ?>
