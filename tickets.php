<?php
require_once __DIR__ . '/includes/functions.php';
$user = require_student($pdo);

function days_until($date) {
    $days = (int)ceil((strtotime($date) - time()) / 86400);
    if ($days <= 0) { return 'Today'; }
    if ($days === 1) { return 'Tomorrow'; }
    return 'In ' . $days . ' days';
}

$tab   = (($_GET['tab'] ?? '') === 'past') ? 'past' : 'upcoming';
$where = ($tab === 'past') ? 'e.event_date < NOW()' : 'e.event_date >= NOW()';
$order = ($tab === 'past') ? 'DESC' : 'ASC';

$st = $pdo->prepare(
    "SELECT r.ticket_code, e.title, e.event_date, e.venue, e.price, e.color
     FROM registrations r
     JOIN events e ON e.id = r.event_id
     WHERE r.user_id = ? AND r.status = 'confirmed' AND $where
     ORDER BY e.event_date $order"
);
$st->execute([$user['id']]);
$tickets = $st->fetchAll();

$main   = ($tab === 'upcoming' && $tickets) ? $tickets[0] : null;
$others = $main ? array_slice($tickets, 1) : $tickets;
$count  = count($tickets);

$pageTitle = 'My Tickets';
$active    = 'tickets';
include __DIR__ . '/includes/header.php';
?>
<div class="tickets-head">
  <div>
    <h1 class="page-title">My Tickets</h1>
    <p class="page-sub"><?= $count ?> <?= $tab ?> ticket<?= $count === 1 ? '' : 's' ?></p>
  </div>
  <div class="chips chips-inline">
    <a class="pill <?= $tab === 'upcoming' ? 'active' : '' ?>" href="tickets.php">Upcoming</a>
    <a class="pill <?= $tab === 'past' ? 'active' : '' ?>" href="tickets.php?tab=past">Past</a>
  </div>
</div>

<?php if (isset($_GET['new'])): ?>
  <div class="alert alert-ok">You're registered! Your ticket is below.</div>
<?php endif; ?>

<?php if ($main): ?>
<section class="card ticket-main">
  <div class="ticket-info">
    <div class="badges">
      <span class="badge badge-green">Confirmed</span>
      <span class="badge badge-purple"><?= e(days_until($main['event_date'])) ?></span>
    </div>
    <h2><?= e($main['title']) ?></h2>
    <div class="ticket-grid">
      <div><small>Date</small><strong><?= e(fmt_date($main['event_date'])) ?></strong></div>
      <div><small>Time</small><strong><?= e(fmt_time($main['event_date'])) ?></strong></div>
      <div><small>Venue</small><strong><?= e($main['venue']) ?></strong></div>
      <div><small>Price</small><strong><?= e(price_text($main['price'])) ?></strong></div>
      <div><small>Ticket ID</small><strong><?= e($main['ticket_code']) ?></strong></div>
    </div>
  </div>
  <div class="ticket-code">
    <div class="code-box"><?= e($main['ticket_code']) ?></div>
    <small>Show this at the entrance</small>
  </div>
</section>
<?php endif; ?>

<?php if ($others): ?>
  <h3 class="section-title"><?= $tab === 'past' ? 'Past Tickets' : 'Other Tickets' ?></h3>
  <?php foreach ($others as $t): ?>
    <div class="ticket-row">
      <span class="ticket-color" style="background: <?= e($t['color']) ?>"></span>
      <div class="ticket-row-info">
        <strong><?= e($t['title']) ?></strong>
        <small><?= e(fmt_datetime($t['event_date'])) ?> · <?= e($t['venue']) ?> · <?= e(price_text($t['price'])) ?></small>
      </div>
      <span class="badge badge-green"><?= $tab === 'past' ? 'Attended' : 'Valid' ?></span>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<?php if (!$tickets): ?>
  <p class="empty">No <?= $tab ?> tickets yet. <a href="events.php">Browse events</a></p>
<?php endif; ?>

<p class="foot-note">Missing a ticket? Check your email or contact the event organizer.</p>
<?php include __DIR__ . '/includes/footer.php'; ?>
