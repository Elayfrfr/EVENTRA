<?php
require __DIR__ . '/../includes/bootstrap.php';
$user = require_login();
$error = null;

if (is_post()) {
    csrf_check();
    $cur = $_POST['current_password'] ?? ''; $new = $_POST['new_password'] ?? ''; $cf = $_POST['confirm_password'] ?? '';
    if (!password_verify($cur, $user['password_hash']))   $error = 'Your current password is incorrect.';
    elseif ($new === $cur)                                $error = 'Your new password must be different from your previous password.';
    elseif ($e = password_error($new))                    $error = $e;
    elseif ($new !== $cf)                                 $error = 'New passwords do not match.';
    else {
        q('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($new, PASSWORD_DEFAULT), $user['id']]);
        flash('success', 'Password updated.');
        redirect('profile/profile.php');
    }
}
$page_title = 'Change Password'; $active = 'profile';
require __DIR__ . '/../includes/layout/header.php';
?>
<div class="card narrow slim" style="max-width:500px;min-height:600px">
  <h2 style="font-size:22px">Change Password</h2>
  <p class="sub" style="font-size:12.5px;max-width:260px">Your new password must be different from your previous password.</p>
  <?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>
  <form method="post" data-match novalidate>
    <?= csrf_field() ?>
    <label class="lbl" for="cp" style="margin-top:22px">Current Password</label>
    <input class="input" type="password" id="cp" name="current_password" placeholder="Enter current password" required>
    <label class="lbl" for="np">New Password</label>
    <input class="input" type="password" id="np" name="new_password" placeholder="Enter new password" required>
    <label class="lbl" for="cf">Confirm New Password</label>
    <input class="input" type="password" id="cf" name="confirm_password" placeholder="Confirm new password" required>
    <p class="hint" style="font-size:11.5px;color:var(--muted);margin:30px 0 22px"><?= e(PASSWORD_HINT) ?></p>
    <button class="btn btn-primary btn-lg btn-block" type="submit">Update Password</button>
  </form>
</div>
<?php require __DIR__ . '/../includes/layout/footer.php'; ?>
