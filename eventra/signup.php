<?php
require __DIR__ . '/includes/bootstrap.php';
if (current_user()) redirect('home.php');

$f = ['first_name' => '', 'last_name' => '', 'email' => '', 'id_number' => '', 'role' => 'student'];
$error = null;

if (is_post()) {
    csrf_check();
    foreach ($f as $k => $_) $f[$k] = trim($_POST[$k] ?? '');
    $f['role'] = $f['role'] === 'faculty' ? 'faculty' : 'student';
    $pw = $_POST['password'] ?? '';
    $cf = $_POST['confirm'] ?? '';

    if (in_array('', [$f['first_name'], $f['last_name'], $f['email'], $f['id_number'], $pw], true)) $error = 'Please fill in all fields.';
    elseif (!filter_var($f['email'], FILTER_VALIDATE_EMAIL))     $error = 'Please enter a valid email address.';
    elseif ($e = password_error($pw))                            $error = $e;
    elseif ($pw !== $cf)                                         $error = 'Passwords do not match.';
    elseif (empty($_POST['terms']))                              $error = 'Please accept the Terms and Conditions.';
    elseif (q_val('SELECT COUNT(*) FROM users WHERE email = ?', [$f['email']]))         $error = 'That email is already registered.';
    elseif (q_val('SELECT COUNT(*) FROM users WHERE id_number = ?', [$f['id_number']])) $error = 'That ID number is already registered.';
    else {
        q('INSERT INTO users (role, first_name, last_name, email, id_number, password_hash) VALUES (?,?,?,?,?,?)',
          [$f['role'], $f['first_name'], $f['last_name'], $f['email'], $f['id_number'], password_hash($pw, PASSWORD_DEFAULT)]);
        flash('success', 'Account created! Please sign in.');
        redirect('login.php?role=' . $f['role']);
    }
}

$page_title = 'Create Account';
require __DIR__ . '/includes/auth_header.php';
?>
<h2>Create Account</h2>
<p class="sub" style="margin-bottom:10px">Join Eventra with your school ID</p>
<?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>

<form method="post" data-match novalidate>
  <?= csrf_field() ?>
  <div class="two">
    <div><label class="lbl" for="first_name">First Name</label><input class="input" id="first_name" name="first_name" placeholder="Enter first name" value="<?= e($f['first_name']) ?>" required></div>
    <div><label class="lbl" for="last_name">Last Name</label><input class="input" id="last_name" name="last_name" placeholder="Enter last name" value="<?= e($f['last_name']) ?>" required></div>
  </div>
  <label class="lbl" for="email">Email</label>
  <input class="input" type="email" id="email" name="email" placeholder="Enter your email" value="<?= e($f['email']) ?>" required>
  <label class="lbl" for="id_number">ID Number</label>
  <input class="input" id="id_number" name="id_number" placeholder="ID Number" value="<?= e($f['id_number']) ?>" required>

  <label class="lbl" style="margin-top:18px">I am a</label>
  <div class="seg">
    <label><input type="radio" name="role" value="student" <?= $f['role'] === 'student' ? 'checked' : '' ?>><span>Student</span></label>
    <label><input type="radio" name="role" value="faculty" <?= $f['role'] === 'faculty' ? 'checked' : '' ?>><span>Faculty</span></label>
  </div>

  <div class="two" style="margin-top:6px">
    <div><label class="lbl" for="password">Password</label><input class="input" type="password" id="password" name="password" placeholder="Create a password" required></div>
    <div><label class="lbl" for="confirm">Confirm Password</label><input class="input" type="password" id="confirm" name="confirm" placeholder="Confirm your password" required></div>
  </div>
  <p class="sub" style="font-size:11.5px;margin-top:6px"><?= e(PASSWORD_HINT) ?>.</p>

  <label class="check" style="margin-top:16px"><input type="checkbox" name="terms" id="terms" value="1" <?= !empty($_POST['terms']) ? 'checked' : '' ?>>
    <span>I accept the <a data-open="m-terms">Terms and Conditions</a></span></label>

  <button class="btn btn-primary btn-lg" type="submit">Create Account</button>
</form>
<p class="switch-line" style="margin-top:20px">Already have an account? <a href="<?= e(url('login.php')) ?>">Sign In</a></p>

<?php require __DIR__ . '/includes/terms_modal.php'; ?>
<?php require __DIR__ . '/includes/auth_footer.php'; ?>
