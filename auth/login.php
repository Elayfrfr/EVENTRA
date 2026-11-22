<?php
require __DIR__ . '/../includes/bootstrap.php';
if (current_user()) redirect('dashboard/home.php');

$role  = ($_POST['role'] ?? $_GET['role'] ?? 'student') === 'faculty' ? 'faculty' : 'student';
$error = null;
$email = '';

if (is_post()) {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    if ($email === '' || $pass === '')       $error = 'Please enter your email and password.';
    elseif (empty($_POST['terms']))          $error = 'Please accept the Terms and Conditions.';
    else {
        $u = q_one('SELECT * FROM users WHERE email = ?', [$email]);
        if (!$u || !password_verify($pass, $u['password_hash'])) {
            $error = 'Incorrect email or password.';
        } elseif ($u['role'] !== $role) {
            $error = 'This account is a ' . $u['role'] . ' account. Please switch the toggle to ' . ucfirst($u['role']) . '.';
        } else {
            session_regenerate_id(true);
            $_SESSION['uid'] = (int)$u['id'];
            redirect('dashboard/home.php');
        }
    }
}

$page_title = 'Sign In';
require __DIR__ . '/../includes/layout/auth_header.php';
?>
<h2 id="welcome">Welcome <?= $role === 'faculty' ? 'Faculty' : 'Student' ?>!</h2>
<p class="sub">Sign in to continue to Eventra</p>

<?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>

<form method="post" novalidate>
  <?= csrf_field() ?>
  <div class="seg">
    <label><input type="radio" name="role" value="student" data-heading="Welcome Student!" <?= $role === 'student' ? 'checked' : '' ?>><span>Student</span></label>
    <label><input type="radio" name="role" value="faculty" data-heading="Welcome Faculty!" <?= $role === 'faculty' ? 'checked' : '' ?>><span>Faculty</span></label>
  </div>

  <label class="lbl" for="email">Email</label>
  <input class="input" type="email" id="email" name="email" placeholder="Enter your email" value="<?= e($email) ?>" required>
  <label class="lbl" for="password">Password</label>
  <input class="input" type="password" id="password" name="password" placeholder="Enter your password" required>
  <a class="forgot" href="<?= e(url('auth/forgot-password.php')) ?>">Forgot Password?</a>

  <label class="check"><input type="checkbox" name="terms" id="terms" value="1" <?= !empty($_POST['terms']) ? 'checked' : '' ?>>
    <span>I accept the <a data-open="m-terms">Terms and Conditions</a></span></label>

  <button class="btn btn-primary btn-lg" type="submit">Sign In</button>
</form>

<p class="or">or continue with</p>
<div class="social">
  <a class="btn btn-outline" href="<?= e(url('auth/coming-soon.php')) ?>">Google</a>
  <a class="btn btn-outline" href="<?= e(url('auth/coming-soon.php')) ?>">Facebook</a>
</div>
<p class="switch-line">Don't have an account? <a href="<?= e(url('auth/signup.php')) ?>">Sign up</a></p>

<?php require __DIR__ . '/../includes/partials/terms_modal.php'; ?>
<?php require __DIR__ . '/../includes/layout/auth_footer.php'; ?>
