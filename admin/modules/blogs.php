<?php
/** Blogs — list screen. Editing happens in blog-edit.php (Section 8.15). */
require __DIR__ . '/../inc/guard.php';

$blogs = Store::load('blogs');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['gd_action'] ?? '';
    $index  = isset($_GET['i']) ? (int)$_GET['i'] : null;

    if ($action === 'delete' && $index !== null && isset($blogs[$index])) {
        gd_log_activity('Deleted blog post', $blogs[$index]['title'] ?? '');
        array_splice($blogs, $index, 1);
        Store::save('blogs', array_values($blogs));
        flash('success', 'Post deleted.');
        header('Location: ' . gd_admin_url('/modules/blogs.php')); exit;
    }
    if ($action === 'move' && $index !== null && isset($blogs[$index])) {
        $dir = $_POST['dir'] ?? 'up';
        $swap = $dir === 'up' ? $index - 1 : $index + 1;
        if (isset($blogs[$swap])) {
            [$blogs[$index], $blogs[$swap]] = [$blogs[$swap], $blogs[$index]];
            Store::save('blogs', $blogs);
        }
        header('Location: ' . gd_admin_url('/modules/blogs.php')); exit;
    }
}

$adminTitle  = 'Blogs';
$adminActive = 'blogs';
require __DIR__ . '/../inc/layout-top.php';
?>
<div class="gd-admin-toolbar">
  <div><h1>Blogs</h1><p>The Journal listing. The first post here is the featured cover story.</p></div>
  <a href="<?= e(gd_admin_url('/modules/blog-edit.php?action=add')) ?>" class="btn btn-gd-primary-a"><i class="bi bi-plus-lg"></i> Add post</a>
</div>

<div class="gd-admin-table-wrap">
  <?php if (!$blogs): ?>
    <div class="gd-admin-table-empty">No posts yet.</div>
  <?php else: ?>
    <table class="gd-admin-table">
      <thead><tr><th style="width:70px;">Order</th><th>Image</th><th>Title</th><th>Author</th><th>Date</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($blogs as $i => $b): ?>
          <tr>
            <td>
              <div class="gd-admin-order-controls">
                <form method="post" action="<?= e(gd_admin_url('/modules/blogs.php?i=' . $i)) ?>"><?= csrf_field() ?>
                  <input type="hidden" name="gd_action" value="move"><input type="hidden" name="dir" value="up">
                  <button type="submit" <?= $i === 0 ? 'disabled' : '' ?>><i class="bi bi-caret-up-fill"></i></button>
                </form>
                <form method="post" action="<?= e(gd_admin_url('/modules/blogs.php?i=' . $i)) ?>"><?= csrf_field() ?>
                  <input type="hidden" name="gd_action" value="move"><input type="hidden" name="dir" value="down">
                  <button type="submit" <?= $i === count($blogs) - 1 ? 'disabled' : '' ?>><i class="bi bi-caret-down-fill"></i></button>
                </form>
              </div>
              <?php if ($i === 0): ?><div class="small text-success mt-1">Featured</div><?php endif; ?>
            </td>
            <td><?php if (!empty($b['image'])): ?><img class="gd-admin-table-thumb" src="<?= e(gd_img($b['image'], 'blog')) ?>" alt=""><?php endif; ?></td>
            <td><?= e($b['title']) ?><div class="text-muted small"><?= e($b['slug']) ?></div></td>
            <td><?= e($b['author']) ?></td>
            <td><?= e($b['date']) ?></td>
            <td>
              <div class="gd-admin-actions">
                <a class="btn btn-sm btn-outline-secondary" href="<?= e(gd_admin_url('/modules/blog-edit.php?action=edit&i=' . $i)) ?>"><i class="bi bi-pencil-fill"></i></a>
                <form method="post" action="<?= e(gd_admin_url('/modules/blogs.php?i=' . $i)) ?>" data-gd-confirm="Delete this post?">
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
