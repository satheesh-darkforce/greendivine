<?php /* Single blog post */
$blog = $GLOBALS['blog'] ?? null;
if (!$blog) { include __DIR__ . '/404.php'; return; }

$shareUrl = $DATA['site']['base_url'] . '/blog/' . $blog['slug'];

$related = array_filter($DATA['blogs'], fn($b) => $b['slug'] !== $blog['slug']);
$related = array_slice($related, 0, 3);

/* "In this article" contents: give every <h2> in the body an id (keeping
   any it already has) and list them. Only shown for posts long enough to
   need it, so short posts stay uncluttered. */
$bodyHtml = (string)($blog['body'] ?? '');
$toc      = [];
$usedIds  = [];
$bodyHtml = preg_replace_callback('#<h2\b([^>]*)>(.*?)</h2>#is', function ($m) use (&$toc, &$usedIds) {
    $text = trim(html_entity_decode(strip_tags($m[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    if (preg_match('#\bid\s*=\s*["\']([^"\']+)["\']#i', $m[1], $idm)) {
        $id = $idm[1];
        $attrs = $m[1];
    } else {
        $base = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($text)), '-') ?: 'section';
        $id = $base; $n = 2;
        while (isset($usedIds[$id])) $id = $base . '-' . $n++;
        $attrs = $m[1] . ' id="' . $id . '"';
    }
    $usedIds[$id] = true;
    $toc[] = ['id' => $id, 'text' => $text];
    return '<h2' . $attrs . '>' . $m[2] . '</h2>';
}, $bodyHtml);
$showToc = count($toc) >= 4;
// Root-relative links/images in the body ("/assets/images/blog/x.jpg") get
// the site's base path, so they also resolve when installed in a subfolder.
$basePath = rtrim(gd_url('/'), '/');
if ($basePath !== '') {
    $bodyHtml = preg_replace('#(\s(?:src|href)=["\'])/(?!/)#i', '$1' . $basePath . '/', $bodyHtml);
}

$ctaCategory = null;
if (!empty($blog['cta_category'])) {
    foreach ($DATA['categories'] as $c) {
        if ($c['slug'] === $blog['cta_category']) { $ctaCategory = $c; break; }
    }
}
?>

<article class="gd-blog-article py-5">
  <div class="container" style="max-width: 920px;">

    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= e(gd_url('/')) ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?= e(gd_url('/blogs')) ?>">Blog</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= e($blog['title']) ?></li>
      </ol>
    </nav>

    <header class="mb-4 gd-reveal">
      <?php if (!empty($blog['tags'])): ?>
        <div class="d-flex flex-wrap gap-2 mb-2">
          <?php foreach ($blog['tags'] as $tag): ?>
            <span class="gd-blog-tag"><?= e($tag) ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <h1 class="gd-blog-h1"><?= e($blog['title']) ?></h1>
      <div class="d-flex flex-wrap align-items-center gap-3 mt-3 text-muted small">
        <span><i class="bi bi-calendar3"></i> <?= e(date('F j, Y', strtotime($blog['date']))) ?></span>
        <?php if (!empty($blog['read_min'])): ?>
          <span><i class="bi bi-clock"></i> <?= (int)$blog['read_min'] ?> min read</span>
        <?php endif; ?>
      </div>
    </header>

    <div class="gd-blog-hero gd-reveal mb-4">
      <img src="<?= e(gd_img($blog['image'], 'blog')) ?>" alt="<?= e($blog['title']) ?>">
    </div>

    <!-- Social share -->
    <div class="gd-share d-flex flex-wrap align-items-center gap-2 mb-4 gd-reveal">
      <strong class="me-1">Share:</strong>
      <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener"
         href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($shareUrl) ?>">
        <i class="bi bi-facebook"></i> Facebook
      </a>
      <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener"
         href="https://twitter.com/intent/tweet?url=<?= urlencode($shareUrl) ?>&text=<?= urlencode($blog['title']) ?>">
        <i class="bi bi-twitter-x"></i> Twitter
      </a>
      <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener"
         href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($shareUrl) ?>">
        <i class="bi bi-linkedin"></i> LinkedIn
      </a>
      <a class="btn btn-sm btn-outline-success" target="_blank" rel="noopener"
         href="https://wa.me/?text=<?= urlencode($blog['title'] . ' — ' . $shareUrl) ?>">
        <i class="bi bi-whatsapp"></i> WhatsApp
      </a>
    </div>

    <?php if (!empty($blog['tldr'])): ?>
      <aside class="gd-tldr gd-reveal">
        <span class="gd-tldr-tag">TL;DR</span>
        <p><?= e($blog['tldr']) ?></p>
      </aside>
    <?php endif; ?>

    <?php if ($showToc): ?>
      <details class="gd-blog-toc gd-reveal" data-gd-toc open>
        <summary><i class="bi bi-list-ul"></i> In this article <span><?= count($toc) ?> sections</span></summary>
        <ol>
          <?php foreach ($toc as $t): ?>
            <li><a href="#<?= e($t['id']) ?>"><?= e($t['text']) ?></a></li>
          <?php endforeach; ?>
        </ol>
      </details>
    <?php endif; ?>

    <!-- Body -->
    <div class="gd-blog-body">
      <?= $bodyHtml /* trusted HTML from $DATA, with heading ids added */ ?>
    </div>

    <?php if (!empty($blog['pull_quote']['text'])): ?>
      <blockquote class="gd-pull-quote gd-reveal">
        <p>&ldquo;<?= e($blog['pull_quote']['text']) ?>&rdquo;</p>
        <?php if (!empty($blog['pull_quote']['author'])): ?>
          <cite>— <?= e($blog['pull_quote']['author']) ?></cite>
        <?php endif; ?>
      </blockquote>
    <?php endif; ?>

    <?php if (!empty($blog['gallery'])): ?>
      <section class="gd-blog-gallery gd-reveal" aria-label="Photo gallery">
        <?php foreach ($blog['gallery'] as $img): ?>
          <figure>
            <img src="<?= e(gd_img($img['src'], 'blog')) ?>" alt="<?= e($img['caption'] ?? $blog['title']) ?>" loading="lazy">
            <?php if (!empty($img['caption'])): ?>
              <figcaption><?= e($img['caption']) ?></figcaption>
            <?php endif; ?>
          </figure>
        <?php endforeach; ?>
      </section>
    <?php endif; ?>

    <?php if (!empty($blog['key_takeaways'])): ?>
      <section class="gd-takeaways gd-reveal" aria-labelledby="gd-takeaways-title">
        <h2 id="gd-takeaways-title" class="h5 mb-3">
          <i class="bi bi-bookmark-star-fill"></i> Key Takeaways
        </h2>
        <ul>
          <?php foreach ($blog['key_takeaways'] as $k): ?>
            <li><?= e($k) ?></li>
          <?php endforeach; ?>
        </ul>
      </section>
    <?php endif; ?>

    <?php if ($ctaCategory): ?>
      <aside class="gd-inline-cta gd-reveal">
        <div class="gd-inline-cta-media">
          <img src="<?= e(gd_img($ctaCategory['image'], 'categories')) ?>" alt="<?= e($ctaCategory['name']) ?>" loading="lazy">
        </div>
        <div class="gd-inline-cta-body">
          <span class="gd-eyebrow">Talk to us</span>
          <h3 class="h5 mt-2 mb-2">Ready to plan your <?= e(strtolower($ctaCategory['name'])) ?> project?</h3>
          <p class="text-muted mb-3"><?= e($ctaCategory['desc']) ?> Book a free site visit and get a transparent, written quote within 24 hours.</p>
          <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-gd-primary" href="<?= e(gd_url('/products/' . $ctaCategory['slug'])) ?>">
              View <?= e($ctaCategory['name']) ?> <i class="bi bi-arrow-right ms-1"></i>
            </a>
            <a class="btn btn-gd-outline" href="<?= e(gd_url('/contact')) ?>">
              <i class="bi bi-calendar-check"></i> Free site visit
            </a>
          </div>
        </div>
      </aside>
    <?php endif; ?>

    <?php if (!empty($blog['faqs'])): ?>
      <section class="gd-blog-faqs gd-reveal" aria-labelledby="gd-blog-faqs-title">
        <h2 id="gd-blog-faqs-title" class="gd-section-title mb-4">
          Frequently Asked <span>Questions</span>
        </h2>
        <div class="accordion gd-accordion" id="gdBlogFaq">
          <?php foreach ($blog['faqs'] as $i => $faq):
            $fid = 'bfaq-' . $i; ?>
            <div class="accordion-item">
              <h3 class="accordion-header">
                <button class="accordion-button <?= $i === 0 ? '' : 'collapsed' ?>" type="button"
                        data-bs-toggle="collapse" data-bs-target="#<?= e($fid) ?>"
                        aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>" aria-controls="<?= e($fid) ?>">
                  <?= e($faq['q']) ?>
                </button>
              </h3>
              <div id="<?= e($fid) ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>" data-bs-parent="#gdBlogFaq">
                <div class="accordion-body"><?= e($faq['a']) ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <?php if (!empty($blog['references'])): ?>
      <section class="gd-references gd-reveal" aria-labelledby="gd-refs-title">
        <h2 id="gd-refs-title" class="h5 mb-3">
          <i class="bi bi-link-45deg"></i> Further reading &amp; references
        </h2>
        <ul class="gd-refs-list">
          <?php foreach ($blog['references'] as $ref): ?>
            <li>
              <a href="<?= e($ref['url']) ?>" target="_blank" rel="external noopener">
                <?= e($ref['title']) ?>
                <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
              </a>
              <?php if (!empty($ref['source'])): ?>
                <span class="text-muted small">— <?= e($ref['source']) ?></span>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
        <p class="small text-muted mb-0">
          External links open in a new tab and pass authority (do-follow) to the cited sources.
        </p>
      </section>
    <?php endif; ?>

    <!-- Tags footer -->
    <?php if (!empty($blog['tags'])): ?>
      <div class="gd-blog-tag-foot gd-reveal">
        <strong class="me-2">Tagged:</strong>
        <?php foreach ($blog['tags'] as $tag): ?>
          <a class="gd-blog-tag" href="<?= e(gd_url('/blogs')) ?>"><?= e($tag) ?></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</article>

<!-- ==================== JSON-LD: BlogPosting + FAQPage ==================== -->
<?php
$ldImage = gd_img($blog['image'] ?? '', 'blog');
if (!preg_match('#^https?://#i', $ldImage)) $ldImage = $DATA['site']['base_url'] . $ldImage;
$ldPost = [
    '@context'         => 'https://schema.org',
    '@type'            => 'BlogPosting',
    'headline'         => $blog['title'],
    'description'      => ($blog['meta_desc'] ?? '') !== '' ? $blog['meta_desc'] : $blog['excerpt'],
    'image'            => [$ldImage],
    'datePublished'    => $blog['date'],
    'author'           => ['@type' => 'Organization', 'name' => $blog['author'] ?: $DATA['site']['name']],
    'publisher'        => ['@type' => 'Organization', 'name' => $DATA['site']['name']],
    'mainEntityOfPage' => $shareUrl,
];
if (!empty($blog['tags'])) $ldPost['keywords'] = implode(', ', $blog['tags']);
?>
<script type="application/ld+json"><?= json_encode($ldPost, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<?php if (!empty($blog['faqs'])): ?>
<script type="application/ld+json"><?= json_encode([
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => array_map(fn($f) => [
        '@type'          => 'Question',
        'name'           => $f['q'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
    ], array_values($blog['faqs'])),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<?php endif; ?>

<!-- ======================== RELATED POSTS ======================== -->
<?php if (!empty($related)): ?>
<section class="py-5 gd-bg-soft">
  <div class="container">
    <div class="text-center mb-4 gd-reveal">
      <h2 class="gd-section-title">Continue <span>reading</span></h2>
    </div>
    <div class="row g-4">
      <?php foreach ($related as $b): ?>
        <div class="col-md-6 col-lg-4 gd-reveal">
          <article class="gd-product-card h-100">
            <a class="gd-product-media" href="<?= e(gd_url('/blog/' . $b['slug'])) ?>" aria-label="<?= e($b['title']) ?>">
              <img src="<?= e(gd_img($b['image'], 'blog')) ?>" alt="<?= e($b['title']) ?>" loading="lazy">
            </a>
            <div class="gd-product-body">
              <span class="gd-product-cat"><?= e(date('M j, Y', strtotime($b['date']))) ?><?php if (!empty($b['read_min'])): ?> · <?= (int)$b['read_min'] ?> min<?php endif; ?></span>
              <h3 class="gd-product-title h6"><a href="<?= e(gd_url('/blog/' . $b['slug'])) ?>"><?= e($b['title']) ?></a></h3>
              <p class="gd-product-short small"><?= e($b['excerpt']) ?></p>
              <div class="gd-product-foot">
                <a class="btn btn-gd-outline btn-sm" href="<?= e(gd_url('/blog/' . $b['slug'])) ?>">Read</a>
              </div>
            </div>
          </article>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ====================== NEWSLETTER SIGNUP ====================== -->
<section class="py-5">
  <div class="container">
    <div class="gd-newsletter mx-auto text-center">
      <span class="gd-eyebrow">Stay Updated</span>
      <h2 class="gd-section-title mt-2">Like this post? Get more, monthly.</h2>
      <p class="text-muted">Join our friendly email — short, practical green-living tips.</p>
      <form class="d-flex flex-column flex-md-row justify-content-center gap-2 mt-3" data-gd-form
            data-gs-endpoint="<?= e($DATA['config']['google_sheet_endpoint']) ?>"
            data-source="newsletter-blog"
            data-fallback-email="<?= e($DATA['site']['email']) ?>"
            novalidate>
        <input class="form-control form-control-lg" type="email" name="email" placeholder="your@email.com" required style="max-width:380px;">
        <button class="btn btn-gd-primary btn-lg" type="submit">Subscribe</button>
      </form>
      <div class="small text-success mt-2 d-none" data-gd-success>Subscribed! See you in the inbox.</div>
    </div>
  </div>
</section>
