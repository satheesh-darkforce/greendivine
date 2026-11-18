<?php /* Blogs listing — magazine masthead + immersive featured + grid + newsletter */
$featured = $DATA['blogs'][0] ?? null;
$rest     = array_slice($DATA['blogs'], 1);
?>

<!-- ============================ MASTHEAD ============================ -->
<section class="gd-blog-masthead">
  <span class="gd-hero-blob gd-hero-blob-1" aria-hidden="true"></span>
  <span class="gd-hero-blob gd-hero-blob-2" aria-hidden="true"></span>
  <div class="container position-relative text-center">
    <span class="gd-eyebrow justify-content-center">The Journal</span>
    <h1 class="gd-blog-masthead-title">Notes on <span>greener living</span></h1>
    <p class="text-muted mx-auto mb-0" style="max-width:620px;">
      Trends, field-tested guides and honest tips on artificial grass, vertical gardens
      and biophilic design — from the team that installs them.
    </p>
  </div>
</section>

<!-- =========================== FEATURED (cover) =========================== -->
<?php if ($featured): ?>
<section class="pt-5">
  <div class="container">
    <a class="gd-blog-cover gd-reveal" href="<?= e(gd_url('/blog/' . $featured['slug'])) ?>"
       aria-label="<?= e($featured['title']) ?>">
      <img src="<?= e(gd_img($featured['image'], 'blog')) ?>" alt="<?= e($featured['title']) ?>" loading="lazy">
      <div class="gd-blog-cover-body">
        <span class="gd-blog-featured-flag mb-3"><i class="bi bi-star-fill"></i> Featured Story</span>
        <h2 class="gd-blog-cover-title"><?= e($featured['title']) ?></h2>
        <p class="gd-blog-cover-excerpt d-none d-md-block"><?= e($featured['excerpt']) ?></p>
        <div class="gd-blog-cover-meta">
          <span><i class="bi bi-calendar3"></i> <?= e(date('M j, Y', strtotime($featured['date']))) ?></span>
          <?php if (!empty($featured['read_min'])): ?>
            <span><i class="bi bi-clock"></i> <?= (int)$featured['read_min'] ?> min read</span>
          <?php endif; ?>
          <span class="gd-blog-cover-go">Read article <i class="bi bi-arrow-right"></i></span>
        </div>
      </div>
    </a>
  </div>
</section>
<?php endif; ?>

<!-- ====================== MORE STORIES (overlay mosaic) ====================== -->
<section class="py-5">
  <div class="container">
    <div class="text-center mb-5 gd-reveal">
      <span class="gd-eyebrow justify-content-center">More Stories</span>
      <h2 class="gd-section-title">From the <span>Journal</span></h2>
    </div>

    <?php if ($rest): ?>
      <div class="gd-blog-mosaic">
        <?php foreach ($rest as $b): ?>
          <?php
            // Reusable, array-mapped listing tile (includes/blog-card.php)
            $blog = $b;
            include __DIR__ . '/../includes/blog-card.php';
          ?>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="text-center text-muted mb-0">More stories are on the way — check back soon.</p>
    <?php endif; ?>
  </div>
</section>
