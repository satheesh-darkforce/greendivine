<?php
/**
 * Small shared helpers used by every admin page: URL building, flash
 * messages, and the "redisplay the form with what was typed" pattern
 * (post/redirect/get, so a refresh never resubmits and a validation
 * failure never loses what the admin already typed).
 */

function gd_admin_url(string $path = ''): string {
    return GD_BASE . '/admin' . ($path === '' ? '' : '/' . ltrim($path, '/'));
}

function flash(string $type, string $message): void {
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/** Pulls and clears any pending flash messages (list of ['type','message']). */
function flash_pull(): array {
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

/** Save the failed submission + errors, then redirect back to the form. */
function fail_back(string $path, array $errors, array $input): void {
    $_SESSION['old_errors'] = $errors;
    $_SESSION['old_input']  = $input;
    header('Location: ' . gd_admin_url($path));
    exit;
}

/** Pulls and clears the last failed submission (if any). */
function old_input(): ?array {
    $v = $_SESSION['old_input'] ?? null;
    unset($_SESSION['old_input']);
    return $v;
}

function old_errors(): array {
    $v = $_SESSION['old_errors'] ?? [];
    unset($_SESSION['old_errors']);
    return $v;
}

/**
 * Value for a form field: prefers a just-failed resubmission ($old), falls
 * back to the existing record, falls back to $default. Keeps every edit
 * template's inputs one line each instead of repeating this fallback chain.
 */
function gd_field(?array $old, ?array $record, string $key, $default = '') {
    if ($old !== null && array_key_exists($key, $old)) return $old[$key];
    if ($record !== null && array_key_exists($key, $record)) return $record[$key];
    return $default;
}

function current_admin_username(): ?string {
    return $_SESSION['admin_user'] ?? null;
}

/**
 * Append-only activity log — "who changed what, when" (Section P2 of
 * docs/update.md). Plain text, not Store-managed data: there's nothing to
 * validate or restore here, just a running record. Viewed in
 * admin/modules/activity-log.php.
 */
function gd_log_activity(string $action, string $detail = ''): void {
    $line = sprintf(
        "[%s] %-20s %s%s\n",
        date('Y-m-d H:i:s'),
        current_admin_username() ?? 'unknown',
        $action,
        $detail !== '' ? ' — ' . $detail : ''
    );
    $dir = __DIR__ . '/../../data/security';
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    @file_put_contents($dir . '/activity.log', $line, FILE_APPEND | LOCK_EX);
}

/** Best-effort human-readable label for a record, for activity log lines. */
function gd_record_label(array $record): string {
    foreach (['name', 'title', 'label', 'q', 'eyebrow'] as $key) {
        if (!empty($record[$key]) && is_string($record[$key])) return mb_strimwidth($record[$key], 0, 60, '…');
    }
    return '';
}

function current_admin(): ?array {
    $username = current_admin_username();
    if (!$username) return null;
    $users = Store::loadPath(__DIR__ . '/../config/admin-users.php');
    return $users[$username] ?? null;
}

/** Read a "a.b.c" dot-path out of a nested array. */
function gd_dot_get(array $arr, string $path, $default = null) {
    $cur = $arr;
    foreach (explode('.', $path) as $seg) {
        if (!is_array($cur) || !array_key_exists($seg, $cur)) return $default;
        $cur = $cur[$seg];
    }
    return $cur;
}

/** Write a value into a nested array at a "a.b.c" dot-path, creating sub-arrays as needed. */
function gd_dot_set(array &$arr, string $path, $value): void {
    $segs = explode('.', $path);
    $cur  = &$arr;
    foreach ($segs as $i => $seg) {
        if ($i === count($segs) - 1) { $cur[$seg] = $value; break; }
        if (!isset($cur[$seg]) || !is_array($cur[$seg])) $cur[$seg] = [];
        $cur = &$cur[$seg];
    }
}

/**
 * PHP itself converts dots (and spaces) in submitted field names to
 * underscores before they ever reach $_POST/$_FILES — a "a.b" input name
 * arrives as $_POST['a_b'], never $_POST['a.b']. Every dot-path field name
 * must be translated through this at both the <input name="..."> and the
 * $_POST/$_FILES read site, or the two silently stop matching.
 */
function gd_form_name(string $path): string {
    return str_replace('.', '__', $path);
}
