<?php
/** Landing Page editor — one file per category slug, data/landing/<slug>.php (Section 8.5). */
require __DIR__ . '/../inc/guard.php';

// Underscores are allowed: the '__sports' category slug uses them (same rule as gd_find_landing()).
$slug = preg_replace('/[^a-z0-9_\-]/', '', strtolower($_GET['slug'] ?? ''));
$category = gd_find_category($slug);
if (!$category) { flash('error', 'Unknown category.'); header('Location: ' . gd_admin_url('/modules/landing-pages.php')); exit; }

$landingFile = __DIR__ . '/../../data/landing/' . $slug . '.php';
$blankAdSlot = ['eyebrow' => '', 'title' => '', 'sub' => '', 'image' => '', 'cta' => ['label' => '', 'href' => '']];
$defaults = [
    'eyebrow' => '', 'h1' => '', 'hero_sub' => '', 'hero_image' => '', 'hero_alt' => '',
    'tagline' => '', 'meta_title' => '', 'meta_desc' => '', 'intro' => '',
    'hero_points' => [], 'benefits' => [], 'use_cases' => [], 'process' => [],
    'ad_slots' => [$blankAdSlot, $blankAdSlot], 'gallery' => [], 'stats' => [], 'faqs' => [],
];
$landing = is_file($landingFile) ? array_replace($defaults, Store::loadPath($landingFile)) : $defaults;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $v = new V();
    $in = [];

    foreach (['eyebrow', 'h1', 'hero_sub', 'hero_alt', 'tagline', 'meta_title', 'meta_desc'] as $f) {
        $in[$f] = trim((string)($_POST[$f] ?? ''));
    }
    $in['intro'] = trim((string)($_POST['intro'] ?? ''));
    $in['hero_image'] = (function () use ($landing) {
        $r = gd_resolve_image('hero_image', 'categories', $landing['hero_image']);
        return $r['ok'] ? ($r['name'] ?? '') : $landing['hero_image'];
    })();
    $in['hero_points'] = gd_lines_to_list($_POST['hero_points'] ?? '');

    foreach (['benefits', 'use_cases', 'process'] as $group) {
        $icons = (array)($_POST["{$group}_icon"] ?? []);
        $titles = (array)($_POST["{$group}_title"] ?? []);
        $descs = (array)($_POST["{$group}_desc"] ?? []);
        $rows = [];
        foreach ($icons as $i => $icon) {
            $icon = trim((string)$icon); $title = trim((string)($titles[$i] ?? '')); $desc = trim((string)($descs[$i] ?? ''));
            if ($icon !== '' || $title !== '' || $desc !== '') $rows[] = ['icon' => $icon, 'title' => $title, 'desc' => $desc];
        }
        $in[$group] = $rows;
    }

    $numbers = (array)($_POST['stat_number'] ?? []);
    $suffixes = (array)($_POST['stat_suffix'] ?? []);
    $labels = (array)($_POST['stat_label'] ?? []);
    $stats = [];
    foreach ($numbers as $i => $num) {
        $label = trim((string)($labels[$i] ?? ''));
        if ($label === '' && trim((string)$num) === '') continue;
        $stats[] = ['number' => (int)$num, 'suffix' => trim((string)($suffixes[$i] ?? '')), 'label' => $label];
    }
    $in['stats'] = $stats;

    $qs = (array)($_POST['faq_q'] ?? []);
    $as = (array)($_POST['faq_a'] ?? []);
    $faqs = [];
    foreach ($qs as $i => $q) {
        $q = trim((string)$q); $a = trim((string)($as[$i] ?? ''));
        if ($q !== '' && $a !== '') $faqs[] = ['q' => $q, 'a' => $a];
    }
    $in['faqs'] = $faqs;

    $in['gallery'] = gd_collect_image_list('gallery', $landing['gallery'], 'categories');

    $in['videos_above_about']  = !empty($_POST['videos_above_about']);
    $in['gallery_above_about'] = !empty($_POST['gallery_above_about']);
    $in['hero_quote_form']     = !empty($_POST['hero_quote_form']);
    // Only an Apps Script Web App URL (…/exec) is accepted for the quote form's own sheet.
    $in['quote_form_endpoint'] = trim((string)($_POST['quote_form_endpoint'] ?? ''));
    $in['show_reviews']        = !empty($_POST['show_reviews']);
    $in['reviews_above_about'] = !empty($_POST['reviews_above_about']);

    // YouTube Shorts: one per line, "<url or id> | <caption>". Lines without a
    // recognisable 11-character video id are dropped.
    $in['youtube_shorts'] = [];
    foreach (gd_lines_to_list($_POST['youtube_shorts'] ?? '') as $line) {
        [$ref, $title] = array_pad(array_map('trim', explode('|', $line, 2)), 2, '');
        if (preg_match('~(?:shorts/|embed/|youtu\.be/|[?&]v=)([A-Za-z0-9_-]{11})~', $ref, $m)
            || preg_match('~^([A-Za-z0-9_-]{11})$~', $ref, $m)) {
            $in['youtube_shorts'][] = ['id' => $m[1], 'title' => $title];
        }
    }

    $adSlots = [];
    for ($i = 0; $i < 2; $i++) {
        $img = (function () use ($i, $landing) {
            $prev = $landing['ad_slots'][$i]['image'] ?? '';
            $r = gd_resolve_image("ad_slot_{$i}_image", 'categories', $prev);
            return $r['ok'] ? ($r['name'] ?? '') : $prev;
        })();
        $adSlots[] = [
            'eyebrow' => trim((string)($_POST["ad_slot_{$i}_eyebrow"] ?? '')),
            'title'   => trim((string)($_POST["ad_slot_{$i}_title"] ?? '')),
            'sub'     => trim((string)($_POST["ad_slot_{$i}_sub"] ?? '')),
            'image'   => $img,
            'cta'     => [
                'label' => trim((string)($_POST["ad_slot_{$i}_cta_label"] ?? '')),
                'href'  => trim((string)($_POST["ad_slot_{$i}_cta_href"] ?? '')),
            ],
        ];
    }
    $in['ad_slots'] = $adSlots;

    $v->required('eyebrow', $in['eyebrow'], 'Eyebrow');
    $v->required('h1', $in['h1'], 'H1 Heading')->maxlen('h1', $in['h1'], 70, 'H1 Heading');
    $v->required('hero_sub', $in['hero_sub'], 'Hero Subtext');
    $v->required('hero_image', $in['hero_image'], 'Hero Image');
    $v->required('hero_alt', $in['hero_alt'], 'Hero Image Alt Text');
    $v->required('tagline', $in['tagline'], 'Tagline');
    $v->required('intro', $in['intro'], 'Intro');
    $v->minCount('hero_points', $in['hero_points'], 1, 'Hero Points');
    $v->minCount('benefits', $in['benefits'], 1, 'Benefits');
    if ($in['quote_form_endpoint'] !== ''
        && !preg_match('#^https://script\.google\.com/macros/s/[A-Za-z0-9_-]+/exec$#', $in['quote_form_endpoint'])) {
        $v->fail('quote_form_endpoint', 'Quote form Google Sheet URL must be an Apps Script Web App URL ending in /exec.');
    }

    if (!$v->ok()) {
        fail_back('/modules/landing-page-edit.php?slug=' . $slug, $v->errors(), array_merge($_POST, [
            'hero_points' => implode("\n", $in['hero_points']),
        ]));
    }

    $merged = array_replace($landing, $in);
    Store::savePath($landingFile, $merged, 'landing-' . $slug);
    gd_log_activity('Edited landing page', $category['name']);
    flash('success', 'Landing page for "' . $category['name'] . '" saved.');
    header('Location: ' . gd_admin_url('/modules/landing-page-edit.php?slug=' . $slug));
    exit;
}

