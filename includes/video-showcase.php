<?php
/**
 * Reusable "See it installed" video strip — short, muted walkthroughs of
 * real installs. Used by product-single.php and category.php.
 *
 * Expects: $videos (array) — list of ['file' => 'name.mp4', 'title' => '...'].
 *   Files live in assets/videos/; each has a same-named .jpg poster beside it.
 * Optional: $videosTitle, $videosLede — section heading overrides.
 *
 * Nothing downloads until a card scrolls into view (preload="none"); main.js
 * ([data-gd-video]) then plays it muted and pauses it again off-screen.
 */
if (empty($videos)) return;
$videosTitle = $videosTitle ?? 'See it <span>installed</span>';
$videosLede  = $videosLede  ?? 'Unedited walkthroughs filmed on our Bangalore sites.';
?>
<section class="py-5 gd-videos" aria-labelledby="gd-videos-title">
  <div class="container">
    <div class="text-center mb-4 gd-reveal">
      <span class="gd-eyebrow">Real Videos</span>
      <h2 id="gd-videos-title" class="gd-section-title"><?= $videosTitle /* trusted markup */ ?></h2>
      <p class="text-muted mb-0"><?= e($videosLede) ?></p>
    </div>

    <div class="gd-video-grid">
      <?php foreach ($videos as $v):
        $src    = gd_url('/assets/videos/' . $v['file']);
        $poster = gd_url('/assets/videos/' . preg_replace('/\.mp4$/i', '.jpg', $v['file']));
      ?>
        <figure class="gd-video-card gd-reveal">
          <video data-gd-video muted loop playsinline preload="none"
                 poster="<?= e($poster) ?>" aria-label="<?= e($v['title']) ?>">
            <source src="<?= e($src) ?>" type="video/mp4">
          </video>
          <figcaption><i class="bi bi-play-circle-fill"></i> <?= e($v['title']) ?></figcaption>
        </figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>
