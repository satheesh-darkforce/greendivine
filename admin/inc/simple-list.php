<?php
/**
 * Generic "list of flat records" admin module — powers every content type
 * that's just an array of similarly-shaped items with add/edit/delete/
 * reorder (Navigation, Services, Why Us, Process Steps, Stats, Team,
 * Clients, Certifications). One call from a tiny module file:
 *
 *   gd_run_simple_list([
 *     'key' => 'clients', 'title' => 'Clients', 'active' => 'clients',
 *     'itemLabel' => 'client', 'columns' => ['logo' => 'Logo', 'name' => 'Name'],
 *     'fields' => ['name' => ['label'=>'Name','type'=>'text','required'=>true,'maxlen'=>60],
 *                  'logo' => ['label'=>'Logo','type'=>'image','bucket'=>'clients','required'=>true]],
 *   ]);
 *
 * Field types: text, textarea, number, image. Set 'fixedCount' => N to
 * lock the list to exactly N items (Process Steps, Stats) — hides Add/
 * Delete, keeps Edit + reorder. Set 'extraValidate' => function(V $v,
 * array $input): V for a rule too specific to belong in the generic engine
 * (e.g. Navigation's route-must-exist check).
 */

function gd_run_simple_list(array $config): void {
    global $DATA;

    $key        = $config['key'];
    $fields     = $config['fields'];
    $itemLabel  = $config['itemLabel'] ?? 'item';
    $fixedCount = $config['fixedCount'] ?? null;
    $records    = Store::load($key);

    $action = $_GET['action'] ?? 'list';
    $index  = isset($_GET['i']) ? (int)$_GET['i'] : null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_check();
        $postAction = $_POST['gd_action'] ?? '';

        if ($postAction === 'delete' && $index !== null && isset($records[$index]) && !$fixedCount) {
            $blockReason = isset($config['canDelete']) ? $config['canDelete']($records[$index], $index, $records) : null;
            if ($blockReason !== null) {
                flash('error', $blockReason);
            } else {
                gd_log_activity('Deleted ' . $itemLabel, gd_record_label($records[$index]));
                array_splice($records, $index, 1);
                Store::save($key, array_values($records));
                flash('success', ucfirst($itemLabel) . ' deleted.');
            }
            header('Location: ' . gd_admin_url('/modules/' . basename($_SERVER['SCRIPT_NAME']))); exit;
        }

        if ($postAction === 'move' && $index !== null && isset($records[$index])) {
            $dir = $_POST['dir'] ?? 'up';
            $swap = $dir === 'up' ? $index - 1 : $index + 1;
            if (isset($records[$swap])) {
                [$records[$index], $records[$swap]] = [$records[$swap], $records[$index]];
                Store::save($key, $records);
            }
            header('Location: ' . gd_admin_url('/modules/' . basename($_SERVER['SCRIPT_NAME']))); exit;
        }

        if ($postAction === 'save') {
            $isNew = $_POST['gd_index'] === 'new';
            $i     = $isNew ? null : (int)$_POST['gd_index'];
            $input = [];
            $v     = new V();

            foreach ($fields as $name => $spec) {
                $type = $spec['type'] ?? 'text';
                if ($type === 'image') {
                    $previous = $isNew ? '' : ($records[$i][$name] ?? '');
                    $img = gd_resolve_image($name, $spec['bucket'] ?? '', $previous);
                    if (!$img['ok']) { $v->fail($name, $img['error']); $input[$name] = $previous; continue; }
                    $input[$name] = $img['name'] ?? '';
                } elseif ($type === 'lines') {
                    $input[$name] = gd_lines_to_list($_POST[$name] ?? '');
                } else {
                    $input[$name] = trim((string)($_POST[$name] ?? ''));
                }

                if ($type === 'lines') {
                    if (!empty($spec['required'])) $v->minCount($name, $input[$name], (int)($spec['minCount'] ?? 1), $spec['label']);
                    continue;
                }
                if (!empty($spec['required'])) $v->required($name, $input[$name], $spec['label']);
                if (!empty($spec['maxlen']))    $v->maxlen($name, (string)$input[$name], $spec['maxlen'], $spec['label']);
                if ($type === 'number' && $input[$name] !== '') {
                    $v->intRange($name, $input[$name], $spec['min'] ?? 0, $spec['max'] ?? 999999999, $spec['label']);
                }
                if ($type === 'select' && isset($spec['options']) && $input[$name] !== '') {
                    $v->oneOf($name, $input[$name], array_keys($spec['options']), $spec['label']);
                }
            }

            if (isset($config['extraValidate'])) $v = $config['extraValidate']($v, $input);

            if (!$v->ok()) {
                fail_back('/modules/' . basename($_SERVER['SCRIPT_NAME']) . '?action=' . ($isNew ? 'add' : 'edit') . ($isNew ? '' : '&i=' . $i), $v->errors(), $input);
            }

            foreach ($fields as $name => $spec) {
                if (($spec['type'] ?? 'text') === 'number') $input[$name] = $input[$name] === '' ? 0 : (int)$input[$name];
            }

            if ($isNew) { $records[] = $input; }
            else { $records[$i] = $input; }

            Store::save($key, array_values($records));
            gd_log_activity(($isNew ? 'Added ' : 'Edited ') . $itemLabel, gd_record_label($input));
            flash('success', ucfirst($itemLabel) . ' saved.');
            header('Location: ' . gd_admin_url('/modules/' . basename($_SERVER['SCRIPT_NAME']))); exit;
        }
    }

    $adminTitle  = $config['title'];
    $adminActive = $config['active'];

    if (($action === 'add' && !$fixedCount) || ($action === 'edit' && $index !== null && isset($records[$index]))) {
        gd_render_simple_form($config, $records, $action === 'edit' ? $index : null);
        return;
    }

    gd_render_simple_list($config, $records);
}

