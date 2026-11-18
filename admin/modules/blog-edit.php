<?php
/** Blog post add/edit — the richest record in the system (Section 8.15). */
require __DIR__ . '/../inc/guard.php';

$blogs = Store::load('blogs');
$index = isset($_GET['i']) ? (int)$_GET['i'] : null;
$isNew = ($_GET['action'] ?? '') === 'add' || $index === null || !isset($blogs[$index]);
$blog  = $isNew ? null : $blogs[$index];

/** Read repeatable {a,b[,c]} rows submitted as parallel arrays a[], b[], c[]. */
function be_rows(array $fieldNames): array {
    $cols = [];
    foreach ($fieldNames as $f) $cols[$f] = (array)($_POST[$f] ?? []);
    $count = $cols[$fieldNames[0]] ? count($cols[$fieldNames[0]]) : 0;
    $rows = [];
    for ($i = 0; $i < $count; $i++) {
        $row = [];
        $hasAny = false;
        foreach ($fieldNames as $f) {
            $val = trim((string)($cols[$f][$i] ?? ''));
            $row[$f] = $val;
            if ($val !== '') $hasAny = true;
        }
        if ($hasAny) $rows[] = $row;
    }
    return $rows;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $v = new V();
    $postIndex = $_POST['gd_index'] === 'new' ? null : (int)$_POST['gd_index'];
    $existing  = $postIndex !== null ? $blogs[$postIndex] : null;

    $in = [];
    foreach (['slug', 'title', 'excerpt', 'author', 'date', 'cta_category'] as $f) {
        $in[$f] = trim((string)($_POST[$f] ?? ''));
    }
    $in['read_min'] = (int)($_POST['read_min'] ?? 0);
    $in['meta_title'] = trim((string)($_POST['meta_title'] ?? ''));
    $in['meta_desc']  = trim((string)($_POST['meta_desc'] ?? ''));
    $in['tldr'] = trim((string)($_POST['tldr'] ?? ''));
    $in['body'] = trim((string)($_POST['body'] ?? ''));
    $in['key_takeaways'] = gd_lines_to_list($_POST['key_takeaways'] ?? '');
    $in['tags'] = gd_lines_to_list($_POST['tags'] ?? '');

    $quoteText = trim((string)($_POST['pull_quote_text'] ?? ''));
    $quoteAuthor = trim((string)($_POST['pull_quote_author'] ?? ''));
    $in['pull_quote'] = ['text' => $quoteText, 'author' => $quoteAuthor];

    $galleryRows = be_rows(['gallery_src', 'gallery_caption']);
    $in['gallery'] = array_map(fn($r) => ['src' => $r['gallery_src'], 'caption' => $r['gallery_caption']], $galleryRows);

    $faqRows = be_rows(['faq_q', 'faq_a']);
    $in['faqs'] = array_filter(array_map(fn($r) => ['q' => $r['faq_q'], 'a' => $r['faq_a']], $faqRows), fn($r) => $r['q'] !== '' && $r['a'] !== '');
    $in['faqs'] = array_values($in['faqs']);

    $refRows = be_rows(['ref_title', 'ref_url', 'ref_source']);
    $in['references'] = array_map(fn($r) => ['title' => $r['ref_title'], 'url' => $r['ref_url'], 'source' => $r['ref_source']], $refRows);

    $imgPrev = $existing['image'] ?? '';
    $imgRes = gd_resolve_image('image', 'blog', $imgPrev);
    $in['image'] = $imgRes['ok'] ? ($imgRes['name'] ?? '') : $imgPrev;
    if (!$imgRes['ok']) $v->fail('image', $imgRes['error']);

    $v->required('slug', $in['slug'], 'Slug')->slug('slug', $in['slug'], 'Slug');
    $v->required('title', $in['title'], 'Title')->maxlen('title', $in['title'], 100, 'Title');
    $v->required('excerpt', $in['excerpt'], 'Excerpt')->maxlen('excerpt', $in['excerpt'], 160, 'Excerpt');
    $v->required('author', $in['author'], 'Author');
    $v->required('date', $in['date'], 'Date');
    $v->required('read_min', (string)$in['read_min'], 'Read minutes')->intRange('read_min', $in['read_min'], 1, 60, 'Read minutes');
    $v->required('image', $in['image'], 'Cover Image');
    $v->required('body', $in['body'], 'Body');
    // TL;DR and Key Takeaways are optional — long-form posts written as a
    // single document (headings, lists, images, FAQs) often have neither.
    $v->maxlen('meta_title', $in['meta_title'], 70, 'Meta Title');
    $v->maxlen('meta_desc', $in['meta_desc'], 170, 'Meta Description');

    if (($quoteText === '') !== ($quoteAuthor === '')) {
        $v->fail('pull_quote_text', 'Pull quote text and author must both be filled in, or both left blank.');
    }

    global $DATA;
    $others = [];
    foreach ($blogs as $i => $b) { if ($i !== $postIndex) $others[] = $b['slug']; }
    $v->uniqueSlug('slug', $in['slug'], $others, 'Slug');
    if ($in['cta_category'] !== '') {
        $v->oneOf('cta_category', $in['cta_category'], array_column($DATA['categories'], 'slug'), 'Related Category');
    }
    foreach ($in['references'] as $i => $ref) {
        if ($ref['url'] !== '' && !preg_match('#^https?://#i', $ref['url'])) {
            $v->fail('ref_url', 'Reference links must be full https:// URLs.');
            break;
        }
    }

    if (!$v->ok()) {
        $old = $_POST;
        $old['key_takeaways'] = implode("\n", $in['key_takeaways']);
        $old['tags'] = implode("\n", $in['tags']);
        fail_back('/modules/blog-edit.php?' . ($postIndex === null ? 'action=add' : 'action=edit&i=' . $postIndex), $v->errors(), $old);
    }

    if ($postIndex === null) { $blogs[] = $in; }
    else { $blogs[$postIndex] = $in; }

    Store::save('blogs', array_values($blogs));
    gd_log_activity($postIndex === null ? 'Added blog post' : 'Edited blog post', $in['title']);
    flash('success', 'Post saved.');
    header('Location: ' . gd_admin_url('/modules/blogs.php'));
    exit;
}

