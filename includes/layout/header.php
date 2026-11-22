<?php
/** @var array $user */
/**
 * Main app header (navbar). Set these BEFORE including:
 *   $page_title (string)   $active ('home'|'events'|'tickets'|'profile')   $user (array)
 */
$user = $user ?? current_user();   // pages set $user before including this file
generate_reminders($user);
$unread     = unread_count($user['id']);
$dropdown   = q_all('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC, id DESC LIMIT 4', [$user['id']]);
$here       = $_SERVER['REQUEST_URI'] ?? '/dashboard/home.php';
$nav        = ['home' => 'Home', 'events' => 'Events', 'tickets' => 'Tickets', 'profile' => 'Profile'];
$navPages   = ['home' => 'dashboard/home.php', 'events' => 'events/events.php', 'tickets' => 'tickets/tickets.php', 'profile' => 'profile/profile.php'];
?>
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
<header class="nav">
  <a class="logo" href="<?= e(url('dashboard/home.php')) ?>"><span class="logo-mark">E</span><span class="logo-text">Eventra</span></a>

  <nav class="nav-links">
    <?php foreach ($nav as $key => $label): ?>
        <a href="<?= e(url($navPages[$key])) ?>" class="<?= ($active ?? '') === $key ? 'active' : '' ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
  </nav>

  <form class="nav-search" action="<?= e(url('events/events.php')) ?>" method="get">
    <input type="search" name="q" placeholder="Search events..." value="<?= e($_GET['q'] ?? '') ?>" aria-label="Search events">
  </form>

  <div class="nav-right">
    <div class="bell-wrap">
      <button type="button" class="bell" id="bellBtn" aria-label="Notifications"><?= icon('bell') ?><?php if ($unread): ?><i class="bell-dot"></i><?php endif; ?></button>

      <div class="notif-panel" id="notifPanel">
        <div class="np-head"><h3>Notifications</h3><button type="button" class="np-x" id="bellClose" aria-label="Close">x</button></div>
        <div class="np-tools">
          <div class="pills small">
            <button type="button" class="pill active" data-nfilter="all">All</button>
            <button type="button" class="pill" data-nfilter="unread">Unread · <?= $unread ?></button>
          </div>
          <form method="post" action="<?= e(url('dashboard/notifications.php')) ?>">
            <?= csrf_field() ?><input type="hidden" name="action" value="mark_all"><input type="hidden" name="return" value="<?= e($here) ?>">
            <button class="link-btn" type="submit">Mark all read</button>
          </form>
        </div>
        <div class="np-list">
          <?php foreach ($dropdown as $n): ?>
            <a class="np-item" href="<?= e(url('dashboard/notifications.php')) ?>" data-unread="<?= (int)!$n['is_read'] ?>">
              <i class="dot <?= $n['is_read'] ? '' : 'on' ?>"></i>
              <div><b><?= e($n['title']) ?></b><p><?= e($n['body']) ?></p><small><?= e(ago_label($n['created_at'])) ?></small></div>
            </a>
          <?php endforeach; ?>
          <?php if (!$dropdown): ?><p class="empty">You're all caught up.</p><?php endif; ?>
        </div>
        <a class="np-all" href="<?= e(url('dashboard/notifications.php')) ?>">View all notifications</a>
      </div>
    </div>
    <a href="<?= e(url('profile/profile.php')) ?>" aria-label="Profile"><?= avatar($user, 36, true) ?></a>
  </div>
</header>
<main class="page">
