<?php
require __DIR__ . '/inc/bootstrap.php';

if (!empty($_SESSION['admin_user'])) {
    header('Location: ' . gd_admin_url('/index.php'));
    exit;
}

$error   = '';
$ip      = gd_client_ip();
$timeout = isset($_GET['timeout']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    if (gd_login_is_locked($ip)) {
        $error = 'Too many failed attempts. Please wait 15 minutes and try again.';
    } else {
        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $users    = Store::loadPath(__DIR__ . '/config/admin-users.php');
        $user     = $users[$username] ?? null;

        if ($user && password_verify($password, $user['password_hash'])) {
            gd_login_clear($ip);
            session_regenerate_id(true);
            $_SESSION['admin_user']      = $username;
            $_SESSION['admin_last_seen'] = time();
            header('Location: ' . gd_admin_url('/index.php'));
            exit;
        }

        gd_login_record_failure($ip);
        $error = 'Invalid username or password.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Admin Login · Green Divine</title>
<link rel="stylesheet" href="<?= e(gd_asset('/assets/vendor/bootstrap-5.3.3/bootstrap.min.css')) ?>">
<link rel="stylesheet" href="<?= e(gd_asset('/assets/vendor/bootstrap-icons-1.11.3/bootstrap-icons.css')) ?>">
<link rel="stylesheet" href="<?= e(gd_asset('/admin/assets/css/login.css')) ?>">
</head>
<body class="gd-login-page">
  <div class="gd-login-card">
    <div class="gd-login-brand">
      <img src="<?= e(gd_img($DATA['config']['logo'])) ?>" alt="" width="48" height="38">
      <span><strong><?= e($DATA['site']['name']) ?></strong><span>Admin Console</span></span>
    </div>
    <h1>Sign in</h1>
    <p class="sub">Manage every part of the site from here.</p>

    <?php if ($timeout && !$error): ?>
      <div class="gd-login-notice"><i class="bi bi-clock-history"></i> You were signed out after 2 hours of inactivity.</div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="gd-login-error"><i class="bi bi-exclamation-triangle-fill"></i> <?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" novalidate>
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label" for="username">Username</label>
        <input type="text" class="form-control" id="username" name="username" required autofocus autocomplete="username">
      </div>
      <div class="mb-4">
        <label class="form-label" for="password">Password</label>
        <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn btn-dark w-100">Sign in <i class="bi bi-arrow-right ms-1"></i></button>
    </form>
    <p class="gd-login-foot">Green Divine internal use only.</p>
  </div>
</body>
</html>
