<?php
require __DIR__ . '/includes/bootstrap.php';
$user = require_login();
$page_title = 'Help & Support'; $active = 'profile';
$faq = [
    ['How do I register for an event?', 'Open an event from Home or Events and tap Register. Your ticket with a QR code will appear in the Tickets tab.'],
    ['Can I cancel my registration?', 'Tap Tickets and open the events you want to cancel. Tap Cancel Registration. Confirm to void your ticket.'],
    ['How do I reset my password?', 'Go to profile tab. Tap Change Password under Settings. Enter your Current and New Password, then tap Update Password.'],
];
require __DIR__ . '/includes/header.php';
?>
<div class="card narrow" style="padding-bottom:26px">
  <h2 style="font-size:22px">Help &amp; Support</h2>
  <p class="sub" style="margin-bottom:22px">Frequently Asked Questions</p>
  <?php foreach ($faq as [$qq, $aa]): ?>
    <div class="faq"><button type="button"><?= e($qq) ?> <span>+</span></button><div class="ans"><?= e($aa) ?></div></div>
  <?php endforeach; ?>
  <div class="help-card">
    <h3>Still need help?</h3>
    <p>Our team usually replies within 120 hours.</p>
    <a href="mailto:<?= e(SUPPORT_EMAIL) ?>">Email Support: <?= e(SUPPORT_EMAIL) ?></a>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
