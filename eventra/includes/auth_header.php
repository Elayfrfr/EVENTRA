<?php /** Split-screen layout used by login / signup / forgot / reset. Set $page_title before including. */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page_title ?? 'Eventra') ?> · Eventra</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body>
<div class="auth">
  <aside class="auth-left">
    <a class="auth-logo" href="<?= e(url('landing.php')) ?>"><span class="logo-mark light">E</span><span>Eventra</span></a>
    <div class="auth-hero">
      <h1>Book your Tickets,<br>Events without Hassle</h1>
      <p>Your event is always one tap away.</p>
    </div>
    <div class="auth-foot">EVENT MANAGEMENT &amp; TICKETING</div>
  </aside>
  <section class="auth-right"><div class="auth-box">
