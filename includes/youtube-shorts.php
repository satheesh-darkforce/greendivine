<?php
/**
 * YouTube Shorts strip — used by category.php when a landing page has a
 * 'youtube_shorts' list (data/landing/<slug>.php, editable in admin).
 *
 * Expects: $shorts (array) — list of ['id' => '<11-char YouTube id>', 'title' => '...'].
 * Optional: $shortsTitle, $shortsLede — section heading overrides.
 *
 * Renders nothing when the list is empty, so the section only appears on
 * landing pages that actually have Shorts.
 *
 * Speed: embedding the YouTube player up front costs roughly 1 MB of script
 * per video, so ten Shorts made the page crawl. Each card is only a
 * lazy-loaded thumbnail (~10-40 KB) linking to the Short; main.js
 * ([data-gd-yt]) swaps in the real player only for the card that's tapped.
 * Without JS the link simply opens the Short on YouTube.
 */
$shorts = array_values(array_filter($shorts ?? [], fn($s) => preg_match('/^[A-Za-z0-9_-]{11}$/', $s['id'] ?? '')));
if (!$shorts) return;
$shortsTitle = $shortsTitle ?? 'Watch our <span>project videos</span>';
$shortsLede  = $shortsLede  ?? 'Tap any video to play it right here.';
?>
<section class="py-5 gd-videos" aria-labelledby="gd-shorts-title">
  <div class="container">
    <div class="text-center mb-4 gd-reveal">
      <span class="gd-eyebrow">YouTube Shorts</span>
      <h2 id="gd-shorts-title" class="gd-section-title"><?= $shortsTitle /* trusted markup */ ?></h2>
      <p class="text-muted mb-0"><?= e($shortsLede) ?></p>
    </div>

    <div class="gd-video-grid gd-yt-grid">
      <?php foreach ($shorts as $s): $title = trim($s['title'] ?? '') ?: 'Green Divine project video'; ?>
        <figure class="gd-video-card gd-yt-card gd-reveal">
          <a href="https://www.youtube.com/shorts/<?= e($s['id']) ?>" target="_blank" rel="noopener"
             data-gd-yt="<?= e($s['id']) ?>" aria-label="Play video: <?= e($title) ?>">
            <img src="https://i.ytimg.com/vi/<?= e($s['id']) ?>/hqdefault.jpg" alt="<?= e($title) ?>"
                 width="480" height="360" loading="lazy" decoding="async">
            <span class="gd-yt-play" aria-hidden="true"></span>
          </a>
          <figcaption><i class="bi bi-youtube"></i> <?= e($title) ?></figcaption>
        </figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>
