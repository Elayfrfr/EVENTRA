<?php
// Needs: $user (logged-in student), $active ('home'|'events'|'tickets'|'profile'), $pageTitle
$searchValue = isset($_GET['q']) ? trim($_GET['q']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> | Eventra</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="topbar">
  <a class="brand" href="dashboard.php">
    <span class="brand-box">E</span><span class="brand-name">Eventra</span>
  </a>

  <nav class="nav">
    <a href="dashboard.php" class="<?= $active === 'home' ? 'active' : '' ?>">Home</a>
    <a href="events.php"    class="<?= $active === 'events' ? 'active' : '' ?>">Events</a>
    <a href="tickets.php"   class="<?= $active === 'tickets' ? 'active' : '' ?>">Tickets</a>
    <a href="profile.php"   class="<?= $active === 'profile' ? 'active' : '' ?>">Profile</a>
  </nav>

  <div class="topbar-right">
    <form action="events.php" method="get" class="search">
      <input type="text" name="q" placeholder="Search events..." value="<?= e($searchValue) ?>">
    </form>
    <a href="#" class="bell" title="Notifications">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="#9B2FB8"><path d="M12 22a2.5 2.5 0 0 0 2.45-2h-4.9A2.5 2.5 0 0 0 12 22zm7-6V11a7 7 0 0 0-5-6.7V3a2 2 0 1 0-4 0v1.3A7 7 0 0 0 5 11v5l-2 2v1h18v-1l-2-2z"/></svg>
      <span class="dot"></span>
    </a>
    <a href="profile.php" class="avatar"><?= e(first_initial($user)) ?></a>
  </div>
</header>
<main class="page">
