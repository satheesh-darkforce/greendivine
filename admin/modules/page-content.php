<?php
/**
 * Page Copy — the narrative blocks that used to be hand-typed directly into
 * pages/about.php, home.php, services.php and contact.php (Section 8.18 of
 * the implementation plan). Bespoke rather than built on the generic
 * singleton engine: this one file holds several distinctly-shaped sections
 * (a fixed 3-item pillar list, a bullet list, plain text blocks) rather
 * than one flat/dot-path record, so a hand-written form reads far more
 * clearly than forcing every shape through one generic renderer.
 */
require __DIR__ . '/../inc/guard.php';

$adminTitle  = 'Page Copy';
$adminActive = 'page-content';
$key         = 'page_content';
$record      = Store::load($key);

// name => [label, type(text|textarea|image), maxlen, bucket]
$fields = [
    'about.story.eyebrow'      => ['Eyebrow', 'text', 60],
    'about.story.title'        => ['Heading (before highlight)', 'text', 80],
    'about.story.title_accent' => ['Heading (highlighted part)', 'text', 60],
    'about.story.lead'         => ['Lead paragraph', 'textarea', 200],
    'about.story.body'         => ['Body paragraph', 'textarea', 500],
    'about.story.image'        => ['Photo', 'image', null, ''],
    'about.cta.title'          => ['CTA Heading', 'text', 80],
    'about.cta.sub'            => ['CTA Subtext', 'text', 160],
    'home.why_us_editorial.eyebrow'      => ['Eyebrow', 'text', 60],
    'home.why_us_editorial.title'        => ['Heading (before highlight)', 'text', 80],
    'home.why_us_editorial.title_accent' => ['Heading (highlighted part)', 'text', 60],
    'home.why_us_editorial.lead'         => ['Paragraph', 'textarea', 300],
    'home.why_us_editorial.image'        => ['Photo', 'image', null, 'installations'],
    'home.quote_section.eyebrow'      => ['Eyebrow', 'text', 60],
    'home.quote_section.title'        => ['Heading (before highlight)', 'text', 60],
    'home.quote_section.title_accent' => ['Heading (highlighted part)', 'text', 60],
    'home.quote_section.lede'         => ['Intro text', 'textarea', 200],
    'services.cta.title' => ['CTA Heading', 'text', 80],
    'services.cta.sub'   => ['CTA Subtext', 'text', 160],
    'contact.hero.eyebrow'      => ['Eyebrow', 'text', 60],
    'contact.hero.title'        => ['Heading (before highlight)', 'text', 60],
    'contact.hero.title_accent' => ['Heading (highlighted part)', 'text', 60],
    'contact.hero.sub'          => ['Subtext', 'text', 200],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $v = new V();
    $input = [];

    foreach ($fields as $name => [$label, $type, $maxlen, $extra]) {
        $formName = gd_form_name($name);
        if ($type === 'image') {
            $previous = (string)gd_dot_get($record, $name, '');
            $img = gd_resolve_image($formName, $extra ?? '', $previous);
            $value = $img['ok'] ? ($img['name'] ?? '') : $previous;
            if (!$img['ok']) $v->fail($name, $img['error']);
        } else {
            $value = trim((string)($_POST[$formName] ?? ''));
        }
        gd_dot_set($input, $name, $value);
        $v->required($name, $value, $label);
        if ($maxlen) $v->maxlen($name, $value, $maxlen, $label);
    }

    // Bullets: one per line, at least one required.
    $bullets = gd_lines_to_list($_POST[gd_form_name('about.story.bullets')] ?? '');
    $v->minCount('about.story.bullets', $bullets, 1, 'Story bullet points');
    gd_dot_set($input, 'about.story.bullets', $bullets);

    // Pillars: fixed 3 (Mission / Vision / Promise).
    $pillars = [];
    for ($i = 0; $i < 3; $i++) {
        $pillars[] = [
            'icon'  => trim((string)($_POST["pillar_{$i}_icon"] ?? '')),
            'title' => trim((string)($_POST["pillar_{$i}_title"] ?? '')),
            'desc'  => trim((string)($_POST["pillar_{$i}_desc"] ?? '')),
        ];
        $v->required("pillar_{$i}_icon", $pillars[$i]['icon'], "Pillar " . ($i + 1) . " icon");
        $v->required("pillar_{$i}_title", $pillars[$i]['title'], "Pillar " . ($i + 1) . " title");
        $v->required("pillar_{$i}_desc", $pillars[$i]['desc'], "Pillar " . ($i + 1) . " description");
    }
    $input['about']['pillars'] = $pillars;

    if (!$v->ok()) {
        fail_back('/modules/page-content.php', $v->errors(), array_merge($_POST, ['about.story.bullets' => implode("\n", $bullets)]));
    }

    $merged = array_replace_recursive($record, $input);
    Store::save($key, $merged);
    gd_log_activity('Updated Page Copy');
    flash('success', 'Page Copy saved.');
    header('Location: ' . gd_admin_url('/modules/page-content.php'));
    exit;
}

