<?php
/**
 * Reusable footer — SEO links, contact info, socials.
 * Closes <main>, then includes Bootstrap JS + custom JS at the bottom.
 */
$site = $DATA['site'];
?>
</main><!-- /#main -->

<footer class="gd-footer">
  <div class="container">
    <div class="row gy-4">

      <div class="col-lg-4">
        <a class="d-flex align-items-center gap-2 text-white text-decoration-none" href="<?= e(gd_url('/')) ?>">
          <span class="gd-footer-logo">
            <img src="<?= e(gd_img($DATA['config']['logo'])) ?>" alt="Green Divine logo" height="38" width="48">
          </span>
          <span class="h5 mb-0"><?= e($site['name']) ?></span>
        </a>
        <p class="mt-3 mb-0 small">
          <?= e($site['tagline']) ?>. Bangalore's trusted partner for
          artificial grass, vertical gardens & landscaping.
        </p>
        <ul class="list-inline mt-3 gd-socials">
          <?php foreach (gd_socials($site['socials']) as $network => $url): ?>
            <li class="list-inline-item"><a href="<?= e($url) ?>" target="_blank" rel="noopener" aria-label="<?= e(ucfirst($network)) ?>"><i class="bi bi-<?= e($network) ?>"></i></a></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div class="col-6 col-lg-2">
        <h6 class="gd-foot-h">Explore</h6>
        <ul class="list-unstyled gd-foot-list">
          <li><a href="<?= e(gd_url('/')) ?>">Home</a></li>
          <li><a href="<?= e(gd_url('/products')) ?>">Products</a></li>
          <li><a href="<?= e(gd_url('/services')) ?>">Services</a></li>
          <li><a href="<?= e(gd_url('/about')) ?>">About</a></li>
          <li><a href="<?= e(gd_url('/blogs')) ?>">Blogs</a></li>
          <li><a href="<?= e(gd_url('/contact')) ?>">Contact</a></li>
        </ul>
      </div>

      <div class="col-6 col-lg-3">
        <h6 class="gd-foot-h">Categories</h6>
        <ul class="list-unstyled gd-foot-list">
          <?php foreach ($DATA['categories'] as $c): ?>
            <li><a href="<?= e(gd_url('/' . $c['slug'])) ?>"><?= e($c['name']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div class="col-lg-3">
        <h6 class="gd-foot-h">Reach Us</h6>
        <address class="small mb-0">
          <i class="bi bi-geo-alt-fill me-2"></i><?= e($site['address']) ?><br>
          <i class="bi bi-telephone-fill me-2"></i><a href="tel:<?= e($site['phone']) ?>"><?= e($site['phone_label']) ?></a><br>
          <i class="bi bi-envelope-fill me-2"></i><a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a>
        </address>
      </div>

    </div>

    <hr class="my-4 border-light opacity-25">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small gap-2">
      <p class="mb-2 mb-md-0">&copy; <?= date('Y') ?> <?= e($site['name']) ?>. All rights reserved.
        &middot; <a href="<?= e(gd_url('/privacy-policy')) ?>" class="text-white-50">Privacy Policy</a>
        &middot; <a href="<?= e(gd_url('/terms')) ?>" class="text-white-50">Terms of Service</a>
      </p>
      <p class="mb-0">Made with <i class="bi bi-heart-fill text-success"></i> in <?= e($site['location']) ?></p>
    </div>
  </div>
</footer>

<!-- Local JS (NO CDNs) -->
<script src="<?= e(gd_asset('/assets/vendor/bootstrap-5.3.3/bootstrap.bundle.min.js')) ?>"></script>
<script src="<?= e(gd_asset('/assets/js/main.js')) ?>"></script>

<?php /* Deploy guard: gd_asset() flags any CSS/JS that is missing or the
         wrong size on this server. Surfaced as a source comment plus a
         console error — invisible to visitors, impossible to miss for
         anyone looking at why the page renders wrong. Already logged to
         the PHP error log by gd_asset_flag(). */ ?>
<?php if ($gdAssetProblems = gd_asset_problems()): ?>
<!-- GREEN DIVINE DEPLOY WARNING
<?php foreach ($gdAssetProblems as $gdRel => $gdWhy): ?>
  * <?= e($gdRel . ' - ' . $gdWhy) . "\n" ?>
<?php endforeach; ?>
  Fix: re-upload these files, then run tools/asset-check.php on the server.
-->
<script>
console.error('[Green Divine] Broken deploy - these assets are missing or stale on the server:\n' +
  <?= json_encode(array_map(fn($k, $v) => "  \u{2022} $k - $v", array_keys($gdAssetProblems), $gdAssetProblems), JSON_UNESCAPED_SLASHES) ?>.join('\n'));
</script>
<?php endif; ?>
</body>
</html>
