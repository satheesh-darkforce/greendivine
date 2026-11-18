<?php /* 404 — friendly not-found */ ?>
<section class="py-5 text-center">
  <div class="container" style="max-width: 560px;">
    <i class="bi bi-flower2" style="font-size:4rem;color:var(--gd-green-500)"></i>
    <h1 class="gd-section-title mt-3">Page Not <span>Found</span></h1>
    <p class="text-muted">The page you're looking for has moved or never existed.</p>
    <a class="btn btn-gd-primary mt-2" href="<?= e(gd_url('/')) ?>"><i class="bi bi-house-fill me-1"></i> Back to Home</a>
  </div>
</section>