function gd_render_simple_list(array $config, array $records): void {
    global $DATA;
    $itemLabel  = $config['itemLabel'] ?? 'item';
    $fixedCount = $config['fixedCount'] ?? null;
    $columns    = $config['columns'];
    $self       = basename($_SERVER['SCRIPT_NAME']);

    require __DIR__ . '/layout-top.php';
    ?>
    <div class="gd-admin-toolbar">
      <div>
        <h1><?= e($config['title']) ?></h1>
        <?php if (!empty($config['hint'])): ?><p><?= e($config['hint']) ?></p><?php endif; ?>
      </div>
      <?php if (!$fixedCount): ?>
        <a href="<?= e(gd_admin_url('/modules/' . $self . '?action=add')) ?>" class="btn btn-gd-primary-a">
          <i class="bi bi-plus-lg"></i> Add <?= e($itemLabel) ?>
        </a>
      <?php endif; ?>
    </div>

    <div class="gd-admin-table-wrap">
      <?php if (!$records): ?>
        <div class="gd-admin-table-empty">No <?= e($itemLabel) ?>s yet.</div>
      <?php else: ?>
        <table class="gd-admin-table">
          <thead><tr>
            <th style="width:70px;">Order</th>
            <?php foreach ($columns as $c): ?><th><?= e($c) ?></th><?php endforeach; ?>
            <th></th>
          </tr></thead>
          <tbody>
            <?php foreach ($records as $i => $r): ?>
              <tr>
                <td>
                  <div class="gd-admin-order-controls">
                    <form method="post" action="<?= e(gd_admin_url('/modules/' . $self . '?i=' . $i)) ?>"><?= csrf_field() ?>
                      <input type="hidden" name="gd_action" value="move"><input type="hidden" name="dir" value="up">
                      <button type="submit" <?= $i === 0 ? 'disabled' : '' ?> title="Move up"><i class="bi bi-caret-up-fill"></i></button>
                    </form>
                    <form method="post" action="<?= e(gd_admin_url('/modules/' . $self . '?i=' . $i)) ?>"><?= csrf_field() ?>
                      <input type="hidden" name="gd_action" value="move"><input type="hidden" name="dir" value="down">
                      <button type="submit" <?= $i === count($records) - 1 ? 'disabled' : '' ?> title="Move down"><i class="bi bi-caret-down-fill"></i></button>
                    </form>
                  </div>
                </td>
                <?php foreach (array_keys($columns) as $field): ?>
                  <td>
                    <?php if (($config['fields'][$field]['type'] ?? '') === 'image'): ?>
                      <?php if (!empty($r[$field])): ?>
                        <img class="gd-admin-table-thumb" src="<?= e(gd_img($r[$field], $config['fields'][$field]['bucket'] ?? '')) ?>" alt="">
                      <?php endif; ?>
                    <?php elseif (is_array($r[$field] ?? null)): ?>
                      <?= e(mb_strimwidth(implode(', ', $r[$field]), 0, 70, '…')) ?>
                    <?php else: ?>
                      <?= e(mb_strimwidth((string)($r[$field] ?? ''), 0, 70, '…')) ?>
                    <?php endif; ?>
                  </td>
                <?php endforeach; ?>
                <td>
                  <div class="gd-admin-actions">
                    <a class="btn btn-sm btn-outline-secondary" href="<?= e(gd_admin_url('/modules/' . $self . '?action=edit&i=' . $i)) ?>"><i class="bi bi-pencil-fill"></i></a>
                    <?php if (!$fixedCount): ?>
                      <form method="post" action="<?= e(gd_admin_url('/modules/' . $self . '?i=' . $i)) ?>" data-gd-confirm="Delete this <?= e($itemLabel) ?>?">
                        <?= csrf_field() ?><input type="hidden" name="gd_action" value="delete">
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3-fill"></i></button>
                      </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
    <?php
    require __DIR__ . '/layout-bottom.php';
}

