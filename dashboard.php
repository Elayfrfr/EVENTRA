<?php
require_once __DIR__ . '/includes/functions.php';
$user = require_student($pdo);

$category = $_GET['category'] ?? '';
if (!in_array($category, CATEGORIES, true)) {
    $category = '';
}

// Numbers shown in the two cards
$upcomingCount = (int)$pdo->query('SELECT COUNT(*) FROM events WHERE event_date >= NOW()')->fetchColumn();
$st = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE user_id = ? AND status = 'confirmed'");
$st->execute([$user['id']]);
$ticketCount = (int)$st->fetchColumn();

// Purple banner = the next upcoming event; cards = up to 6 events (filtered)
$next   = get_events($pdo, $user['id'], '', '', 1);
$banner = $next ? $next[0] : null;
$events = get_events($pdo, $user['id'], $category, '', 6);

$pageTitle = 'Home';
$active    = 'home';
include __DIR__ . '/includes/header.php';
?>
<h1 class="page-title">Hello, <?= e($user['first_name']) ?></h1>
<p class="page-sub">Find your next campus event</p>

<div class="stats">
  <div class="stat"><strong><?= $upcomingCount ?></strong><span>Upcoming Events</span></div>
  <div class="stat"><strong><?= $ticketCount ?></strong><span>My Tickets</span></div>
</div>

<?php if ($banner): ?>
<section class="banner">
  <span class="chip-white">Upcoming Event</span>
  <h2><?= e($banner['title']) ?></h2>
  <p><?= e(fmt_datetime($banner['event_date'])) ?> · <?= e($banner['venue']) ?></p>
  <?php if ($banner['joined']): ?>
    <a class="btn btn-white" href="tickets.php">Registered</a>
  <?php else: ?>
    <a class="btn btn-white" href="register_event.php?id=<?= (int)$banner['id'] ?>">Register</a>
  <?php endif; ?>
</section>
<?php endif; ?>

<div class="chips">
  <?php foreach (array_merge([''], CATEGORIES) as $c): ?>
    <a class="pill <?= $c === $category ? 'active' : '' ?>"
       href="dashboard.php<?= $c === '' ? '' : '?category=' . urlencode($c) ?>"><?= $c === '' ? 'All' : e($c) ?></a>
  <?php endforeach; ?>
</div>

<?php if ($events): ?>
  <div class="grid grid-dash">
    <?php foreach ($events as $ev) { include __DIR__ . '/includes/event_card.php'; } ?>
  </div>
<?php else: ?>
  <p class="empty">No upcoming events found.</p>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
