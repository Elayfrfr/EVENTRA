<?php
require __DIR__ . '/../includes/bootstrap.php';
if (current_user()) redirect('dashboard/home.php');

$sent = false; $error = null; $debugLink = null; $email = '';

if (is_post()) {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $u = q_one('SELECT id, first_name FROM users WHERE email = ?', [$email]);
        if ($u) {
            $token = bin2hex(random_bytes(32));
            q('DELETE FROM password_resets WHERE user_id = ?', [$u['id']]);
            q('INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?,?,DATE_ADD(NOW(), INTERVAL 1 HOUR))', [$u['id'], hash('sha256', $token)]);
            $link = full_url('auth/reset-password.php?token=' . $token);
            $body = "Hi {$u['first_name']},\n\nUse this link to reset your Eventra password (valid for 1 hour):\n$link\n\nIf you didn't ask for this, ignore this email.";
            @mail($email, 'Reset your Eventra password', $body, 'From: ' . MAIL_FROM);
            if (APP_DEBUG || SHOW_RESET_LINK) $debugLink = $link;   // mail() is blocked on InfinityFree, so show the link on screen
        }
        $sent = true;   // always say "sent" so nobody can guess which emails exist
    }
}

$page_title = 'Forgot Password';
require __DIR__ . '/../includes/layout/auth_header.php';
?>
<h2>Forgot Password?</h2>
<p class="sub" style="max-width:300px">Enter your email and we'll send you a link to reset your password.</p>
<?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>

<form method="post" id="forgotForm" novalidate>
  <?= csrf_field() ?>
  <label class="lbl" for="email">Email</label>
  <input class="input" type="email" id="email" name="email" placeholder="Enter your email" value="<?= e($email) ?>" required>
  <button class="btn btn-primary btn-lg" type="submit" style="margin-top:22px">Send Reset Link</button>
</form>
<p class="switch-line">Remember your password? <a href="<?= e(url('auth/login.php')) ?>">Sign in</a></p>

<div class="modal <?= $sent ? 'open' : '' ?>" id="m-check" data-sticky>
  <div class="modal-card">
    <div class="circle purple"><?= icon('check') ?></div>
    <h3>Check your email</h3>
    <p class="msg">We've sent a password reset link to your email. It may take a minute to arrive.</p>
    <?php if ($debugLink): ?><div class="alert info" style="text-align:left;font-size:11.5px">Demo mode (email is not available on free hosting): <a href="<?= e($debugLink) ?>"><u><?= e($debugLink) ?></u></a></div><?php endif; ?>
    <a class="btn btn-primary" href="<?= e(url('auth/login.php')) ?>">Back to Sign In</a>
    <?php if ($sent && is_post() && !empty($_POST['resend'])): ?>
      <p class="resend" id="resendBox">Resend in <b id="countdown">59</b>sec</p>
      <form method="post" id="resendReady" style="display:none">
        <?= csrf_field() ?><input type="hidden" name="email" value="<?= e($email) ?>"><input type="hidden" name="resend" value="1">
        <p class="resend">Didn't get it? <button type="submit">Resend</button></p>
      </form>
    <?php else: ?>
      <form method="post"><?= csrf_field() ?><input type="hidden" name="email" value="<?= e($email) ?>"><input type="hidden" name="resend" value="1">
        <p class="resend">Didn't get it? <button type="submit">Resend</button></p></form>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/../includes/layout/auth_footer.php'; ?>
