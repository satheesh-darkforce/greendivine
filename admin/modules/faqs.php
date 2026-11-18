<?php
/**
 * FAQs — site-wide fallback + one tab per page (Section 8.16). Bespoke
 * rather than the generic simple-list engine because "page" tabs are
 * slices of ONE shared file (page_faqs.php) rather than each its own
 * data/content/<key>.php — load/save needs a thin key-aware wrapper.
 */
require __DIR__ . '/../inc/guard.php';

$tabs = ['sitewide' => 'Site-wide', 'home' => 'Home', 'products' => 'Products', 'services' => 'Services',
         'about' => 'About', 'blogs' => 'Blogs', 'contact' => 'Contact'];
$tab = $_GET['tab'] ?? 'sitewide';
if (!isset($tabs[$tab])) $tab = 'sitewide';

function faqs_load(string $tab): array {
    if ($tab === 'sitewide') return Store::load('faqs');
    $pf = Store::load('page_faqs');
    return $pf[$tab] ?? [];
}
function faqs_save(string $tab, array $rows): void {
    if ($tab === 'sitewide') { Store::save('faqs', array_values($rows)); return; }
    $pf = Store::load('page_faqs');
    $pf[$tab] = array_values($rows);
    Store::save('page_faqs', $pf);
}

$rows = faqs_load($tab);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['gd_action'] ?? '';
    $index  = isset($_GET['i']) ? (int)$_GET['i'] : null;

    if ($action === 'delete' && $index !== null && isset($rows[$index])) {
        gd_log_activity('Deleted FAQ (' . $tabs[$tab] . ')', $rows[$index]['q'] ?? '');
        array_splice($rows, $index, 1);
        faqs_save($tab, $rows);
        flash('success', 'FAQ deleted.' . ($tab !== 'sitewide' && !$rows ? ' This page will now show the site-wide FAQs.' : ''));
        header('Location: ' . gd_admin_url('/modules/faqs.php?tab=' . $tab)); exit;
    }
    if ($action === 'move' && $index !== null && isset($rows[$index])) {
        $dir = $_POST['dir'] ?? 'up';
        $swap = $dir === 'up' ? $index - 1 : $index + 1;
        if (isset($rows[$swap])) { [$rows[$index], $rows[$swap]] = [$rows[$swap], $rows[$index]]; faqs_save($tab, $rows); }
        header('Location: ' . gd_admin_url('/modules/faqs.php?tab=' . $tab)); exit;
    }
    if ($action === 'save') {
        $isNew = $_POST['gd_index'] === 'new';
        $i = $isNew ? null : (int)$_POST['gd_index'];
        $q = trim((string)($_POST['q'] ?? ''));
        $a = trim((string)($_POST['a'] ?? ''));
        $v = new V();
        $v->required('q', $q, 'Question')->maxlen('q', $q, 150, 'Question');
        $v->required('a', $a, 'Answer')->maxlen('a', $a, 500, 'Answer');
        if (!$v->ok()) {
            fail_back('/modules/faqs.php?tab=' . $tab . '&action=' . ($isNew ? 'add' : 'edit') . ($isNew ? '' : '&i=' . $i), $v->errors(), ['q' => $q, 'a' => $a]);
        }
        if ($isNew) $rows[] = ['q' => $q, 'a' => $a]; else $rows[$i] = ['q' => $q, 'a' => $a];
        faqs_save($tab, $rows);
        gd_log_activity(($isNew ? 'Added' : 'Edited') . ' FAQ (' . $tabs[$tab] . ')', $q);
        flash('success', 'FAQ saved.');
        header('Location: ' . gd_admin_url('/modules/faqs.php?tab=' . $tab)); exit;
    }
}

$action = $_GET['action'] ?? 'list';
$editIndex = isset($_GET['i']) ? (int)$_GET['i'] : null;
$old = old_input();
$errors = old_errors();

$adminTitle  = 'FAQs';
$adminActive = 'faqs';
require __DIR__ . '/../inc/layout-top.php';
?>
<div class="gd-admin-toolbar">
  <div><h1>FAQs</h1><p>The site-wide fallback list, plus one override list per page. A page with no FAQs of its own shows the site-wide list automatically.</p></div>
</div>

<ul class="nav nav-tabs mb-3">
  <?php foreach ($tabs as $key => $label): ?>
    <li class="nav-item">
      <a class="nav-link <?= $tab === $key ? 'active' : '' ?>" href="<?= e(gd_admin_url('/modules/faqs.php?tab=' . $key)) ?>"><?= e($label) ?></a>
    </li>
  <?php endforeach; ?>
