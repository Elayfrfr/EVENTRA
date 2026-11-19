<?php
require_once __DIR__ . '/includes/functions.php';

if (current_user($pdo)) {
    redirect('index.php');
}

$error = '';
$old = ['first_name' => '', 'last_name' => '', 'email' => '', 'id_number' => '', 'role' => 'student'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['first_name'] = trim($_POST['first_name'] ?? '');
    $old['last_name']  = trim($_POST['last_name'] ?? '');
    $old['email']      = strtolower(trim($_POST['email'] ?? ''));
    $old['id_number']  = trim($_POST['id_number'] ?? '');
    $old['role']       = (($_POST['role'] ?? '') === 'faculty') ? 'faculty' : 'student';
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (!csrf_ok()) {
        $error = 'Session expired. Please try again.';
    } elseif ($old['first_name'] === '' || $old['last_name'] === '' || $old['email'] === '' || $old['id_number'] === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!preg_match('/^[A-Za-z0-9-]{3,30}$/', $old['id_number'])) {
        $error = 'ID Number may only have letters, numbers and dashes (3-30 characters).';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (empty($_POST['terms'])) {
        $error = 'Please accept the Terms and Conditions.';
    } else {
        $st = $pdo->prepare('SELECT id FROM users WHERE email = ? OR id_number = ?');
        $st->execute([$old['email'], $old['id_number']]);
        if ($st->fetch()) {
            $error = 'That email or ID Number is already registered.';
        } else {
            $st = $pdo->prepare(
                'INSERT INTO users (first_name, last_name, email, id_number, role, password_hash)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $st->execute([
                $old['first_name'], $old['last_name'], $old['email'], $old['id_number'],
                $old['role'], password_hash($password, PASSWORD_DEFAULT),
            ]);
            redirect('login.php?registered=1');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Account | Eventra</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth">
  <?php include __DIR__ . '/includes/auth_left.php'; ?>

  <section class="auth-right auth-right-top">
    <div class="auth-box">
      <h1>Create Account</h1>
      <p class="sub">Join Eventra with your school ID</p>

      <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

      <form method="post" action="register.php" novalidate>
        <?= csrf_field() ?>

        <div class="row2">
          <div class="field">
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" placeholder="Enter first name" value="<?= e($old['first_name']) ?>" maxlength="50">
          </div>
          <div class="field">
            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" placeholder="Enter last name" value="<?= e($old['last_name']) ?>" maxlength="50">
          </div>
        </div>

        <div class="field">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="Enter your email" value="<?= e($old['email']) ?>" maxlength="100">
        </div>
        <div class="field field-gap">
          <label for="id_number">ID Number</label>
          <input type="text" id="id_number" name="id_number" placeholder="ID Number" value="<?= e($old['id_number']) ?>" maxlength="30">
        </div>

        <div class="field field-gap">
          <label>I am a</label>
          <div class="toggle">
            <input type="radio" name="role" id="r-student" value="student" <?= $old['role'] === 'student' ? 'checked' : '' ?>>
            <label for="r-student">Student</label>
            <input type="radio" name="role" id="r-faculty" value="faculty" <?= $old['role'] === 'faculty' ? 'checked' : '' ?>>
            <label for="r-faculty">Faculty</label>
          </div>
        </div>

        <div class="row2">
          <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Create a password">
          </div>
          <div class="field">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password">
          </div>
        </div>

        <label class="check check-gap">
          <input type="checkbox" name="terms" value="1">
          <span>I accept the <a href="terms.php" target="_blank">Terms and Conditions</a></span>
        </label>

        <button type="submit" class="btn btn-primary btn-lg btn-block">Create Account</button>
      </form>

      <p class="switch">Already have an account? <a href="login.php">Sign In</a></p>
    </div>
  </section>
</div>
</body>
</html>
