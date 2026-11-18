<?php
/**
 * Recovery tool for a locked-out admin — CLI-only, not web-exposed.
 * Generates a new random password for a given username, hashes it, and
 * writes it straight into admin/config/admin-users.php via Store, the
 * same atomic write (with a backup) every admin save uses.
 *
 * Usage (run from the project root, or anywhere — the path is absolute):
 *   php admin/bin/reset-password.php <username>
 *   php admin/bin/reset-password.php admin
 *
 * With no arguments, lists every existing admin username.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only.'); }

require __DIR__ . '/../inc/store.php';

$usersFile = __DIR__ . '/../config/admin-users.php';
$users = Store::loadPath($usersFile);

$username = $argv[1] ?? null;

if ($username === null) {
    echo "Usage: php admin/bin/reset-password.php <username>\n\n";
    echo "Existing admin accounts:\n";
    foreach ($users as $u => $info) {
        echo "  - {$u} (" . ($info['display_name'] ?? '') . ")\n";
    }
    exit(0);
}

if (!isset($users[$username])) {
    fwrite(STDERR, "No admin account named \"$username\".\n");
    exit(1);
}

$alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
$password = '';
for ($i = 0; $i < 14; $i++) $password .= $alphabet[random_int(0, strlen($alphabet) - 1)];

$users[$username]['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
$ok = Store::savePath($usersFile, $users, 'admin-users');

if (!$ok) {
    fwrite(STDERR, "Could not write admin-users.php — check file permissions.\n");
    exit(1);
}

echo "Password reset for \"$username\".\n";
echo "New password (shown once — copy it now, then change it after logging in):\n\n";
echo "    $password\n\n";
