<?php
/**
 * Template for admin/config/admin-users.php (the real file holds password
 * hashes and is kept out of git — the repository is public).
 *
 * Fresh install:
 *   1. Copy this file to admin/config/admin-users.php
 *   2. Run:  php admin/bin/reset-password.php admin
 *      It sets a random password for "admin" and prints it once.
 *   3. Log in at /admin/ and change it under Admin Users.
 *
 * An empty password_hash never matches, so this template can't be used to log in.
 */
return array (
  'admin' =>
  array (
    'password_hash' => '',
    'display_name' => 'Administrator',
    'created_at' => '',
  ),
);
