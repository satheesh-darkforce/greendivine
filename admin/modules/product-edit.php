<?php
/** Product add/edit — the richest record in the system (Section 8.6). */
require __DIR__ . '/../inc/guard.php';

$products = Store::load('products');
$index    = isset($_GET['i']) ? (int)$_GET['i'] : null;
$isNew    = ($_GET['action'] ?? '') === 'add' || $index === null || !isset($products[$index]);
$product  = $isNew ? null : $products[$index];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $v = new V();
    $postIndex = $_POST['gd_index'] === 'new' ? null : (int)$_POST['gd_index'];
    $existing  = $postIndex !== null ? $products[$postIndex] : null;

    $in = [];
    $in['slug']        = trim((string)($_POST['slug'] ?? ''));
    $in['name']        = trim((string)($_POST['name'] ?? ''));
    $in['category']    = trim((string)($_POST['category'] ?? ''));
    $in['short']       = trim((string)($_POST['short'] ?? ''));
    $in['description'] = trim((string)($_POST['description'] ?? ''));
    $in['price_label'] = trim((string)($_POST['price_label'] ?? ''));
    $in['price_was']   = trim((string)($_POST['price_was'] ?? ''));
    $in['badge']       = trim((string)($_POST['badge'] ?? ''));
    $in['is_deal']     = isset($_POST['is_deal']);
    $in['show_price']  = isset($_POST['show_price']);
    $in['meta_title']  = trim((string)($_POST['meta_title'] ?? ''));
    $in['meta_desc']   = trim((string)($_POST['meta_desc'] ?? ''));

    $in['features'] = gd_lines_to_list($_POST['features'] ?? '');

    $specKeys = (array)($_POST['spec_key'] ?? []);
    $specVals = (array)($_POST['spec_value'] ?? []);
    $specs = [];
    foreach ($specKeys as $i => $k) {
        $k = trim((string)$k); $val = trim((string)($specVals[$i] ?? ''));
        if ($k !== '' && $val !== '') $specs[$k] = $val;
    }
    $in['specs'] = $specs;

    $in['images']        = gd_collect_image_list('images', $existing['images'] ?? [], 'products');
    $in['installations']  = gd_collect_image_list('installations', $existing['installations'] ?? [], 'installations');
    // Install videos (assets/videos/) have no editor field yet — carry them
    // over untouched so saving the form doesn't silently drop them.
    $in['videos']         = $existing['videos'] ?? [];

    $v->required('slug', $in['slug'], 'Slug')->slug('slug', $in['slug'], 'Slug');
    $v->required('name', $in['name'], 'Name')->maxlen('name', $in['name'], 80, 'Name');
    $v->required('category', $in['category'], 'Category');
    $v->required('short', $in['short'], 'Short description')->maxlen('short', $in['short'], 140, 'Short description');
    $v->required('description', $in['description'], 'Description');
    // Only demanded when the price is actually published — a quote-only
    // product has nothing meaningful to put here.
    if ($in['show_price']) {
        $v->required('price_label', $in['price_label'], 'Price label');
    }
    $v->maxlen('badge', $in['badge'], 20, 'Badge');
    $v->minCount('features', $in['features'], 1, 'Features');
    $v->minCount('images', $in['images'], 1, 'Images');

    global $DATA;
    $categorySlugs = array_column($DATA['categories'], 'slug');
    $v->oneOf('category', $in['category'], $categorySlugs, 'Category');

    $others = [];
    foreach ($products as $i => $p) { if ($i !== $postIndex) $others[] = $p['slug']; }
    $v->uniqueSlug('slug', $in['slug'], $others, 'Slug');

    if (!$v->ok()) {
        $old = $_POST;
        $old['features'] = implode("\n", $in['features']); // keep the textarea shape on redisplay
        fail_back('/modules/product-edit.php?' . ($postIndex === null ? 'action=add' : 'action=edit&i=' . $postIndex), $v->errors(), $old);
    }

    // is_deal is a single-select across the whole catalogue: turning it on
    // here turns it off everywhere else, so gd_todays_deal() never has to
    // pick a winner between two flagged products.
    if ($in['is_deal']) {
        foreach ($products as $i => $p) { if ($i !== $postIndex) $products[$i]['is_deal'] = false; }
    }

    if ($postIndex === null) { $products[] = $in; }
    else { $products[$postIndex] = $in; }

    Store::save('products', array_values($products));
    gd_log_activity($postIndex === null ? 'Added product' : 'Edited product', $in['name']);
    flash('success', 'Product saved.');
    header('Location: ' . gd_admin_url('/modules/products.php'));
    exit;
}

