<?php
/** Session guard. Assumes bootstrap.php has already run (session started). */

const GD_ADMIN_IDLE_TIMEOUT = 7200; // 2 hours

function require_admin(): void {
    if (empty($_SESSION['admin_user'])) {
        header('Location: ' . gd_admin_url('/login.php'));
        exit;
    }
    if (time() - ($_SESSION['admin_last_seen'] ?? 0) > GD_ADMIN_IDLE_TIMEOUT) {
        $_SESSION = [];
        session_destroy();
        header('Location: ' . gd_admin_url('/login.php?timeout=1'));
        exit;
    }
    $_SESSION['admin_last_seen'] = time();
}
