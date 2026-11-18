<?php
require __DIR__ . '/inc/bootstrap.php';

$_SESSION = [];
session_destroy();
header('Location: ' . gd_admin_url('/login.php'));
exit;
