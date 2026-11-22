<?php
require __DIR__ . '/includes/bootstrap.php';
if (current_user()) redirect('dashboard/home.php');
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Welcome · Eventra</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body>
<div class="splash">
  <div class="landing-card">
    <div class="logo"><span class="logo-mark">E</span><span>Eventra</span></div>
    <p class="tagline">EVENT MANAGEMENT &amp; TICKETING</p>
    <a class="btn btn-primary" href="<?= e(url('auth/signup.php')) ?>">Get Started</a>
    <a class="btn btn-outline" href="<?= e(url('auth/login.php')) ?>">Sign In</a>
    <p class="or" style="margin-top:24px">or continue with</p>
    <div class="social">
      <a class="btn btn-outline" style="margin:0;height:44px" href="<?= e(url('auth/coming-soon.php')) ?>">Google</a>
      <a class="btn btn-outline" style="margin:0;height:44px" href="<?= e(url('auth/coming-soon.php')) ?>">Facebook</a>
    </div>
  </div>
</div>
</body></html>