function gd_render_simple_form(array $config, array $records, ?int $index): void {
    global $DATA;
    $itemLabel = $config['itemLabel'] ?? 'item';
    $self      = basename($_SERVER['SCRIPT_NAME']);
    $record    = $index !== null ? $records[$index] : null;
    $old       = old_input();
    $errors    = old_errors();

    $adminTitle  = ($index === null ? 'Add ' : 'Edit ') . $config['title'];
    $adminActive = $config['active'];

    require __DIR__ . '/layout-top.php';
    ?>
    <div class="gd-admin-toolbar">
      <div><h1><?= $index === null ? 'Add' : 'Edit' ?> <?= e($itemLabel) ?></h1></div>
      <a href="<?= e(gd_admin_url('/modules/' . $self)) ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to list</a>
    </div>

    <form method="post" enctype="multipart/form-data" class="gd-admin-card">
      <?= csrf_field() ?>
      <input type="hidden" name="gd_action" value="save">
      <input type="hidden" name="gd_index" value="<?= $index === null ? 'new' : (int)$index ?>">

      <?php foreach ($config['fields'] as $name => $spec): ?>
        <?php
          $type  = $spec['type'] ?? 'text';
          $value = gd_field($old, $record, $name, $spec['default'] ?? '');
        ?>
        <div class="mb-3">
          <?php if ($type === 'image'): ?>
            <?php gd_image_field($name, $spec['bucket'] ?? '', (string)$value, $spec['label']); ?>
          <?php elseif ($type === 'lines'): ?>
            <label class="form-label" for="f-<?= e($name) ?>"><?= e($spec['label']) ?><?= !empty($spec['required']) ? ' *' : '' ?></label>
            <textarea class="form-control <?= isset($errors[$name]) ? 'is-invalid' : '' ?>" id="f-<?= e($name) ?>" name="<?= e($name) ?>" rows="4"><?= e(is_array($value) ? implode("\n", $value) : (string)$value) ?></textarea>
            <div class="form-hint mt-1">One per line.</div>
          <?php elseif ($type === 'textarea'): ?>
            <label class="form-label" for="f-<?= e($name) ?>"><?= e($spec['label']) ?><?= !empty($spec['required']) ? ' *' : '' ?></label>
            <textarea class="form-control <?= isset($errors[$name]) ? 'is-invalid' : '' ?>" id="f-<?= e($name) ?>" name="<?= e($name) ?>" rows="3"><?= e((string)$value) ?></textarea>
          <?php elseif ($type === 'select'): ?>
            <label class="form-label" for="f-<?= e($name) ?>"><?= e($spec['label']) ?><?= !empty($spec['required']) ? ' *' : '' ?></label>
            <select class="form-select <?= isset($errors[$name]) ? 'is-invalid' : '' ?>" id="f-<?= e($name) ?>" name="<?= e($name) ?>">
              <?php if (empty($spec['required'])): ?><option value="">— None —</option><?php endif; ?>
              <?php foreach ($spec['options'] as $optVal => $optLabel): ?>
                <option value="<?= e((string)$optVal) ?>" <?= (string)$value === (string)$optVal ? 'selected' : '' ?>><?= e($optLabel) ?></option>
              <?php endforeach; ?>
            </select>
          <?php else: ?>
            <label class="form-label" for="f-<?= e($name) ?>"><?= e($spec['label']) ?><?= !empty($spec['required']) ? ' *' : '' ?></label>
            <input type="<?= $type === 'number' ? 'number' : 'text' ?>" class="form-control <?= isset($errors[$name]) ? 'is-invalid' : '' ?>"
                   id="f-<?= e($name) ?>" name="<?= e($name) ?>" value="<?= e((string)$value) ?>"
                   <?= $name === 'title' || $name === 'name' ? 'data-gd-slug-source-owner' : '' ?>>
          <?php endif; ?>
          <?php if (isset($errors[$name])): ?><div class="invalid-feedback"><?= e($errors[$name]) ?></div><?php endif; ?>
          <?php if (!empty($spec['hint'])): ?><div class="form-hint mt-1"><?= e($spec['hint']) ?></div><?php endif; ?>
        </div>
      <?php endforeach; ?>

      <button type="submit" class="btn btn-gd-primary-a"><i class="bi bi-check-lg"></i> Save</button>
      <a href="<?= e(gd_admin_url('/modules/' . $self)) ?>" class="btn btn-outline-secondary">Cancel</a>
    </form>
    <?php
    require __DIR__ . '/layout-bottom.php';
}
