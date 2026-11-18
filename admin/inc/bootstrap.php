<?php
/**
 * Included at the very top of every file under admin/ (including login.php).
 * Starts the session with hardened cookie flags, then loads the public
 * site's $DATA + helpers (e(), gd_url(), gd_img() ...) plus every admin
 * library. Does NOT enforce login — see auth.php / guard.php for that.
 */
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    $gdHttps = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
            || ((int)($_SERVER['SERVER_PORT'] ?? 0) === 443);
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => $gdHttps,
    ]);
    session_start();
}

require_once __DIR__ . '/../../data.php';
require_once __DIR__ . '/store.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/validate.php';
require_once __DIR__ . '/uploads.php';
require_once __DIR__ . '/throttle.php';
