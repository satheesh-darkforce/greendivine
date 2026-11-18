<?php
/**
 * Reusable "How It Works" stepper — an interactive tabbed process, not a
 * static grid. A row of connected numbered tabs up top; clicking one swaps
 * a single detail panel below (main.js wires this up via [data-gd-steps]).
 *
 * Expects: $steps (array) — list of ['icon' => 'bi-...', 'title' => '...', 'desc' => '...'].
 * Reused by home.php, about.php, services.php and category.php, each with
 * their own heading passed as $processTitle / $processEyebrow / $processLede.
 */
if (empty($steps)) return;
?>
<div class="gd-steps" data-gd-steps>
  <div class="gd-steps-nav" role="tablist">
    <?php foreach ($steps as $i => $p): ?>
      <button type="button" class="gd-steps-tab <?= $i === 0 ? 'active' : '' ?>"
              data-gd-step-tab="<?= $i ?>" role="tab" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>">
        <span class="gd-steps-tab-num"><?= sprintf('%02d', $i + 1) ?></span>
        <span class="gd-steps-tab-label"><?= e($p['title']) ?></span>
      </button>
    <?php endforeach; ?>
  </div>

  <div class="gd-steps-panels">
    <?php foreach ($steps as $i => $p): ?>
      <div class="gd-steps-panel <?= $i === 0 ? 'active' : '' ?>" data-gd-step-panel="<?= $i ?>">
        <span class="gd-steps-panel-ghost"><?= sprintf('%02d', $i + 1) ?></span>
        <div class="gd-steps-panel-icon"><i class="bi <?= e($p['icon']) ?>"></i></div>
        <h3><?= e($p['title']) ?></h3>
        <p><?= e($p['desc']) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</div>
