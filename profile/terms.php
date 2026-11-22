<?php
require __DIR__ . '/../includes/bootstrap.php';
$user = require_login();
require __DIR__ . '/../includes/partials/terms_content.php';
$page_title = 'Terms and Conditions'; $active = 'profile';
require __DIR__ . '/../includes/layout/header.php';
?>
<div class="card terms-card">
  <p class="ver"><?= e($TERMS_VERSION) ?></p>
  <h1>Terms and Conditions</h1>
  <?php render_terms_columns($TERMS); ?>
  <a class="btn btn-primary btn-lg btn-block" style="margin-top:40px" href="<?= e(url('profile/profile.php')) ?>">Ok, I Agree</a>
</div>
<?php require __DIR__ . '/../includes/layout/footer.php'; ?>
