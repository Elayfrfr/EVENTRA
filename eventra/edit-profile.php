<?php
require __DIR__ . '/includes/bootstrap.php';
$user = require_login();
$fac  = is_faculty($user);
$error = null;
$f = ['full_name' => display_name($user), 'department' => $user['department'] ?? '', 'contact' => $user['contact'] ?? '', 'email' => $user['email'], 'id_number' => $user['id_number']];

if (is_post()) {
    csrf_check();
    foreach ($f as $k => $_) $f[$k] = trim($_POST[$k] ?? '');
    $name  = trim(preg_replace('/^Prof\.\s*/i', '', $f['full_name']));
    $parts = preg_split('/\s+/', $name);

    if (count($parts) < 2)                                  $error = 'Please enter your first and last name.';
    elseif (!filter_var($f['email'], FILTER_VALIDATE_EMAIL)) $error = 'Please enter a valid email address.';
    elseif ($f['id_number'] === '')                          $error = 'Please enter your ID.';
    elseif (q_val('SELECT COUNT(*) FROM users WHERE email = ? AND id <> ?', [$f['email'], $user['id']]))          $error = 'That email is already used by another account.';
    elseif (q_val('SELECT COUNT(*) FROM users WHERE id_number = ? AND id <> ?', [$f['id_number'], $user['id']])) $error = 'That ID number is already used by another account.';
    else {
        $avatar = $user['avatar'];
        if (!empty($_FILES['photo']['name'])) {
            $file = $_FILES['photo'];
            $info = $file['error'] === UPLOAD_ERR_OK ? @getimagesize($file['tmp_name']) : false;
            $ext  = $info ? [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'][$info[2]] ?? null : null;
            if (!$ext || $file['size'] > 2 * 1024 * 1024) {
                $error = 'Photo must be a JPG, PNG or WEBP image under 2 MB.';
            } else {
                $dir = __DIR__ . '/uploads/avatars';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $name2 = 'u' . $user['id'] . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $dir . '/' . $name2)) {
                    if ($avatar && file_exists(__DIR__ . '/' . $avatar)) @unlink(__DIR__ . '/' . $avatar);
                    $avatar = 'uploads/avatars/' . $name2;
                } else $error = 'Could not save the photo. Try again.';
            }
        }
        if (!$error) {
            $last = array_pop($parts);
            q('UPDATE users SET first_name=?, last_name=?, department=?, contact=?, email=?, id_number=?, avatar=? WHERE id=?',
              [implode(' ', $parts), $last, $f['department'] ?: null, $f['contact'] ?: null, $f['email'], $f['id_number'], $avatar, $user['id']]);
            flash('success', 'Profile updated.');
            redirect('profile.php');
        }
    }
}

$page_title = 'Edit Profile'; $active = 'profile';
require __DIR__ . '/includes/header.php';
?>
<div class="card narrow">
  <h2 style="font-size:22px">Edit Profile</h2>
  <?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>
  <form method="post" enctype="multipart/form-data" novalidate>
    <?= csrf_field() ?>
    <div class="photo-row">
      <?= avatar($user, 56) ?>
      <label class="change-photo">Change Photo <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" style="display:none" onchange="this.parentNode.nextElementSibling.textContent=this.files[0]?this.files[0].name:''"></label>
      <span class="sub" style="font-size:12px"></span>
    </div>
    <label class="lbl" for="full_name">Full Name</label>
    <input class="input" id="full_name" name="full_name" value="<?= e($f['full_name']) ?>" required>
    <label class="lbl" for="department"><?= $fac ? 'Department / Office' : 'Department' ?></label>
    <input class="input" id="department" name="department" value="<?= e($f['department']) ?>">
    <label class="lbl" for="contact">Contact Number</label>
    <input class="input" id="contact" name="contact" value="<?= e($f['contact']) ?>">
    <label class="lbl" for="email">Email</label>
    <input class="input" type="email" id="email" name="email" value="<?= e($f['email']) ?>" required>
    <label class="lbl" for="id_number"><?= $fac ? 'Employee ID' : 'Student ID' ?></label>
    <input class="input" id="id_number" name="id_number" value="<?= e($f['id_number']) ?>" required>
    <div class="actions"><button class="btn btn-primary" type="submit">Save Changes</button></div>
  </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
