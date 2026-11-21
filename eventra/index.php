<?php
require __DIR__ . '/includes/bootstrap.php';
if (current_user()) redirect('home.php');
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Eventra</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body>
<a class="splash" href="<?= e(url('landing.php')) ?>" data-forward="<?= e(url('landing.php')) ?>">
  <div>
    <div class="big"><span class="logo-mark">E</span><span>ventra</span></div>
    <p>EVENT MANAGEMENT &amp; TICKETING</p>
  </div>
</a>
<script src="<?= e(url('assets/js/app.js')) ?>"></script>
</body></html>