$old    = old_input();
$errors = old_errors();
function lp_val($old, $landing, $key, $default = '') {
    if ($old !== null) return $old[$key] ?? $default;
    return $landing[$key] ?? $default;
}
function lp_lines($old, $landing, $key) {
    if ($old !== null) return (string)($old[$key] ?? '');
    $list = $landing[$key] ?? [];
    return is_array($list) ? implode("\n", $list) : (string)$list;
}
/** A dynamic add/remove row group for icon+title+desc triples (benefits, use_cases, process). */
function lp_icon_group(string $group, string $label, array $rows, string $hint = ''): void {
    ?>
    <div class="gd-admin-card">
      <h2><?= e($label) ?></h2>
      <?php if ($hint): ?><p class="gd-admin-card-hint"><?= e($hint) ?></p><?php endif; ?>
      <div data-gd-repeat>
        <div class="gd-repeat-list" data-gd-repeat-list>
          <?php foreach ($rows as $row): ?>
            <div class="gd-repeat-row">
              <div class="gd-repeat-row-fields">
                <input type="text" class="form-control form-control-sm" name="<?= e($group) ?>_icon[]" value="<?= e($row['icon'] ?? '') ?>" placeholder="Icon, e.g. bi-sun-fill">
                <input type="text" class="form-control form-control-sm" name="<?= e($group) ?>_title[]" value="<?= e($row['title'] ?? '') ?>" placeholder="Title">
                <input type="text" class="form-control form-control-sm" name="<?= e($group) ?>_desc[]" value="<?= e($row['desc'] ?? '') ?>" placeholder="Description">
              </div>
              <button type="button" class="gd-repeat-row-remove" data-gd-repeat-remove><i class="bi bi-x-lg"></i></button>
            </div>
          <?php endforeach; ?>
        </div>
        <template data-gd-repeat-template>
          <div class="gd-repeat-row">
            <div class="gd-repeat-row-fields">
              <input type="text" class="form-control form-control-sm" name="<?= e($group) ?>_icon[]" placeholder="Icon">
              <input type="text" class="form-control form-control-sm" name="<?= e($group) ?>_title[]" placeholder="Title">
              <input type="text" class="form-control form-control-sm" name="<?= e($group) ?>_desc[]" placeholder="Description">
            </div>
            <button type="button" class="gd-repeat-row-remove" data-gd-repeat-remove><i class="bi bi-x-lg"></i></button>
          </div>
        </template>
        <button type="button" class="btn btn-sm btn-outline-secondary gd-repeat-add" data-gd-repeat-add><i class="bi bi-plus-lg"></i> Add row</button>
      </div>
    </div>
    <?php
}

