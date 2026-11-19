<?php
require_once __DIR__ . '/includes/functions.php';
$user = require_student($pdo);

$courseYear = trim($user['course'] . ($user['course'] && $user['year_level'] ? ' - ' : '') . $user['year_level']);

$pageTitle = 'Profile';
$active    = 'profile';
include __DIR__ . '/includes/header.php';
?>
<section class="card profile">
  <div class="profile-top">
    <div class="avatar avatar-lg"><?= e(initials($user)) ?></div>
    <h2><?= e($user['first_name'] . ' ' . $user['last_name']) ?></h2>
    <p class="meta"><?= e($user['email']) ?></p>
    <span class="badge badge-purple"><?= e(ucfirst($user['role'])) ?></span>
  </div>

  <div class="info-head">
    <strong>Account Information</strong>
    <span class="meta"><?= e($courseYear) ?></span>
    <a href="#">Edit</a>
  </div>
  <div class="info-row"><span>Student ID</span><strong><?= e($user['id_number']) ?></strong></div>
  <div class="info-row"><span>Department</span><strong><?= e($user['department'] ?: '—') ?></strong></div>
  <div class="info-row"><span>Contact Number</span><strong><?= e($user['contact_no'] ?: '—') ?></strong></div>

  <p class="settings-title">Settings</p>
  <a class="setting" href="#"><span>Change Password</span><span>&gt;</span></a>
  <a class="setting" href="#"><span>Help &amp; Support</span><span>&gt;</span></a>
  <a class="setting" href="terms.php" target="_blank"><span>Terms and Conditions</span><span>&gt;</span></a>
  <a class="setting setting-red" href="logout.php"><span>Log Out</span><span>&gt;</span></a>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
