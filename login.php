<?php
require_once __DIR__ . '/includes/functions.php';

if (current_user($pdo)) {
    redirect('index.php');
}

$error = '';
$email = '';
$role  = 'student';
$success = isset($_GET['registered']) ? 'Account created! Please sign in.' : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $role     = (($_POST['role'] ?? '') === 'faculty') ? 'faculty' : 'student';

    if (!csrf_ok()) {
        $error = 'Session expired. Please try again.';
    } elseif ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } elseif (empty($_POST['terms'])) {
        $error = 'Please accept the Terms and Conditions.';
    } else {
        $st = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $st->execute([$email]);
        $found = $st->fetch();

        if (!$found || !password_verify($password, $found['password_hash'])) {
            $error = 'Incorrect email or password.';
        } elseif ($found['role'] !== $role) {
            $error = 'This is not a ' . ucfirst($role) . ' account. Choose the correct tab.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $found['id'];
            redirect($found['role'] === 'student' ? 'dashboard.php' : 'faculty.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In | Eventra</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth">
  <?php include __DIR__ . '/includes/auth_left.php'; ?>

  <section class="auth-right">
    <div class="auth-box">
      <h1 id="welcome">Welcome <?= e(ucfirst($role)) ?>!</h1>
      <p class="sub">Sign in to continue to Eventra</p>

      <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert alert-ok"><?= e($success) ?></div><?php endif; ?>

      <form method="post" action="login.php" novalidate>
        <?= csrf_field() ?>

        <div class="toggle">
          <input type="radio" name="role" id="r-student" value="student" <?= $role === 'student' ? 'checked' : '' ?>>
          <label for="r-student">Student</label>
          <input type="radio" name="role" id="r-faculty" value="faculty" <?= $role === 'faculty' ? 'checked' : '' ?>>
          <label for="r-faculty">Faculty</label>
        </div>

        <div class="field">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="Enter your email" value="<?= e($email) ?>">
        </div>
        <div class="field">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Create a password">
        </div>

        <div class="forgot"><a href="#">Forgot Password?</a></div>

        <label class="check">
          <input type="checkbox" name="terms" value="1">
          <span>I accept the <a href="terms.php" target="_blank">Terms and Conditions</a></span>
        </label>

        <button type="submit" class="btn btn-primary btn-lg btn-block">Sign In</button>
      </form>

      <p class="or">or continue with</p>
      <div class="social">
        <button type="button" class="btn btn-outline" onclick="alert('Google sign-in will be added in a later sprint.')">Google</button>
        <button type="button" class="btn btn-outline" onclick="alert('Facebook sign-in will be added in a later sprint.')">Facebook</button>
      </div>

      <p class="switch">Don't have an account? <a href="register.php">Sign up</a></p>
    </div>
  </section>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
