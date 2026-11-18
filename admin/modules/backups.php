<?php
/**
 * Backup / Restore — browse the last 20 auto-saved versions of any content
 * file and roll back to one with a click (Section P1-5 of docs/update.md).
 * Every Store::save() already keeps these; this is just the missing UI.
 */
require __DIR__ . '/../inc/guard.php';

const GD_BACKUP_LABELS = [
    'site' => 'Site Settings', 'config' => 'Site Settings (Brand/SEO)', 'festival' => 'Festival Banner',
    'quote_popup' => 'Quote Popup', 'page_content' => 'Page Copy', 'hero' => 'Hero Slides',
    'categories' => 'Categories', 'products' => 'Products', 'stats' => 'Stats', 'clients' => 'Clients',
    'faqs' => 'FAQs (site-wide)', 'page_faqs' => 'FAQs (per-page)', 'services' => 'Services',
    'service_details' => 'Service Details', 'why_us' => 'Why Us', 'process' => 'Process Steps',
    'team' => 'Team', 'certifications' => 'Certifications', 'reviews' => 'Reviews', 'blogs' => 'Blogs',
    'nav' => 'Navigation Menu', 'admin-users' => 'Admin Accounts',
];

function gd_backup_label(string $key): string {
    if (isset(GD_BACKUP_LABELS[$key])) return GD_BACKUP_LABELS[$key];
    if (str_starts_with($key, 'landing-')) return 'Landing Page — ' . substr($key, 8);
    return $key;
}

/** Maps a backup key to the real file it should be restored into. */
function gd_backup_target_file(string $key): string {
    if (str_starts_with($key, 'landing-')) {
        return __DIR__ . '/../../data/landing/' . substr($key, 8) . '.php';
    }
    if ($key === 'admin-users') {
        return __DIR__ . '/../config/admin-users.php';
    }
    return __DIR__ . '/../../data/content/' . $key . '.php';
}

// Not useful to show/restore from this screen — a rate-limit cache, not content.
const GD_BACKUP_HIDDEN_KEYS = ['login-attempts'];

$key = $_GET['key'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $postKey = $_POST['key'] ?? '';
    $file    = $_POST['file'] ?? '';
    if ($postKey !== '' && $file !== '' && !in_array($postKey, GD_BACKUP_HIDDEN_KEYS, true)) {
        $ok = Store::restorePath(gd_backup_target_file($postKey), $file, $postKey);
        if ($ok) gd_log_activity('Restored backup', gd_backup_label($postKey) . ' (' . basename($file) . ')');
        flash($ok ? 'success' : 'error', $ok ? 'Restored "' . gd_backup_label($postKey) . '" from that backup.' : 'Could not restore — the backup file looked invalid.');
    }
    header('Location: ' . gd_admin_url('/modules/backups.php' . ($postKey ? '?key=' . urlencode($postKey) : '')));
    exit;
}

$allKeys = array_values(array_diff(Store::allBackupKeys(), GD_BACKUP_HIDDEN_KEYS));

$adminTitle  = 'Backups & Restore';
$adminActive = 'backups';
require __DIR__ . '/../inc/layout-top.php';
?>
<div class="gd-admin-toolbar">
  <div><h1>Backups &amp; Restore</h1><p>Every save keeps the last 20 versions of that file. Roll back any single one — the current version is backed up first, so a restore is never a dead end either.</p></div>
</div>

<?php if ($key && in_array($key, $allKeys, true)): ?>
  <div class="gd-admin-toolbar">
    <div><h2 class="h5 mb-0"><?= e(gd_backup_label($key)) ?></h2></div>
    <a href="<?= e(gd_admin_url('/modules/backups.php')) ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> All backups</a>
  </div>
  <div class="gd-admin-table-wrap">
    <?php $versions = Store::backups($key); ?>
    <?php if (!$versions): ?>
      <div class="gd-admin-table-empty">No backups for this yet.</div>
    <?php else: ?>
      <table class="gd-admin-table">
        <thead><tr><th>Saved at</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($versions as $i => $v): ?>
            <tr>
              <td><?= e(date('D, j M Y — g:i:s A', $v['time'])) ?><?= $i === 0 ? ' <span class="badge text-bg-secondary">most recent</span>' : '' ?></td>
              <td>
                <form method="post" data-gd-confirm="Restore &quot;<?= e(gd_backup_label($key)) ?>&quot; to this version from <?= e(date('j M, g:i A', $v['time'])) ?>? The current version will be backed up first.">
                  <?= csrf_field() ?>
                  <input type="hidden" name="key" value="<?= e($key) ?>">
                  <input type="hidden" name="file" value="<?= e($v['file']) ?>">
                  <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-arrow-counterclockwise"></i> Restore this version</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
<?php else: ?>
  <div class="gd-admin-table-wrap">
    <?php if (!$allKeys): ?>
      <div class="gd-admin-table-empty">No backups yet — they appear automatically the first time you edit and save anything.</div>
    <?php else: ?>
      <table class="gd-admin-table">
        <thead><tr><th>Content</th><th>Versions kept</th><th>Most recent save</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($allKeys as $k): $versions = Store::backups($k); if (!$versions) continue; ?>
            <tr>
              <td><?= e(gd_backup_label($k)) ?></td>
              <td><?= count($versions) ?></td>
              <td><?= e(date('D, j M Y — g:i A', $versions[0]['time'])) ?></td>
              <td><a class="btn btn-sm btn-outline-secondary" href="<?= e(gd_admin_url('/modules/backups.php?key=' . urlencode($k))) ?>">View versions</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/../inc/layout-bottom.php'; ?>
