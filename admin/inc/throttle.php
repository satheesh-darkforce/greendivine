<?php
/**
 * File-based login throttling — data/security/login-attempts.php, a plain
 * array keyed by IP, each value a list of failure timestamps. 5 failures
 * from the same IP within 15 minutes locks that IP out until enough of
 * those timestamps age past the 15-minute window (a rolling lockout, not
 * a fixed one — simple, consistent with the rest of this project's
 * file+array approach, and sufficient for a single-admin site).
 *
 * Checked BEFORE password_verify() runs, so a lockout can never be
 * timing-attacked into revealing whether a username exists.
 */
const GD_LOGIN_MAX_ATTEMPTS = 5;
const GD_LOGIN_WINDOW       = 900; // 15 minutes, seconds

function gd_login_attempts_path(): string {
    return __DIR__ . '/../../data/security/login-attempts.php';
}

function gd_client_ip(): string {
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function gd_login_recent(array $attempts, string $ip): array {
    $cutoff = time() - GD_LOGIN_WINDOW;
    return array_values(array_filter($attempts[$ip] ?? [], fn($t) => $t > $cutoff));
}

function gd_login_is_locked(string $ip): bool {
    $attempts = Store::loadPath(gd_login_attempts_path());
    return count(gd_login_recent($attempts, $ip)) >= GD_LOGIN_MAX_ATTEMPTS;
}

function gd_login_record_failure(string $ip): void {
    $attempts = Store::loadPath(gd_login_attempts_path());
    $cutoff = time() - GD_LOGIN_WINDOW;
    // prune stale IPs while we're here so the file never grows unbounded
    foreach ($attempts as $k => $_) {
        $recent = gd_login_recent($attempts, $k);
        if (empty($recent)) unset($attempts[$k]); else $attempts[$k] = $recent;
    }
    $attempts[$ip] = array_merge($attempts[$ip] ?? [], [time()]);
    Store::savePath(gd_login_attempts_path(), $attempts, 'login-attempts');
}

function gd_login_clear(string $ip): void {
    $attempts = Store::loadPath(gd_login_attempts_path());
    if (isset($attempts[$ip])) {
        unset($attempts[$ip]);
        Store::savePath(gd_login_attempts_path(), $attempts, 'login-attempts');
    }
}