$adminTitle  = 'Landing Page — ' . $category['name'];
$adminActive = 'landing-pages';
require __DIR__ . '/../inc/layout-top.php';
?>
<div class="gd-admin-toolbar">
  <div><h1>Landing Page — <?= e($category['name']) ?></h1><p>Public URL: <code>/<?= e($slug) ?></code></p></div>
  <a href="<?= e(gd_admin_url('/modules/landing-pages.php')) ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to list</a>
</div>

<form method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>

  <div class="gd-admin-card">
    <h2>Hero</h2>
    <div class="gd-admin-field-row">
      <div class="mb-2">
        <label class="form-label">Eyebrow *</label>
        <input type="text" class="form-control <?= isset($errors['eyebrow']) ? 'is-invalid' : '' ?>" name="eyebrow" value="<?= e((string)lp_val($old, $landing, 'eyebrow')) ?>">
      </div>
      <div class="mb-2">
        <label class="form-label">H1 Heading * <span class="text-muted fw-normal">(≤70 chars)</span></label>
        <input type="text" class="form-control <?= isset($errors['h1']) ? 'is-invalid' : '' ?>" name="h1" value="<?= e((string)lp_val($old, $landing, 'h1')) ?>">
      </div>
      <div class="mb-2">
        <label class="form-label">Tagline *</label>
        <input type="text" class="form-control <?= isset($errors['tagline']) ? 'is-invalid' : '' ?>" name="tagline" value="<?= e((string)lp_val($old, $landing, 'tagline')) ?>">
      </div>
      <div class="mb-2">
        <label class="form-label">Hero Image Alt Text *</label>
        <input type="text" class="form-control <?= isset($errors['hero_alt']) ? 'is-invalid' : '' ?>" name="hero_alt" value="<?= e((string)lp_val($old, $landing, 'hero_alt')) ?>">
      </div>
    </div>
    <div class="mb-2">
      <label class="form-label">Hero Subtext *</label>
      <textarea class="form-control <?= isset($errors['hero_sub']) ? 'is-invalid' : '' ?>" name="hero_sub" rows="2"><?= e((string)lp_val($old, $landing, 'hero_sub')) ?></textarea>
    </div>
    <?php gd_image_field('hero_image', 'categories', (string)$landing['hero_image'], 'Hero Image *'); ?>
    <div class="mb-2 mt-2">
      <label class="form-label">Hero Points * <span class="text-muted fw-normal">(one per line)</span></label>
      <textarea class="form-control <?= isset($errors['hero_points']) ? 'is-invalid' : '' ?>" name="hero_points" rows="4"><?= e(lp_lines($old, $landing, 'hero_points')) ?></textarea>
      <?php if (isset($errors['hero_points'])): ?><div class="invalid-feedback"><?= e($errors['hero_points']) ?></div><?php endif; ?>
    </div>
    <?php $qf = $old ? !empty($old['hero_quote_form']) : !empty($landing['hero_quote_form']); ?>
    <div class="form-check mt-3">
      <input class="form-check-input" type="checkbox" name="hero_quote_form" value="1" id="hero_quote_form" <?= $qf ? 'checked' : '' ?>>
      <label class="form-check-label" for="hero_quote_form">Replace "Free Site Visit" with a "Get Quote" button that opens a quote form</label>
      <div class="form-text">The form asks for name, phone, location and need (the "need" options come from this page's Use Cases).</div>
    </div>
    <div class="mt-2 ms-4">
      <label class="form-label" for="quote_form_endpoint">Quote form's own Google Sheet URL <span class="text-muted fw-normal">(optional)</span></label>
      <input type="url" class="form-control <?= isset($errors['quote_form_endpoint']) ? 'is-invalid' : '' ?>" id="quote_form_endpoint" name="quote_form_endpoint" maxlength="300"
             placeholder="https://script.google.com/macros/s/…/exec"
             value="<?= e((string)($old['quote_form_endpoint'] ?? ($landing['quote_form_endpoint'] ?? ''))) ?>">
      <?php if (isset($errors['quote_form_endpoint'])): ?><div class="invalid-feedback"><?= e($errors['quote_form_endpoint']) ?></div><?php endif; ?>
      <div class="form-text">Apps Script Web App URL of a separate sheet for this page's quote form. Leave empty to use the site-wide sheet (Settings).</div>
    </div>
  </div>

  <div class="gd-admin-card">
    <h2>Intro *</h2>
    <textarea class="form-control <?= isset($errors['intro']) ? 'is-invalid' : '' ?>" name="intro" rows="4"><?= e((string)lp_val($old, $landing, 'intro')) ?></textarea>
    <div class="form-hint mt-1">Basic HTML (&lt;p&gt;, &lt;strong&gt;) is fine — rendered as-is.</div>
  </div>

  <?php lp_icon_group('benefits', 'Benefits *', $landing['benefits'], 'Shown as an icon grid — "Built-in Benefits".'); ?>
  <?php lp_icon_group('use_cases', 'Use Cases', $landing['use_cases'], 'Optional — section is hidden if empty.'); ?>
  <?php lp_icon_group('process', 'Process Override', $landing['process'], 'Optional — leave empty to hide the "How it works" section on this page entirely (it does NOT fall back to the global Process Steps).'); ?>

  <div class="gd-admin-card">
    <h2>Stats <span class="text-muted fw-normal">(optional, up to 4 shown)</span></h2>
    <div data-gd-repeat>
      <div class="gd-repeat-list" data-gd-repeat-list>
        <?php foreach ($landing['stats'] as $s): ?>
          <div class="gd-repeat-row">
            <div class="gd-repeat-row-fields">
              <input type="number" class="form-control form-control-sm" name="stat_number[]" value="<?= e((string)($s['number'] ?? '')) ?>" placeholder="Number">
              <input type="text" class="form-control form-control-sm" name="stat_suffix[]" value="<?= e($s['suffix'] ?? '') ?>" placeholder="Suffix, e.g. +">
              <input type="text" class="form-control form-control-sm" name="stat_label[]" value="<?= e($s['label'] ?? '') ?>" placeholder="Label">
            </div>
            <button type="button" class="gd-repeat-row-remove" data-gd-repeat-remove><i class="bi bi-x-lg"></i></button>
          </div>
        <?php endforeach; ?>
      </div>
      <template data-gd-repeat-template>
        <div class="gd-repeat-row">
          <div class="gd-repeat-row-fields">
            <input type="number" class="form-control form-control-sm" name="stat_number[]" placeholder="Number">
            <input type="text" class="form-control form-control-sm" name="stat_suffix[]" placeholder="Suffix">
            <input type="text" class="form-control form-control-sm" name="stat_label[]" placeholder="Label">
          </div>
          <button type="button" class="gd-repeat-row-remove" data-gd-repeat-remove><i class="bi bi-x-lg"></i></button>
        </div>
      </template>
      <button type="button" class="btn btn-sm btn-outline-secondary gd-repeat-add" data-gd-repeat-add><i class="bi bi-plus-lg"></i> Add stat</button>
    </div>
  </div>

  <?php foreach ([0, 1] as $i): $ad = $landing['ad_slots'][$i]; ?>
    <div class="gd-admin-card">
      <h2>Ad Slot <?= $i + 1 ?> <span class="text-muted fw-normal">(optional)</span></h2>
      <div class="gd-admin-field-row">
        <div class="mb-2"><label class="form-label">Eyebrow</label><input type="text" class="form-control" name="ad_slot_<?= $i ?>_eyebrow" value="<?= e($ad['eyebrow'] ?? '') ?>"></div>
        <div class="mb-2"><label class="form-label">Title</label><input type="text" class="form-control" name="ad_slot_<?= $i ?>_title" value="<?= e($ad['title'] ?? '') ?>"></div>
        <div class="mb-2"><label class="form-label">Button Label</label><input type="text" class="form-control" name="ad_slot_<?= $i ?>_cta_label" value="<?= e($ad['cta']['label'] ?? '') ?>"></div>
        <div class="mb-2"><label class="form-label">Button Link</label><input type="text" class="form-control" name="ad_slot_<?= $i ?>_cta_href" value="<?= e($ad['cta']['href'] ?? '') ?>" placeholder="/contact"></div>
      </div>
      <div class="mb-2"><label class="form-label">Subtext</label><input type="text" class="form-control" name="ad_slot_<?= $i ?>_sub" value="<?= e($ad['sub'] ?? '') ?>"></div>
      <?php gd_image_field("ad_slot_{$i}_image", 'categories', (string)($ad['image'] ?? ''), 'Banner Image'); ?>
    </div>
  <?php endforeach; ?>

  <div class="gd-admin-card">
    <h2>Gallery <span class="text-muted fw-normal">(optional)</span></h2>
    <?php gd_image_list_field('gallery', 'categories', $landing['gallery'], 'Installation Photos'); ?>
    <?php $galAbove = $old ? !empty($old['gallery_above_about']) : !empty($landing['gallery_above_about']); ?>
    <div class="form-check mt-3">
      <input class="form-check-input" type="checkbox" name="gallery_above_about" value="1" id="gallery_above_about" <?= $galAbove ? 'checked' : '' ?>>
      <label class="form-check-label" for="gallery_above_about">Show "Completed Installations" above the About section</label>
      <div class="form-text">Unticked: it appears lower down, after the videos.</div>
    </div>
    <?php $rv = $old ? !empty($old['show_reviews']) : !empty($landing['show_reviews']); ?>
    <div class="form-check mt-2">
      <input class="form-check-input" type="checkbox" name="show_reviews" value="1" id="show_reviews" <?= $rv ? 'checked' : '' ?>>
      <label class="form-check-label" for="show_reviews">Show customer testimonials on this page</label>
      <div class="form-text">Reviews tied to this category's products (admin → Reviews) are shown first, then the general ones.</div>
    </div>
    <?php $rvTop = $old ? !empty($old['reviews_above_about']) : !empty($landing['reviews_above_about']); ?>
    <div class="form-check mt-2 ms-4">
      <input class="form-check-input" type="checkbox" name="reviews_above_about" value="1" id="reviews_above_about" <?= $rvTop ? 'checked' : '' ?>>
      <label class="form-check-label" for="reviews_above_about">Show the testimonials above the About section</label>
      <div class="form-text">Unticked: they appear near the bottom, just before the final call-to-action.</div>
    </div>
  </div>

  <div class="gd-admin-card">
    <h2>YouTube Shorts <span class="text-muted fw-normal">(optional — section shows only when this has videos)</span></h2>
    <?php
    $shortsText = $old['youtube_shorts'] ?? implode("\n", array_map(
        fn($s) => 'https://www.youtube.com/shorts/' . $s['id'] . (($s['title'] ?? '') !== '' ? ' | ' . $s['title'] : ''),
        $landing['youtube_shorts'] ?? []
    ));
    ?>
    <textarea class="form-control font-monospace" name="youtube_shorts" rows="6"
              placeholder="https://www.youtube.com/shorts/VIDEO_ID | Caption shown on the card"><?= e($shortsText) ?></textarea>
    <div class="form-text">One video per line: a YouTube Shorts link (or video ID), then <code>|</code> and a short caption. Only a thumbnail loads with the page; the player loads when a visitor taps it.</div>
    <?php $above = $old ? !empty($old['videos_above_about']) : !empty($landing['videos_above_about']); ?>
    <div class="form-check mt-3">
      <input class="form-check-input" type="checkbox" name="videos_above_about" value="1" id="videos_above_about" <?= $above ? 'checked' : '' ?>>
      <label class="form-check-label" for="videos_above_about">Show the video sections (Real Videos + YouTube Shorts) above the About section</label>
      <div class="form-text">Unticked: they appear lower down, after the Process section.</div>
    </div>
  </div>

  <div class="gd-admin-card">
    <h2>FAQs <span class="text-muted fw-normal">(optional, category-specific)</span></h2>
    <div data-gd-repeat>
      <div class="gd-repeat-list" data-gd-repeat-list>
        <?php foreach ($landing['faqs'] as $f): ?>
          <div class="gd-repeat-row">
            <div class="gd-repeat-row-fields">
              <input type="text" class="form-control form-control-sm" name="faq_q[]" value="<?= e($f['q'] ?? '') ?>" placeholder="Question">
              <input type="text" class="form-control form-control-sm" name="faq_a[]" value="<?= e($f['a'] ?? '') ?>" placeholder="Answer">
            </div>
            <button type="button" class="gd-repeat-row-remove" data-gd-repeat-remove><i class="bi bi-x-lg"></i></button>
          </div>
        <?php endforeach; ?>
      </div>
      <template data-gd-repeat-template>
        <div class="gd-repeat-row">
          <div class="gd-repeat-row-fields">
            <input type="text" class="form-control form-control-sm" name="faq_q[]" placeholder="Question">
            <input type="text" class="form-control form-control-sm" name="faq_a[]" placeholder="Answer">
          </div>
          <button type="button" class="gd-repeat-row-remove" data-gd-repeat-remove><i class="bi bi-x-lg"></i></button>
        </div>
      </template>
      <button type="button" class="btn btn-sm btn-outline-secondary gd-repeat-add" data-gd-repeat-add><i class="bi bi-plus-lg"></i> Add FAQ</button>
    </div>
  </div>

  <div class="gd-admin-card">
    <h2>SEO</h2>
    <div class="mb-2">
      <label class="form-label">Meta Title</label>
      <input type="text" class="form-control" name="meta_title" value="<?= e((string)lp_val($old, $landing, 'meta_title')) ?>">
    </div>
    <div class="mb-2">
      <label class="form-label">Meta Description</label>
      <textarea class="form-control" name="meta_desc" rows="2"><?= e((string)lp_val($old, $landing, 'meta_desc')) ?></textarea>
    </div>
  </div>

  <button type="submit" class="btn btn-gd-primary-a"><i class="bi bi-check-lg"></i> Save Landing Page</button>
  <a href="<?= e(gd_admin_url('/modules/landing-pages.php')) ?>" class="btn btn-outline-secondary">Cancel</a>
</form>
<?php require __DIR__ . '/../inc/layout-bottom.php'; ?>