$old    = old_input();
$errors = old_errors();

/** Value for a (possibly dot-path) field: prefers a failed resubmission, else the stored record. */
function pc_val(?array $old, array $record, string $name, $default = '') {
    if ($old !== null) {
        $formName = gd_form_name($name);
        if (array_key_exists($formName, $old)) return $old[$formName];
        if (array_key_exists($name, $old)) return $old[$name];
    }
    return gd_dot_get($record, $name, $default);
}

require __DIR__ . '/../inc/layout-top.php';
?>
<div class="gd-admin-toolbar">
  <div>
    <h1>Page Copy</h1>
    <p>The narrative text blocks on About, Home, Services and Contact that aren't part of any list — story, mission/vision/promise, CTA bands and the contact hero.</p>
  </div>
</div>

<form method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>

  <div class="gd-admin-card">
    <h2>About Page &mdash; Our Story</h2>
    <div class="gd-admin-field-row">
      <?php foreach (['about.story.eyebrow', 'about.story.title', 'about.story.title_accent'] as $name):
          [$label, $type, $maxlen] = $fields[$name]; ?>
        <div class="mb-2">
          <label class="form-label"><?= e($label) ?> *</label>
          <input type="text" class="form-control <?= isset($errors[$name]) ? 'is-invalid' : '' ?>" name="<?= e(gd_form_name($name)) ?>" value="<?= e((string)pc_val($old, $record, $name)) ?>">
          <?php if (isset($errors[$name])): ?><div class="invalid-feedback"><?= e($errors[$name]) ?></div><?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="mb-2">
      <label class="form-label">Lead paragraph *</label>
      <textarea class="form-control <?= isset($errors['about.story.lead']) ? 'is-invalid' : '' ?>" name="<?= e(gd_form_name('about.story.lead')) ?>" rows="2"><?= e((string)pc_val($old, $record, 'about.story.lead')) ?></textarea>
    </div>
    <div class="mb-2">
      <label class="form-label">Body paragraph *</label>
      <textarea class="form-control <?= isset($errors['about.story.body']) ? 'is-invalid' : '' ?>" name="<?= e(gd_form_name('about.story.body')) ?>" rows="3"><?= e((string)pc_val($old, $record, 'about.story.body')) ?></textarea>
    </div>
    <div class="mb-2">
      <label class="form-label">Bullet points (one per line) *</label>
      <?php $bulletsVal = $old !== null ? (is_array($old['about.story.bullets'] ?? null) ? implode("\n", $old['about.story.bullets']) : ($old[gd_form_name('about.story.bullets')] ?? '')) : implode("\n", gd_dot_get($record, 'about.story.bullets', [])); ?>
      <textarea class="form-control <?= isset($errors['about.story.bullets']) ? 'is-invalid' : '' ?>" name="<?= e(gd_form_name('about.story.bullets')) ?>" rows="4"><?= e((string)$bulletsVal) ?></textarea>
      <?php if (isset($errors['about.story.bullets'])): ?><div class="invalid-feedback"><?= e($errors['about.story.bullets']) ?></div><?php endif; ?>
    </div>
    <?php gd_image_field(gd_form_name('about.story.image'), '', (string)gd_dot_get($record, 'about.story.image', ''), 'Photo'); ?>
  </div>

  <div class="gd-admin-card">
    <h2>About Page &mdash; Mission / Vision / Promise</h2>
    <p class="gd-admin-card-hint">Always exactly 3 pillars &mdash; the layout is built for 3.</p>
    <?php $pillarLabels = ['Mission', 'Vision', 'Promise']; ?>
    <?php foreach ($pillarLabels as $i => $pl): $pillar = $record['about']['pillars'][$i] ?? ['icon'=>'','title'=>'','desc'=>'']; ?>
      <div class="gd-repeat-row mb-2">
        <div class="gd-repeat-row-fields">
          <div>
            <label class="form-label small">Icon (<?= e($pl) ?>)</label>
            <input type="text" class="form-control form-control-sm <?= isset($errors["pillar_{$i}_icon"]) ? 'is-invalid' : '' ?>" name="pillar_<?= $i ?>_icon" value="<?= e($old['pillar_' . $i . '_icon'] ?? $pillar['icon']) ?>">
          </div>
          <div>
            <label class="form-label small">Title</label>
            <input type="text" class="form-control form-control-sm <?= isset($errors["pillar_{$i}_title"]) ? 'is-invalid' : '' ?>" name="pillar_<?= $i ?>_title" value="<?= e($old['pillar_' . $i . '_title'] ?? $pillar['title']) ?>">
          </div>
          <div>
            <label class="form-label small">Description</label>
            <input type="text" class="form-control form-control-sm <?= isset($errors["pillar_{$i}_desc"]) ? 'is-invalid' : '' ?>" name="pillar_<?= $i ?>_desc" value="<?= e($old['pillar_' . $i . '_desc'] ?? $pillar['desc']) ?>">
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="gd-admin-card">
    <h2>About Page &mdash; CTA Band</h2>
    <div class="gd-admin-field-row">
      <div class="mb-2">
        <label class="form-label">Heading *</label>
        <input type="text" class="form-control" name="<?= e(gd_form_name('about.cta.title')) ?>" value="<?= e((string)pc_val($old, $record, 'about.cta.title')) ?>">
      </div>
      <div class="mb-2">
        <label class="form-label">Subtext *</label>
        <input type="text" class="form-control" name="<?= e(gd_form_name('about.cta.sub')) ?>" value="<?= e((string)pc_val($old, $record, 'about.cta.sub')) ?>">
      </div>
    </div>
  </div>

  <div class="gd-admin-card">
    <h2>Home Page &mdash; "Why Green Divine" Editorial</h2>
    <div class="gd-admin-field-row">
      <?php foreach (['home.why_us_editorial.eyebrow', 'home.why_us_editorial.title', 'home.why_us_editorial.title_accent'] as $name): ?>
        <div class="mb-2">
          <label class="form-label"><?= e($fields[$name][0]) ?> *</label>
          <input type="text" class="form-control" name="<?= e(gd_form_name($name)) ?>" value="<?= e((string)pc_val($old, $record, $name)) ?>">
        </div>
      <?php endforeach; ?>
    </div>
    <div class="mb-2">
      <label class="form-label">Paragraph *</label>
      <textarea class="form-control" name="<?= e(gd_form_name('home.why_us_editorial.lead')) ?>" rows="2"><?= e((string)pc_val($old, $record, 'home.why_us_editorial.lead')) ?></textarea>
    </div>
    <?php gd_image_field(gd_form_name('home.why_us_editorial.image'), 'installations', (string)gd_dot_get($record, 'home.why_us_editorial.image', ''), 'Photo'); ?>
  </div>

  <div class="gd-admin-card">
    <h2>Home Page &mdash; Quote Section Intro</h2>
    <div class="gd-admin-field-row">
      <?php foreach (['home.quote_section.eyebrow', 'home.quote_section.title', 'home.quote_section.title_accent'] as $name): ?>
        <div class="mb-2">
          <label class="form-label"><?= e($fields[$name][0]) ?> *</label>
          <input type="text" class="form-control" name="<?= e(gd_form_name($name)) ?>" value="<?= e((string)pc_val($old, $record, $name)) ?>">
        </div>
      <?php endforeach; ?>
    </div>
    <div class="mb-2">
      <label class="form-label">Intro text *</label>
      <textarea class="form-control" name="<?= e(gd_form_name('home.quote_section.lede')) ?>" rows="2"><?= e((string)pc_val($old, $record, 'home.quote_section.lede')) ?></textarea>
    </div>
  </div>

  <div class="gd-admin-card">
    <h2>Services Page &mdash; CTA Band</h2>
    <div class="gd-admin-field-row">
      <div class="mb-2">
        <label class="form-label">Heading *</label>
        <input type="text" class="form-control" name="<?= e(gd_form_name('services.cta.title')) ?>" value="<?= e((string)pc_val($old, $record, 'services.cta.title')) ?>">
      </div>
      <div class="mb-2">
        <label class="form-label">Subtext *</label>
        <input type="text" class="form-control" name="<?= e(gd_form_name('services.cta.sub')) ?>" value="<?= e((string)pc_val($old, $record, 'services.cta.sub')) ?>">
      </div>
    </div>
  </div>

  <div class="gd-admin-card">
    <h2>Contact Page &mdash; Hero</h2>
    <div class="gd-admin-field-row">
      <?php foreach (['contact.hero.eyebrow', 'contact.hero.title', 'contact.hero.title_accent'] as $name): ?>
        <div class="mb-2">
          <label class="form-label"><?= e($fields[$name][0]) ?> *</label>
          <input type="text" class="form-control" name="<?= e(gd_form_name($name)) ?>" value="<?= e((string)pc_val($old, $record, $name)) ?>">
        </div>
      <?php endforeach; ?>
    </div>
    <div class="mb-2">
      <label class="form-label">Subtext *</label>
      <input type="text" class="form-control" name="<?= e(gd_form_name('contact.hero.sub')) ?>" value="<?= e((string)pc_val($old, $record, 'contact.hero.sub')) ?>">
    </div>
  </div>

  <button type="submit" class="btn btn-gd-primary-a"><i class="bi bi-check-lg"></i> Save Changes</button>
</form>
<?php require __DIR__ . '/../inc/layout-bottom.php'; ?>
