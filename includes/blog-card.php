<?php
/**
 * Reusable blog listing item — image-forward overlay tile.
 * Expects: $blog (array) — a single item from $DATA['blogs'].
 *
 * Rendered in a loop by pages/blogs.php. Isolated so the same tile can be
 * reused anywhere a blog list is shown (tag pages, search, related, etc.).
 */
if (!isset($blog) || !is_array($blog)) return;
$url = gd_url('/blog/' . $blog['slug']);
?>
<article class="gd-blog-tile gd-reveal">
  <a class="gd-blog-tile-link" href="<?= e($url) ?>" aria-label="<?= e($blog['title']) ?>">
    <img class="gd-blog-tile-img" src="<?= e(gd_img($blog['image'], 'blog')) ?>"
         alt="<?= e($blog['title']) ?>" loading="lazy">

    <?php if (!empty($blog['read_min'])): ?>
      <span class="gd-blog-tile-read"><i class="bi bi-clock"></i> <?= (int)$blog['read_min'] ?> min</span>
    <?php endif; ?>

    <div class="gd-blog-tile-body">
      <h3 class="gd-blog-tile-title"><?= e($blog['title']) ?></h3>

      <div class="gd-blog-tile-meta">
        <span><i class="bi bi-calendar3"></i> <?= e(date('M j, Y', strtotime($blog['date']))) ?></span>
        <span class="gd-blog-tile-go">Read <i class="bi bi-arrow-right"></i></span>
      </div>
    </div>
  </a>
</article>
