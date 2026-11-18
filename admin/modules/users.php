<?php
/** Change password / add another admin (Section 8.19). Writes admin-users.php via Store. */
require __DIR__ . '/../inc/guard.php';

const GD_WEAK_PASSWORDS = ['password', 'password1', 'admin123', '12345678', 'qwerty123', 'letmein123', 'changeme123'];
$usersFile = __DIR__ . '/../config/admin-users.php';
$users = Store::loadPath($usersFile);
$me = current_admin_username();

$errors = [];
$flashedThisRequest = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $form = $_POST['gd_form'] ?? '';

    if ($form === 'password') {
        $current = (string)($_POST['current_password'] ?? '');
        $new1 = (string)($_POST['new_password'] ?? '');
        $new2 = (string)($_POST['new_password_confirm'] ?? '');
        $v = new V();

        if (!isset($users[$me]) || !password_verify($current, $users[$me]['password_hash'])) {
            $v->fail('current_password', 'Current password is incorrect.');
        }
        $v->required('new_password', $new1, 'New password');
        if ($new1 !== '' && strlen($new1) < 10) $v->fail('new_password', 'New password must be at least 10 characters.');
        if ($new1 !== '' && in_array(strtolower($new1), GD_WEAK_PASSWORDS, true)) $v->fail('new_password', 'That password is too common — choose something harder to guess.');
        if ($new1 !== '' && $new1 !== $new2) $v->fail('new_password_confirm', 'Passwords do not match.');

        if (!$v->ok()) {
            fail_back('/modules/users.php', $v->errors(), []);
        }

        $users[$me]['password_hash'] = password_hash($new1, PASSWORD_DEFAULT);
        Store::savePath($usersFile, $users, 'admin-users');
        gd_log_activity('Changed own password');
        flash('success', 'Password updated.');
        header('Location: ' . gd_admin_url('/modules/users.php'));
        exit;
    }

    if ($form === 'add_admin') {
        $username = trim((string)($_POST['username'] ?? ''));
        $displayName = trim((string)($_POST['display_name'] ?? ''));
        $pw1 = (string)($_POST['password'] ?? '');
        $pw2 = (string)($_POST['password_confirm'] ?? '');
        $v = new V();

        $v->required('username', $username, 'Username')->pattern('username', $username, '/^[a-z0-9_.-]+$/i', 'Username', 'may only contain letters, numbers, dots, hyphens and underscores');
        if ($username !== '' && isset($users[$username])) $v->fail('username', "\"$username\" is already in use.");
        $v->required('display_name', $displayName, 'Display name');
        $v->required('password', $pw1, 'Password');
        if ($pw1 !== '' && strlen($pw1) < 10) $v->fail('password', 'Password must be at least 10 characters.');
        if ($pw1 !== '' && in_array(strtolower($pw1), GD_WEAK_PASSWORDS, true)) $v->fail('password', 'That password is too common — choose something harder to guess.');
        if ($pw1 !== '' && $pw1 !== $pw2) $v->fail('password_confirm', 'Passwords do not match.');

        if (!$v->ok()) {
            fail_back('/modules/users.php', $v->errors(), ['username' => $username, 'display_name' => $displayName]);
        }

        $users[$username] = [
            'password_hash' => password_hash($pw1, PASSWORD_DEFAULT),
            'display_name'  => $displayName,
            'created_at'    => date('Y-m-d'),
        ];
        Store::savePath($usersFile, $users, 'admin-users');
        gd_log_activity('Added admin account', $username);
        flash('success', "Admin \"$username\" added.");
        header('Location: ' . gd_admin_url('/modules/users.php'));
        exit;
    }
}

$old = old_input();
$errors = old_errors();

$adminTitle  = 'Account';
$adminActive = 'users';
require __DIR__ . '/../inc/layout-top.php';
?>
<div class="gd-admin-toolbar"><div><h1>Account</h1><p>Change your password, or add another admin for your team.</p></div></div>

<div class="gd-admin-card">
  <h2>Change Password</h2>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="gd_form" value="password">
    <div class="mb-2">
      <label class="form-label">Current Password *</label>
      <input type="password" class="form-control <?= isset($errors['current_password']) ? 'is-invalid' : '' ?>" name="current_password" autocomplete="current-password">
      <?php if (isset($errors['current_password'])): ?><div class="invalid-feedback"><?= e($errors['current_password']) ?></div><?php endif; ?>
    </div>
    <div class="gd-admin-field-row">
      <div class="mb-2">
        <label class="form-label">New Password * <span class="text-muted fw-normal">(min 10 characters)</span></label>
        <input type="password" class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>" name="new_password" autocomplete="new-password">
        <?php if (isset($errors['new_password'])): ?><div class="invalid-feedback"><?= e($errors['new_password']) ?></div><?php endif; ?>
      </div>
      <div class="mb-2">
        <label class="form-label">Confirm New Password *</label>
        <input type="password" class="form-control <?= isset($errors['new_password_confirm']) ? 'is-invalid' : '' ?>" name="new_password_confirm" autocomplete="new-password">
        <?php if (isset($errors['new_password_confirm'])): ?><div class="invalid-feedback"><?= e($errors['new_password_confirm']) ?></div><?php endif; ?>
      </div>
    </div>
    <button type="submit" class="btn btn-gd-primary-a"><i class="bi bi-check-lg"></i> Update Password</button>
  </form>
</div>

<div class="gd-admin-card">
  <h2>Current Admins</h2>
  <div class="gd-admin-table-wrap">
    <table class="gd-admin-table">
      <thead><tr><th>Username</th><th>Display Name</th><th>Added</th></tr></thead>
      <tbody>
        <?php foreach ($users as $username => $u): ?>
          <tr>
            <td><?= e($username) ?><?= $username === $me ? ' <span class="badge text-bg-secondary">you</span>' : '' ?></td>
            <td><?= e($u['display_name'] ?? '') ?></td>
            <td><?= e($u['created_at'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="gd-admin-card">
  <h2>Add Another Admin</h2>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="gd_form" value="add_admin">
    <div class="gd-admin-field-row">
      <div class="mb-2">
        <label class="form-label">Username *</label>
        <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" name="username" value="<?= e((string)($old['username'] ?? '')) ?>">
        <?php if (isset($errors['username'])): ?><div class="invalid-feedback"><?= e($errors['username']) ?></div><?php endif; ?>
      </div>
      <div class="mb-2">
        <label class="form-label">Display Name *</label>
        <input type="text" class="form-control <?= isset($errors['display_name']) ? 'is-invalid' : '' ?>" name="display_name" value="<?= e((string)($old['display_name'] ?? '')) ?>">
        <?php if (isset($errors['display_name'])): ?><div class="invalid-feedback"><?= e($errors['display_name']) ?></div><?php endif; ?>
      </div>
      <div class="mb-2">
        <label class="form-label">Password * <span class="text-muted fw-normal">(min 10 characters)</span></label>
        <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" name="password" autocomplete="new-password">
        <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?= e($errors['password']) ?></div><?php endif; ?>
      </div>
      <div class="mb-2">
        <label class="form-label">Confirm Password *</label>
        <input type="password" class="form-control <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>" name="password_confirm" autocomplete="new-password">
        <?php if (isset($errors['password_confirm'])): ?><div class="invalid-feedback"><?= e($errors['password_confirm']) ?></div><?php endif; ?>
      </div>
    </div>
    <button type="submit" class="btn btn-gd-primary-a"><i class="bi bi-person-plus-fill"></i> Add Admin</button>
  </form>
</div>
<?php require __DIR__ . '/../inc/layout-bottom.php'; ?>
