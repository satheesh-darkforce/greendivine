<?php
/**
 * Generic "one record, one form" admin module — powers content that isn't
 * a list (Settings, Quote Popup, Festival Banner). Field names may use
 * "a.b" dot-paths to reach into nested values (e.g. Festival's cta.label)
 * without flattening the stored shape. Every dot-path is translated through
 * gd_form_name() at the actual <input name="..."> / $_POST read site,
 * because PHP itself rewrites dots in submitted field names to underscores.
 *
 * Config:
 *   'key'    => default data/content/<key>.php for groups that don't override it
 *   'title', 'active'
 *   'groups' => [ ['label' => 'Section name', 'key' => optional-override, 'fields' => [ name => spec, ... ]], ... ]
 *   Field spec: label, type (text|textarea|number|toggle|image|select|url|email),
 *               required, maxlen, hint, bucket (image), options (select), min/max (number)
 *
 * Most modules save to a single file; Settings spans two (site.php +
 * config.php), so each group may set its own 'key' — every touched key is
 * loaded, merged and saved independently.
 */

function gd_run_singleton(array $config): void {
    global $DATA;
    $defaultKey = $config['key'] ?? null;

    $keys = [];
    foreach ($config['groups'] as $group) $keys[$group['key'] ?? $defaultKey] = true;
    $keys = array_keys($keys);

    $records = [];
    foreach ($keys as $k) $records[$k] = Store::load($k);

    $fieldsFlat = []; // name => spec (+ resolved _key)
    foreach ($config['groups'] as $group) {
        $gKey = $group['key'] ?? $defaultKey;
        foreach ($group['fields'] as $name => $spec) {
            $spec['_key'] = $gKey;
            $fieldsFlat[$name] = $spec;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_check();
        $inputByKey = [];
        foreach ($keys as $k) $inputByKey[$k] = [];
        $v = new V();

        foreach ($fieldsFlat as $name => $spec) {
            $type     = $spec['type'] ?? 'text';
            $gKey     = $spec['_key'];
            $record   = $records[$gKey];
            $formName = gd_form_name($name);

            if ($type === 'toggle') {
                $value = isset($_POST[$formName]);
            } elseif ($type === 'image') {
                $previous = (string)gd_dot_get($record, $name, '');
                $img = gd_resolve_image($formName, $spec['bucket'] ?? '', $previous);
                if (!$img['ok']) { $v->fail($name, $img['error']); $value = $previous; }
                else { $value = $img['name'] ?? ''; }
            } else {
                $value = trim((string)($_POST[$formName] ?? ''));
            }

            gd_dot_set($inputByKey[$gKey], $name, $value);

            if (!empty($spec['required'])) $v->required($name, $value, $spec['label']);
            if (!empty($spec['maxlen']) && is_string($value)) $v->maxlen($name, $value, $spec['maxlen'], $spec['label']);
            if ($type === 'email') $v->email($name, $value, $spec['label']);
            if ($type === 'url') $v->url($name, $value, $spec['label']);
            if ($type === 'number' && $value !== '') $v->intRange($name, $value, $spec['min'] ?? 0, $spec['max'] ?? 999999999, $spec['label']);
            if ($type === 'select' && isset($spec['options'])) $v->oneOf($name, $value, array_keys($spec['options']), $spec['label']);
        }

        if (isset($config['extraValidate'])) $v = $config['extraValidate']($v, $inputByKey);

        // Combined nested snapshot of everything just submitted, keyed the
        // same way records are (dot-paths resolved) — used to repopulate
        // the form untouched if validation fails, regardless of which
        // underlying file(s) a field belongs to.
        $oldCombined = array_merge(...array_values($inputByKey));

        if (!$v->ok()) {
            fail_back('/modules/' . basename($_SERVER['SCRIPT_NAME']), $v->errors(), $oldCombined);
        }

        foreach ($fieldsFlat as $name => $spec) {
            if (($spec['type'] ?? '') === 'number') {
                $gKey = $spec['_key'];
                $val  = gd_dot_get($inputByKey[$gKey], $name);
                gd_dot_set($inputByKey[$gKey], $name, $val === '' ? 0 : (int)$val);
            }
        }

        foreach ($keys as $k) {
            $merged = array_replace_recursive($records[$k], $inputByKey[$k]);
            Store::save($k, $merged);
        }
        gd_log_activity('Updated ' . $config['title']);
        flash('success', $config['title'] . ' saved.');
        header('Location: ' . gd_admin_url('/modules/' . basename($_SERVER['SCRIPT_NAME']))); exit;
    }

    gd_render_singleton_form($config, $records, $defaultKey);
}

function gd_render_singleton_form(array $config, array $records, ?string $defaultKey): void {
    global $DATA;
    $old    = old_input();
    $errors = old_errors();

    $adminTitle  = $config['title'];
    $adminActive = $config['active'];
    require __DIR__ . '/layout-top.php';
    ?>
    <div class="gd-admin-toolbar">
      <div>
        <h1><?= e($config['title']) ?></h1>
        <?php if (!empty($config['hint'])): ?><p><?= e($config['hint']) ?></p><?php endif; ?>
      </div>
    </div>

    <form method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <?php foreach ($config['groups'] as $group):
          $gKey   = $group['key'] ?? $defaultKey;
          $record = $records[$gKey];
      ?>
        <div class="gd-admin-card">
          <h2><?= e($group['label']) ?></h2>
          <?php if (!empty($group['hint'])): ?><p class="gd-admin-card-hint"><?= e($group['hint']) ?></p><?php endif; ?>
          <div class="gd-admin-field-row">
            <?php foreach ($group['fields'] as $name => $spec):
                $type     = $spec['type'] ?? 'text';
                $value    = $old !== null ? gd_dot_get($old, $name, gd_dot_get($record, $name, '')) : gd_dot_get($record, $name, $spec['default'] ?? '');
                $domId    = 'f-' . preg_replace('/[^a-z0-9]+/i', '-', $name);
                $formName = gd_form_name($name);
            ?>
              <div class="mb-2" style="<?= $type === 'textarea' ? 'grid-column: 1 / -1;' : '' ?>">
                <?php if ($type === 'image'): ?>
                  <?php gd_image_field($formName, $spec['bucket'] ?? '', (string)gd_dot_get($record, $name, ''), $spec['label']); ?>
                <?php elseif ($type === 'toggle'): ?>
                  <div class="form-check form-switch mt-4">
                    <input type="checkbox" class="form-check-input" role="switch" id="<?= e($domId) ?>" name="<?= e($formName) ?>" <?= $value ? 'checked' : '' ?>>
                    <label class="form-check-label" for="<?= e($domId) ?>"><?= e($spec['label']) ?></label>
                  </div>
                <?php elseif ($type === 'select'): ?>
                  <label class="form-label" for="<?= e($domId) ?>"><?= e($spec['label']) ?></label>
                  <select class="form-select <?= isset($errors[$name]) ? 'is-invalid' : '' ?>" id="<?= e($domId) ?>" name="<?= e($formName) ?>">
                    <?php foreach ($spec['options'] as $val => $optLabel): ?>
                      <option value="<?= e((string)$val) ?>" <?= (string)$value === (string)$val ? 'selected' : '' ?>><?= e($optLabel) ?></option>
                    <?php endforeach; ?>
                  </select>
                <?php elseif ($type === 'textarea'): ?>
                  <label class="form-label" for="<?= e($domId) ?>"><?= e($spec['label']) ?><?= !empty($spec['required']) ? ' *' : '' ?></label>
                  <textarea class="form-control <?= isset($errors[$name]) ? 'is-invalid' : '' ?>" id="<?= e($domId) ?>" name="<?= e($formName) ?>" rows="3"><?= e((string)$value) ?></textarea>
                <?php else: ?>
                  <label class="form-label" for="<?= e($domId) ?>"><?= e($spec['label']) ?><?= !empty($spec['required']) ? ' *' : '' ?></label>
                  <input type="<?= $type === 'number' ? 'number' : 'text' ?>" class="form-control <?= isset($errors[$name]) ? 'is-invalid' : '' ?>"
                         id="<?= e($domId) ?>" name="<?= e($formName) ?>" value="<?= e((string)$value) ?>">
                <?php endif; ?>
                <?php if (isset($errors[$name])): ?><div class="invalid-feedback"><?= e($errors[$name]) ?></div><?php endif; ?>
                <?php if (!empty($spec['hint'])): ?><div class="form-hint mt-1"><?= e($spec['hint']) ?></div><?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
      <button type="submit" class="btn btn-gd-primary-a"><i class="bi bi-check-lg"></i> Save Changes</button>
    </form>
    <?php
    require __DIR__ . '/layout-bottom.php';
}