$old    = old_input();
$errors = old_errors();
function be_val($old, $blog, $key, $default = '') { return $old !== null ? ($old[$key] ?? $default) : ($blog[$key] ?? $default); }
function be_lines($old, $blog, $key) {
    if ($old !== null) return (string)($old[$key] ?? '');
    $l = $blog[$key] ?? [];
    return is_array($l) ? implode("\n", $l) : (string)$l;
}
/** A dynamic add/remove row group; $cols is [fieldName => placeholder]. */
function be_row_group(string $label, array $cols, array $rows, string $hint = ''): void {
    ?>
    <div class="gd-admin-card">
      <h2><?= e($label) ?></h2>
      <?php if ($hint): ?><p class="gd-admin-card-hint"><?= e($hint) ?></p><?php endif; ?>
      <div data-gd-repeat>
        <div class="gd-repeat-list" data-gd-repeat-list>
          <?php foreach ($rows as $row): ?>
            <div class="gd-repeat-row">
              <div class="gd-repeat-row-fields">
                <?php foreach ($cols as $name => $ph): ?>
                  <input type="text" class="form-control form-control-sm" name="<?= e($name) ?>[]" value="<?= e($row[$name] ?? '') ?>" placeholder="<?= e($ph) ?>">
                <?php endforeach; ?>
              </div>
              <button type="button" class="gd-repeat-row-remove" data-gd-repeat-remove><i class="bi bi-x-lg"></i></button>
            </div>
          <?php endforeach; ?>
        </div>
        <template data-gd-repeat-template>
          <div class="gd-repeat-row">
            <div class="gd-repeat-row-fields">
              <?php foreach ($cols as $name => $ph): ?>
                <input type="text" class="form-control form-control-sm" name="<?= e($name) ?>[]" placeholder="<?= e($ph) ?>">
              <?php endforeach; ?>
            </div>
            <button type="button" class="gd-repeat-row-remove" data-gd-repeat-remove><i class="bi bi-x-lg"></i></button>
          </div>
        </template>
        <button type="button" class="btn btn-sm btn-outline-secondary gd-repeat-add" data-gd-repeat-add><i class="bi bi-plus-lg"></i> Add row</button>
      </div>
    </div>
    <?php
}

