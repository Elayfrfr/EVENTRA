<?php
require __DIR__ . '/includes/bootstrap.php';
$user = require_login();
$cat  = in_array($_GET['cat'] ?? '', CATEGORIES, true) ? $_GET['cat'] : 'All';
$upcoming = "TIMESTAMP(e.event_date, e.event_time) >= NOW()";
$regCount = "(SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id AND r.status = 'registered')";

if (is_faculty($user)) {
    /* ---------------- FACULTY DASHBOARD ---------------- */
    $stat_events = (int)q_val("SELECT COUNT(*) FROM events e WHERE e.organizer_id = ? AND e.status = 'published' AND $upcoming", [$user['id']]);
    $stat_regs   = (int)q_val("SELECT COUNT(*) FROM registrations r JOIN events e ON e.id = r.event_id WHERE e.organizer_id = ? AND r.status = 'registered'", [$user['id']]);
    $featured    = q_one("SELECT e.*, $regCount AS reg_count FROM events e WHERE e.organizer_id = ? AND e.status = 'published' AND $upcoming ORDER BY e.event_date, e.event_time LIMIT 1", [$user['id']]);
    $mine        = q_all("SELECT e.*, $regCount AS reg_count FROM events e WHERE e.organizer_id = ? AND $upcoming ORDER BY e.event_date, e.event_time LIMIT 3", [$user['id']]);
    $page_title = 'Home'; $active = 'home';
    require __DIR__ . '/includes/header.php';
    ?>
    <div class="page-head">
      <div><h1>Hello, Prof. <?= e($user['last_name']) ?></h1><p class="sub">Manage your campus events</p></div>
      <a class="btn btn-primary btn-lg" href="<?= e(url('event-add.php')) ?>">Add Event</a>
    </div>
    <div class="stats">
      <div class="stat"><b><?= $stat_events ?></b><span>Upcoming Events</span></div>
      <div class="stat"><b><?= $stat_regs ?></b><span>Registrations</span></div>
    </div>

    <?php if ($featured): $pct = $featured['capacity'] ? min(100, round($featured['reg_count'] / $featured['capacity'] * 100)) : 0; ?>
    <div class="banner right">
      <span class="tag">Upcoming Event</span>
      <a class="see-all" href="<?= e(url('events.php')) ?>">See all</a>
      <h2><?= e($featured['title']) ?></h2>
      <p><?= e(fmt_when($featured)) ?> · <?= e($featured['venue']) ?></p>
      <div class="progress"><div class="bar"><i style="width:<?= $pct ?>%"></i></div><span><?= (int)$featured['reg_count'] ?> of <?= (int)$featured['capacity'] ?> registered</span></div>
    </div>
    <?php endif; ?>

    <div class="row-between"><h2 class="section-title" style="margin:0 0 12px">My Events</h2><a class="link-btn" href="<?= e(url('events.php')) ?>">View all</a></div>
    <?php foreach ($mine as $ev): ?>
      <div class="list-row">
        <span class="sq" style="background:<?= e($ev['color']) ?>"></span>
        <div class="grow"><b><?= e($ev['title']) ?></b><small><?= e(fmt_when($ev)) ?> · <?= e($ev['venue']) ?></small></div>
        <span class="count"><?= (int)$ev['reg_count'] ?> registered</span>
        <span class="badge"><?= e($ev['category']) ?></span>
        <a class="btn btn-outline btn-sm" style="margin-left:20px" href="<?= e(url('event-edit.php?id=' . $ev['id'])) ?>">Edit</a>
      </div>
    <?php endforeach; ?>
    <?php if (!$mine): ?><p class="no-results">You have no upcoming events yet. Tap “Add Event” to create one.</p><?php endif; ?>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

/* ---------------- STUDENT HOME ---------------- */
$stat_events = (int)q_val("SELECT COUNT(*) FROM events e WHERE e.status = 'published' AND $upcoming");
$stat_tickets = (int)q_val("SELECT COUNT(*) FROM registrations r JOIN events e ON e.id = r.event_id WHERE r.user_id = ? AND r.status = 'registered' AND $upcoming", [$user['id']]);
$regs = [];
foreach (q_all("SELECT id, event_id FROM registrations WHERE user_id = ? AND status = 'registered'", [$user['id']]) as $r) $regs[$r['event_id']] = $r;

$featured = q_one("SELECT e.* FROM events e WHERE e.status = 'published' AND $upcoming ORDER BY e.event_date, e.event_time LIMIT 1");
$params = []; $where = "e.status = 'published' AND $upcoming";
if ($cat !== 'All') { $where .= ' AND e.category = ?'; $params[] = $cat; }
$events = q_all("SELECT e.* FROM events e WHERE $where ORDER BY e.event_date, e.event_time LIMIT 3", $params);

$page_title = 'Home'; $active = 'home';
require __DIR__ . '/includes/header.php';
?>
<h1>Hello, <?= e($user['first_name']) ?></h1>
<p class="sub">Find your next campus event</p>
<div class="stats">
  <div class="stat"><b><?= $stat_events ?></b><span>Upcoming Events</span></div>
  <div class="stat"><b><?= $stat_tickets ?></b><span>My Tickets</span></div>
</div>

<?php if ($featured): ?>
<div class="banner">
  <span class="tag">Upcoming Event</span>
  <h2><?= e($featured['title']) ?></h2>
  <p><?= e(fmt_when($featured)) ?> · <?= e($featured['venue']) ?></p>
  <?php if (isset($regs[$featured['id']])): ?>
    <a class="btn btn-white" href="<?= e(url('tickets.php?t=' . $regs[$featured['id']]['id'])) ?>">View Ticket</a>
  <?php else: ?>
    <a class="btn btn-white" href="<?= e(url('register-event.php?id=' . $featured['id'])) ?>">Register</a>
  <?php endif; ?>
</div>
<?php endif; ?>

<?= category_pills($cat, 'home.php') ?>
<div class="grid3">
  <?php foreach ($events as $ev) echo event_card($ev, $user, $regs[$ev['id']] ?? null); ?>
</div>
<?php if (!$events): ?><p class="no-results">No <?= $cat === 'All' ? '' : e(strtolower($cat)) . ' ' ?>events right now. Check back soon!</p><?php endif; ?>
<p style="margin-top:22px"><a class="link-btn" href="<?= e(url('events.php')) ?>">See all events →</a></p>
<?php require __DIR__ . '/includes/footer.php'; ?>
