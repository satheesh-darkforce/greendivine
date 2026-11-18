<?php
/** Activity Log — read-only view of data/security/activity.log (Section P2 of docs/update.md). */
require __DIR__ . '/../inc/guard.php';

const GD_ACTIVITY_LOG_SHOW = 300;

$logFile = __DIR__ . '/../../data/security/activity.log';
$lines = [];
if (is_file($logFile)) {
    $all = file($logFile, FILE_IGNORE_NEW_LINES) ?: [];
    $lines = array_reverse(array_slice($all, -GD_ACTIVITY_LOG_SHOW));
}

$adminTitle  = 'Activity Log';
$adminActive = 'activity-log';
require __DIR__ . '/../inc/layout-top.php';
?>
<div class="gd-admin-toolbar">
  <div><h1>Activity Log</h1><p>Every save, delete and restore across the console, most recent first<?= count($lines) >= GD_ACTIVITY_LOG_SHOW ? ' (last ' . GD_ACTIVITY_LOG_SHOW . ' entries)' : '' ?>.</p></div>
</div>

<div class="gd-admin-card">
  <?php if (!$lines): ?>
    <p class="text-muted mb-0">Nothing logged yet — entries appear as soon as anything is added, edited, deleted or restored.</p>
  <?php else: ?>
    <div style="max-height:70vh; overflow-y:auto;">
      <pre class="mb-0 small" style="white-space:pre-wrap; font-family: ui-monospace, 'Cascadia Code', Consolas, monospace;"><?php foreach ($lines as $l) echo e($l) . "\n"; ?></pre>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../inc/layout-bottom.php'; ?>