$adminTitle  = $isNew ? 'Add Post' : 'Edit Post';
$adminActive = 'blogs';
require __DIR__ . '/../inc/layout-top.php';
?>
<div class="gd-admin-toolbar">
  <div><h1><?= $isNew ? 'Add' : 'Edit' ?> Post</h1></div>
  <a href="<?= e(gd_admin_url('/modules/blogs.php')) ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to list</a>
</div>

<form method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <input type="hidden" name="gd_index" value="<?= $isNew ? 'new' : (int)$index ?>">

  <div class="gd-admin-card">
    <h2>Basics</h2>
    <div class="gd-admin-field-row">
      <div class="mb-2">
        <label class="form-label">Title *</label>
        <input type="text" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" name="title" id="bf-title" value="<?= e((string)be_val($old, $blog, 'title')) ?>" data-gd-slug-source="#bf-slug">
        <?php if (isset($errors['title'])): ?><div class="invalid-feedback"><?= e($errors['title']) ?></div><?php endif; ?>
      </div>
      <div class="mb-2">
        <label class="form-label">Slug *</label>
        <input type="text" class="form-control <?= isset($errors['slug']) ? 'is-invalid' : '' ?>" name="slug" id="bf-slug" value="<?= e((string)be_val($old, $blog, 'slug')) ?>">
        <?php if (isset($errors['slug'])): ?><div class="invalid-feedback"><?= e($errors['slug']) ?></div><?php endif; ?>
      </div>
      <div class="mb-2">
        <label class="form-label">Author *</label>
        <input type="text" class="form-control <?= isset($errors['author']) ? 'is-invalid' : '' ?>" name="author" value="<?= e((string)be_val($old, $blog, 'author')) ?>">
      </div>
      <div class="mb-2">
        <label class="form-label">Date *</label>
        <input type="date" class="form-control <?= isset($errors['date']) ? 'is-invalid' : '' ?>" name="date" value="<?= e((string)be_val($old, $blog, 'date')) ?>">
      </div>
      <div class="mb-2">
        <label class="form-label">Read Minutes *</label>
        <input type="number" class="form-control <?= isset($errors['read_min']) ? 'is-invalid' : '' ?>" name="read_min" min="1" max="60" value="<?= e((string)be_val($old, $blog, 'read_min')) ?>">
      </div>
      <div class="mb-2">
        <label class="form-label">Related Category <span class="text-muted fw-normal">(optional)</span></label>
        <select class="form-select" name="cta_category">
          <option value="">— None —</option>
          <?php foreach ($DATA['categories'] as $c): ?>
            <option value="<?= e($c['slug']) ?>" <?= (string)be_val($old, $blog, 'cta_category') === $c['slug'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="mb-2">
      <label class="form-label">Excerpt * <span class="text-muted fw-normal">(≤160 chars, shown on cards)</span></label>
      <textarea class="form-control <?= isset($errors['excerpt']) ? 'is-invalid' : '' ?>" name="excerpt" rows="2"><?= e((string)be_val($old, $blog, 'excerpt')) ?></textarea>
    </div>
    <?php gd_image_field('image', 'blog', (string)($blog['image'] ?? ''), 'Cover Image *'); ?>
  </div>

  <div class="gd-admin-card">
    <h2>SEO</h2>
    <p class="gd-admin-card-hint">Optional. Left blank, the page title falls back to the post title and the description to the excerpt.</p>
    <div class="mb-2">
      <label class="form-label">Meta Title <span class="text-muted fw-normal">(≤70 chars)</span></label>
      <input type="text" class="form-control <?= isset($errors['meta_title']) ? 'is-invalid' : '' ?>" name="meta_title" maxlength="70" value="<?= e((string)be_val($old, $blog, 'meta_title')) ?>">
      <?php if (isset($errors['meta_title'])): ?><div class="invalid-feedback"><?= e($errors['meta_title']) ?></div><?php endif; ?>
    </div>
    <div class="mb-2">
      <label class="form-label">Meta Description <span class="text-muted fw-normal">(≤170 chars)</span></label>
      <textarea class="form-control <?= isset($errors['meta_desc']) ? 'is-invalid' : '' ?>" name="meta_desc" rows="2" maxlength="170"><?= e((string)be_val($old, $blog, 'meta_desc')) ?></textarea>
      <?php if (isset($errors['meta_desc'])): ?><div class="invalid-feedback"><?= e($errors['meta_desc']) ?></div><?php endif; ?>
    </div>
  </div>

  <div class="gd-admin-card">
    <h2>Body</h2>
    <div class="mb-2">
      <label class="form-label">TL;DR <span class="text-muted fw-normal">(optional)</span></label>
      <textarea class="form-control <?= isset($errors['tldr']) ? 'is-invalid' : '' ?>" name="tldr" rows="2"><?= e((string)be_val($old, $blog, 'tldr')) ?></textarea>
    </div>
    <div class="mb-2">
      <label class="form-label">Full Body *</label>
      <textarea class="form-control <?= isset($errors['body']) ? 'is-invalid' : '' ?>" name="body" rows="8" data-gd-richtext><?= e((string)be_val($old, $blog, 'body')) ?></textarea>
      <div class="form-hint mt-1">HTML is rendered as-is: &lt;p&gt;, &lt;h2&gt; (section — also listed in the "In this article" contents), &lt;h3&gt;/&lt;h4&gt; (sub-points), &lt;strong&gt;, &lt;ul&gt;&lt;li&gt;. Images: &lt;figure&gt;&lt;img src="/assets/images/blog/name.jpg" alt="…"&gt;&lt;figcaption&gt;…&lt;/figcaption&gt;&lt;/figure&gt; — any size, they scale to fit every screen.</div>
    </div>
    <div class="gd-admin-field-row">
      <div class="mb-2">
        <label class="form-label">Pull Quote Text</label>
        <input type="text" class="form-control <?= isset($errors['pull_quote_text']) ? 'is-invalid' : '' ?>" name="pull_quote_text" value="<?= e((string)($blog['pull_quote']['text'] ?? '')) ?>">
        <?php if (isset($errors['pull_quote_text'])): ?><div class="invalid-feedback"><?= e($errors['pull_quote_text']) ?></div><?php endif; ?>
      </div>
      <div class="mb-2">
        <label class="form-label">Pull Quote Author</label>
        <input type="text" class="form-control" name="pull_quote_author" value="<?= e((string)($blog['pull_quote']['author'] ?? '')) ?>">
      </div>
    </div>
  </div>

  <div class="gd-admin-card">
    <h2>Key Takeaways <span class="text-muted fw-normal fs-6">(optional)</span></h2>
    <label class="form-label">One per line</label>
    <textarea class="form-control <?= isset($errors['key_takeaways']) ? 'is-invalid' : '' ?>" name="key_takeaways" rows="4"><?= e(be_lines($old, $blog, 'key_takeaways')) ?></textarea>
    <?php if (isset($errors['key_takeaways'])): ?><div class="invalid-feedback"><?= e($errors['key_takeaways']) ?></div><?php endif; ?>
  </div>

  <?php be_row_group('Gallery', ['gallery_src' => 'Image filename or URL', 'gallery_caption' => 'Caption'], $blog['gallery'] ?? [], 'Paste an already-uploaded filename (e.g. from Products) or an external image URL.'); ?>
  <?php be_row_group('This Post\'s FAQs', ['faq_q' => 'Question', 'faq_a' => 'Answer'], $blog['faqs'] ?? [], 'Separate from the site-wide/per-page FAQ system.'); ?>
  <?php be_row_group('References', ['ref_title' => 'Title', 'ref_url' => 'https://...', 'ref_source' => 'Source name'], $blog['references'] ?? []); ?>

  <div class="gd-admin-card">
    <h2>Tags <span class="text-muted fw-normal">(internal — not currently displayed on the site)</span></h2>
    <label class="form-label">One per line</label>
    <textarea class="form-control" name="tags" rows="2"><?= e(be_lines($old, $blog, 'tags')) ?></textarea>
  </div>

  <button type="submit" class="btn btn-gd-primary-a"><i class="bi bi-check-lg"></i> Save Post</button>
  <a href="<?= e(gd_admin_url('/modules/blogs.php')) ?>" class="btn btn-outline-secondary">Cancel</a>
</form>
<?php require __DIR__ . '/../inc/layout-bottom.php'; ?>