$old    = old_input();
$errors = old_errors();
function pe_val($old, $product, $key, $default = '') {
    if ($old !== null) return $old[$key] ?? $default;
    return $product[$key] ?? $default;
}
/** Newline-joined string for a textarea, whether the source is a failed resubmission (already a string) or a fresh record (a list). */
function pe_lines($old, $product, $key) {
    if ($old !== null) return (string)($old[$key] ?? '');
    $list = $product[$key] ?? [];
    return is_array($list) ? implode("\n", $list) : (string)$list;
}

$adminTitle  = $isNew ? 'Add Product' : 'Edit Product';
$adminActive = 'products';
require __DIR__ . '/../inc/layout-top.php';
?>
<div class="gd-admin-toolbar">
  <div><h1><?= $isNew ? 'Add' : 'Edit' ?> Product</h1></div>
  <a href="<?= e(gd_admin_url('/modules/products.php')) ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to list</a>
</div>

<form method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <input type="hidden" name="gd_index" value="<?= $isNew ? 'new' : (int)$index ?>">

  <div class="gd-admin-card">
    <h2>Basics</h2>
    <div class="gd-admin-field-row">
      <div class="mb-2">
        <label class="form-label">Name *</label>
        <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" name="name" id="pf-name" value="<?= e((string)pe_val($old, $product, 'name')) ?>" data-gd-slug-source="#pf-slug">
        <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= e($errors['name']) ?></div><?php endif; ?>
      </div>
      <div class="mb-2">
        <label class="form-label">Slug (URL) *</label>
        <input type="text" class="form-control <?= isset($errors['slug']) ? 'is-invalid' : '' ?>" name="slug" id="pf-slug" value="<?= e((string)pe_val($old, $product, 'slug')) ?>">
        <?php if (isset($errors['slug'])): ?><div class="invalid-feedback"><?= e($errors['slug']) ?></div><?php endif; ?>
      </div>
      <div class="mb-2">
        <label class="form-label">Category *</label>
        <select class="form-select <?= isset($errors['category']) ? 'is-invalid' : '' ?>" name="category">
          <option value="">— Choose —</option>
          <?php foreach ($DATA['categories'] as $c): $sel = (string)pe_val($old, $product, 'category') === $c['slug']; ?>
            <option value="<?= e($c['slug']) ?>" <?= $sel ? 'selected' : '' ?>><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <?php if (isset($errors['category'])): ?><div class="invalid-feedback"><?= e($errors['category']) ?></div><?php endif; ?>
      </div>
    </div>
    <div class="mb-2">
      <label class="form-label">Short Description * <span class="text-muted fw-normal">(shown on cards, ≤140 chars)</span></label>
      <textarea class="form-control <?= isset($errors['short']) ? 'is-invalid' : '' ?>" name="short" rows="2"><?= e((string)pe_val($old, $product, 'short')) ?></textarea>
      <?php if (isset($errors['short'])): ?><div class="invalid-feedback"><?= e($errors['short']) ?></div><?php endif; ?>
    </div>
  </div>

  <div class="gd-admin-card">
    <h2>Description &amp; Pricing</h2>
    <div class="mb-2">
      <label class="form-label">Full Description *</label>
      <textarea class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>" name="description" rows="5" data-gd-richtext><?= e((string)pe_val($old, $product, 'description')) ?></textarea>
      <div class="form-hint mt-1">Basic HTML tags (&lt;p&gt;, &lt;strong&gt;, &lt;ul&gt;&lt;li&gt;) are fine — this is rendered as-is on the product page.</div>
      <?php if (isset($errors['description'])): ?><div class="invalid-feedback"><?= e($errors['description']) ?></div><?php endif; ?>
    </div>
    <div class="gd-admin-field-row">
      <div class="mb-2">
        <label class="form-label">Price Label <span class="text-muted fw-normal">(required only when "Show price" is on)</span></label>
        <input type="text" class="form-control <?= isset($errors['price_label']) ? 'is-invalid' : '' ?>" name="price_label" value="<?= e((string)pe_val($old, $product, 'price_label')) ?>" placeholder="e.g. Starting ₹85 / sqft">
        <?php if (isset($errors['price_label'])): ?><div class="invalid-feedback"><?= e($errors['price_label']) ?></div><?php endif; ?>
      </div>
      <div class="mb-2">
        <label class="form-label">Was-Price <span class="text-muted fw-normal">(optional, struck through)</span></label>
        <input type="text" class="form-control" name="price_was" value="<?= e((string)pe_val($old, $product, 'price_was')) ?>">
      </div>
      <div class="mb-2">
        <label class="form-label">Badge <span class="text-muted fw-normal">(optional, e.g. Bestseller)</span></label>
        <input type="text" class="form-control" name="badge" value="<?= e((string)pe_val($old, $product, 'badge')) ?>">
      </div>
      <div class="mb-2 d-flex align-items-end">
        <div class="form-check form-switch mb-2">
          <input type="checkbox" class="form-check-input" role="switch" id="pf-deal" name="is_deal" <?= pe_val($old, $product, 'is_deal') ? 'checked' : '' ?>>
          <label class="form-check-label" for="pf-deal">Today's Deal <span class="text-muted fw-normal">(only one product at a time)</span></label>
        </div>
      </div>
      <div class="mb-2 d-flex align-items-end">
        <div class="form-check form-switch mb-2">
          <input type="checkbox" class="form-check-input" role="switch" id="pf-show-price" name="show_price" <?= pe_val($old, $product, 'show_price') ? 'checked' : '' ?>>
          <label class="form-check-label" for="pf-show-price">Show price on site
            <span class="text-muted fw-normal d-block">Off = the price is hidden everywhere and a "Get Quote" button takes its place on the product card.</span>
          </label>
        </div>
      </div>
    </div>
  </div>

  <div class="gd-admin-card">
    <h2>Images *</h2>
    <?php gd_image_list_field('images', 'products', $product['images'] ?? [], 'Product Images'); ?>
    <?php if (isset($errors['images'])): ?><div class="text-danger small"><?= e($errors['images']) ?></div><?php endif; ?>
  </div>

  <div class="gd-admin-card">
    <h2>Features *</h2>
    <label class="form-label">One per line</label>
    <textarea class="form-control <?= isset($errors['features']) ? 'is-invalid' : '' ?>" name="features" rows="4"><?= e(pe_lines($old, $product, 'features')) ?></textarea>
    <?php if (isset($errors['features'])): ?><div class="invalid-feedback"><?= e($errors['features']) ?></div><?php endif; ?>
  </div>

  <div class="gd-admin-card">
    <h2>Specifications <span class="text-muted fw-normal">(optional)</span></h2>
    <div data-gd-repeat>
      <div class="gd-repeat-list" data-gd-repeat-list>
        <?php $specs = $product['specs'] ?? []; foreach ($specs as $k => $val): ?>
          <div class="gd-repeat-row">
            <div class="gd-repeat-row-fields">
              <input type="text" class="form-control form-control-sm" name="spec_key[]" value="<?= e($k) ?>" placeholder="Spec name, e.g. Pile Height">
              <input type="text" class="form-control form-control-sm" name="spec_value[]" value="<?= e($val) ?>" placeholder="Value, e.g. 40 mm">
            </div>
            <button type="button" class="gd-repeat-row-remove" data-gd-repeat-remove><i class="bi bi-x-lg"></i></button>
          </div>
        <?php endforeach; ?>
      </div>
      <template data-gd-repeat-template>
        <div class="gd-repeat-row">
          <div class="gd-repeat-row-fields">
            <input type="text" class="form-control form-control-sm" name="spec_key[]" placeholder="Spec name">
            <input type="text" class="form-control form-control-sm" name="spec_value[]" placeholder="Value">
          </div>
          <button type="button" class="gd-repeat-row-remove" data-gd-repeat-remove><i class="bi bi-x-lg"></i></button>
        </div>
      </template>
      <button type="button" class="btn btn-sm btn-outline-secondary gd-repeat-add" data-gd-repeat-add><i class="bi bi-plus-lg"></i> Add spec</button>
    </div>
  </div>

  <div class="gd-admin-card">
    <h2>Completed Installations <span class="text-muted fw-normal">(optional gallery)</span></h2>
    <?php gd_image_list_field('installations', 'installations', $product['installations'] ?? [], 'Installation Photos'); ?>
  </div>

  <div class="gd-admin-card">
    <h2>SEO <span class="text-muted fw-normal">(optional)</span></h2>
    <div class="mb-2">
      <label class="form-label">Meta Title <span class="text-muted fw-normal">(≤60 chars recommended)</span></label>
      <input type="text" class="form-control" name="meta_title" value="<?= e((string)pe_val($old, $product, 'meta_title')) ?>">
    </div>
    <div class="mb-2">
      <label class="form-label">Meta Description <span class="text-muted fw-normal">(≤160 chars recommended)</span></label>
      <textarea class="form-control" name="meta_desc" rows="2"><?= e((string)pe_val($old, $product, 'meta_desc')) ?></textarea>
    </div>
  </div>

  <button type="submit" class="btn btn-gd-primary-a"><i class="bi bi-check-lg"></i> Save Product</button>
  <a href="<?= e(gd_admin_url('/modules/products.php')) ?>" class="btn btn-outline-secondary">Cancel</a>
</form>
<?php require __DIR__ . '/../inc/layout-bottom.php'; ?>