</ul>

<?php if (($action === 'add' || ($action === 'edit' && $editIndex !== null && isset($rows[$editIndex]))) ): ?>
  <?php $record = $action === 'edit' ? $rows[$editIndex] : null; ?>
  <div class="gd-admin-toolbar">
    <div><h2 class="h5 mb-0"><?= $action === 'add' ? 'Add' : 'Edit' ?> FAQ &mdash; <?= e($tabs[$tab]) ?></h2></div>
    <a href="<?= e(gd_admin_url('/modules/faqs.php?tab=' . $tab)) ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
  </div>
  <form method="post" class="gd-admin-card">
    <?= csrf_field() ?>
    <input type="hidden" name="gd_action" value="save">
    <input type="hidden" name="gd_index" value="<?= $action === 'add' ? 'new' : (int)$editIndex ?>">
    <div class="mb-2">
      <label class="form-label">Question *</label>
      <input type="text" class="form-control <?= isset($errors['q']) ? 'is-invalid' : '' ?>" name="q" value="<?= e((string)($old['q'] ?? $record['q'] ?? '')) ?>">
      <?php if (isset($errors['q'])): ?><div class="invalid-feedback"><?= e($errors['q']) ?></div><?php endif; ?>
    </div>
    <div class="mb-2">
      <label class="form-label">Answer *</label>
      <textarea class="form-control <?= isset($errors['a']) ? 'is-invalid' : '' ?>" name="a" rows="4"><?= e((string)($old['a'] ?? $record['a'] ?? '')) ?></textarea>
      <?php if (isset($errors['a'])): ?><div class="invalid-feedback"><?= e($errors['a']) ?></div><?php endif; ?>
    </div>
    <button type="submit" class="btn btn-gd-primary-a"><i class="bi bi-check-lg"></i> Save</button>
    <a href="<?= e(gd_admin_url('/modules/faqs.php?tab=' . $tab)) ?>" class="btn btn-outline-secondary">Cancel</a>
  </form>
<?php else: ?>
  <div class="gd-admin-toolbar">
    <div></div>
    <a href="<?= e(gd_admin_url('/modules/faqs.php?tab=' . $tab . '&action=add')) ?>" class="btn btn-gd-primary-a"><i class="bi bi-plus-lg"></i> Add FAQ</a>
  </div>
  <div class="gd-admin-table-wrap">
    <?php if (!$rows): ?>
      <div class="gd-admin-table-empty">No FAQs <?= $tab === 'sitewide' ? 'yet' : 'for this page — the site-wide list is shown instead' ?>.</div>
    <?php else: ?>
      <table class="gd-admin-table">
        <thead><tr><th style="width:70px;">Order</th><th>Question</th><th>Answer</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($rows as $i => $r): ?>
            <tr>
              <td>
                <div class="gd-admin-order-controls">
                  <form method="post" action="<?= e(gd_admin_url('/modules/faqs.php?tab=' . $tab . '&i=' . $i)) ?>"><?= csrf_field() ?>
                    <input type="hidden" name="gd_action" value="move"><input type="hidden" name="dir" value="up">
                    <button type="submit" <?= $i === 0 ? 'disabled' : '' ?>><i class="bi bi-caret-up-fill"></i></button>
                  </form>
                  <form method="post" action="<?= e(gd_admin_url('/modules/faqs.php?tab=' . $tab . '&i=' . $i)) ?>"><?= csrf_field() ?>
                    <input type="hidden" name="gd_action" value="move"><input type="hidden" name="dir" value="down">
                    <button type="submit" <?= $i === count($rows) - 1 ? 'disabled' : '' ?>><i class="bi bi-caret-down-fill"></i></button>
                  </form>
                </div>
              </td>
              <td><?= e(mb_strimwidth($r['q'], 0, 70, '…')) ?></td>
              <td><?= e(mb_strimwidth($r['a'], 0, 90, '…')) ?></td>
              <td>
                <div class="gd-admin-actions">
                  <a class="btn btn-sm btn-outline-secondary" href="<?= e(gd_admin_url('/modules/faqs.php?tab=' . $tab . '&action=edit&i=' . $i)) ?>"><i class="bi bi-pencil-fill"></i></a>
                  <form method="post" action="<?= e(gd_admin_url('/modules/faqs.php?tab=' . $tab . '&i=' . $i)) ?>" data-gd-confirm="Delete this FAQ?">
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
<?php endif; ?>
<?php require __DIR__ . '/../inc/layout-bottom.php'; ?>
