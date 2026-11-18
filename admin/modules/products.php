<?php
/** Products — list screen. Editing happens in product-edit.php (Section 8.6). */
require __DIR__ . '/../inc/guard.php';

$products = Store::load('products');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['gd_action'] ?? '';
    $index  = isset($_GET['i']) ? (int)$_GET['i'] : null;

    if ($action === 'delete' && $index !== null && isset($products[$index])) {
        gd_log_activity('Deleted product', $products[$index]['name'] ?? '');
        array_splice($products, $index, 1);
        Store::save('products', array_values($products));
        flash('success', 'Product deleted.');
        header('Location: ' . gd_admin_url('/modules/products.php')); exit;
    }

    if ($action === 'move' && $index !== null && isset($products[$index])) {
        $dir  = $_POST['dir'] ?? 'up';
        $swap = $dir === 'up' ? $index - 1 : $index + 1;
        if (isset($products[$swap])) {
            [$products[$index], $products[$swap]] = [$products[$swap], $products[$index]];
            Store::save('products', $products);
        }
        header('Location: ' . gd_admin_url('/modules/products.php')); exit;
    }
}

$adminTitle  = 'Products';
$adminActive = 'products';
require __DIR__ . '/../inc/layout-top.php';
?>
<div class="gd-admin-toolbar">
  <div>
    <h1>Products</h1>
    <p>The full catalogue. "Today's Deal" on the home page always shows whichever one product has the deal badge on.</p>
  </div>
  <a href="<?= e(gd_admin_url('/modules/product-edit.php?action=add')) ?>" class="btn btn-gd-primary-a">
    <i class="bi bi-plus-lg"></i> Add product
  </a>
</div>

<div class="gd-admin-table-wrap">
  <?php if (!$products): ?>
    <div class="gd-admin-table-empty">No products yet.</div>
  <?php else: ?>
    <table class="gd-admin-table">
      <thead><tr>
        <th style="width:70px;">Order</th><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Deal</th><th></th>
      </tr></thead>
      <tbody>
        <?php foreach ($products as $i => $p): $cat = gd_find_category($p['category']); ?>
          <tr>
            <td>
              <div class="gd-admin-order-controls">
                <form method="post" action="<?= e(gd_admin_url('/modules/products.php?i=' . $i)) ?>"><?= csrf_field() ?>
                  <input type="hidden" name="gd_action" value="move"><input type="hidden" name="dir" value="up">
                  <button type="submit" <?= $i === 0 ? 'disabled' : '' ?>><i class="bi bi-caret-up-fill"></i></button>
                </form>
                <form method="post" action="<?= e(gd_admin_url('/modules/products.php?i=' . $i)) ?>"><?= csrf_field() ?>
                  <input type="hidden" name="gd_action" value="move"><input type="hidden" name="dir" value="down">
                  <button type="submit" <?= $i === count($products) - 1 ? 'disabled' : '' ?>><i class="bi bi-caret-down-fill"></i></button>
                </form>
              </div>
            </td>
            <td><?php if (!empty($p['images'][0])): ?><img class="gd-admin-table-thumb" src="<?= e(gd_img($p['images'][0], 'products')) ?>" alt=""><?php endif; ?></td>
            <td><?= e($p['name']) ?><div class="text-muted small"><?= e($p['slug']) ?></div></td>
            <td><?= e($cat['name'] ?? $p['category']) ?></td>
            <td>
              <?php if (gd_show_price($p)): ?>
                <?= e($p['price_label']) ?>
              <?php else: ?>
                <span class="text-muted">Quote only</span>
                <?php if (!empty($p['price_label'])): ?>
                  <div class="text-muted small">(<?= e($p['price_label']) ?> — hidden)</div>
                <?php endif; ?>
              <?php endif; ?>
            </td>
            <td><?= !empty($p['is_deal']) ? '<span class="badge text-bg-success">Today\'s Deal</span>' : '' ?></td>
            <td>
              <div class="gd-admin-actions">
                <a class="btn btn-sm btn-outline-secondary" href="<?= e(gd_admin_url('/modules/product-edit.php?action=edit&i=' . $i)) ?>"><i class="bi bi-pencil-fill"></i></a>
                <form method="post" action="<?= e(gd_admin_url('/modules/products.php?i=' . $i)) ?>" data-gd-confirm="Delete &quot;<?= e($p['name']) ?>&quot;? This can't be undone from the UI (a backup is kept).">
                  <?= csrf_field() ?><input type="hidden" name="gd_action" value="delete">
                  <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3-fill"></i></button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../inc/layout-bottom.php'; ?>
