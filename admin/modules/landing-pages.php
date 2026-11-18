<?php
/** Landing Pages — list screen: one per category, editing in landing-page-edit.php. */
require __DIR__ . '/../inc/guard.php';

$adminTitle  = 'Landing Pages';
$adminActive = 'landing-pages';
require __DIR__ . '/../inc/layout-top.php';
?>
<div class="gd-admin-toolbar">
  <div>
    <h1>Landing Pages</h1>
    <p>One SEO landing page per category, at the category's own URL (/&lt;slug&gt;).</p>
  </div>
</div>

<div class="gd-admin-table-wrap">
  <table class="gd-admin-table">
    <thead><tr><th>Category</th><th>Slug</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($DATA['categories'] as $c): $exists = gd_find_landing($c['slug']) !== null; ?>
        <tr>
          <td><?= e($c['name']) ?></td>
          <td><code>/<?= e($c['slug']) ?></code></td>
          <td><?= $exists ? '<span class="badge text-bg-success">Published</span>' : '<span class="badge text-bg-secondary">Not created</span>' ?></td>
          <td>
            <a class="btn btn-sm btn-outline-secondary" href="<?= e(gd_admin_url('/modules/landing-page-edit.php?slug=' . $c['slug'])) ?>">
              <?= $exists ? '<i class="bi bi-pencil-fill"></i> Edit' : '<i class="bi bi-plus-lg"></i> Create' ?>
            </a>
            <?php if ($exists): ?>
              <a class="btn btn-sm btn-outline-secondary" href="<?= e(gd_url('/' . $c['slug'])) ?>" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i></a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . '/../inc/layout-bottom.php'; ?>
