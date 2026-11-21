<?php
require __DIR__ . '/includes/bootstrap.php';

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$row = $token ? q_one('SELECT * FROM password_resets WHERE token_hash = ? AND expires_at > NOW()', [hash('sha256', $token)]) : null;
$error = null;

if ($row && is_post()) {
    csrf_check();
    $pw = $_POST['password'] ?? ''; $cf = $_POST['confirm'] ?? '';
    if ($e = password_error($pw)) $error = $e;
    elseif ($pw !== $cf)          $error = 'Passwords do not match.';
    else {
        q('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($pw, PASSWORD_DEFAULT), $row['user_id']]);
        q('DELETE FROM password_resets WHERE user_id = ?', [$row['user_id']]);
        flash('success', 'Password updated. You can sign in now.');
        redirect('login.php');
    }
}

$page_title = 'Reset Password';
require __DIR__ . '/includes/auth_header.php';
?>
<?php if (!$row): ?>
  <h2>Link expired</h2>
  <p class="sub">This reset link is invalid or has expired.</p>
  <a class="btn btn-primary btn-lg" href="<?= e(url('forgot-password.php')) ?>">Request a new link</a>
<?php else: ?>
  <h2>Reset Password</h2>
  <p class="sub">Choose a new password for your account.</p>
  <?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>
  <form method="post" data-match novalidate>
    <?= csrf_field() ?><input type="hidden" name="token" value="<?= e($token) ?>">
    <label class="lbl" for="password">New Password</label>
    <input class="input" type="password" id="password" name="password" placeholder="Create a password" required>
    <label class="lbl" for="confirm">Confirm Password</label>
    <input class="input" type="password" id="confirm" name="confirm" placeholder="Confirm your password" required>
    <p class="sub" style="font-size:11.5px;margin-top:8px"><?= e(PASSWORD_HINT) ?>.</p>
    <button class="btn btn-primary btn-lg" type="submit" style="margin-top:20px">Update Password</button>
  </form>
<?php endif; ?>
<?php require __DIR__ . '/includes/auth_footer.php'; ?>
