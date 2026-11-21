<?php
require __DIR__ . '/includes/bootstrap.php';
$user = require_login();
$cat  = in_array($_GET['cat'] ?? '', CATEGORIES, true) ? $_GET['cat'] : 'All';
$term = trim($_GET['q'] ?? '');
$regCount = "(SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id AND r.status = 'registered')";

$where  = "TIMESTAMP(e.event_date, e.event_time) >= NOW()";
$params = [];
if (is_faculty($user)) { $where .= ' AND e.organizer_id = ?'; $params[] = $user['id']; }
else                   { $where .= " AND e.status = 'published'"; }
if ($cat !== 'All') { $where .= ' AND e.category = ?'; $params[] = $cat; }
if ($term !== '')   { $where .= ' AND (e.title LIKE ? OR e.venue LIKE ? OR e.category LIKE ?)'; array_push($params, "%$term%", "%$term%", "%$term%"); }

$events = q_all("SELECT e.*, $regCount AS reg_count FROM events e WHERE $where ORDER BY e.event_date, e.event_time", $params);

$regs = [];
if (!is_faculty($user)) {
    foreach (q_all("SELECT id, event_id FROM registrations WHERE user_id = ? AND status = 'registered'", [$user['id']]) as $r) $regs[$r['event_id']] = $r;
}

$page_title = 'Events'; $active = 'events';
require __DIR__ . '/includes/header.php';
$extra = $term !== '' ? '&q=' . urlencode($term) : '';
?>
<div class="page-head">
  <div>
    <h1>Hello, <?= is_faculty($user) ? 'Prof. ' . e($user['last_name']) : e($user['first_name']) ?></h1>
    <p class="sub"><?= is_faculty($user) ? 'Manage your campus events' : 'Find your next campus event' ?></p>
  </div>
  <?php if (is_faculty($user)): ?><a class="btn btn-primary btn-lg" href="<?= e(url('event-add.php')) ?>">Add Event</a><?php endif; ?>
</div>

<?= category_pills($cat, 'events.php', $extra) ?>
<?php if ($term !== ''): ?><p class="sub" style="margin:-6px 0 14px">Results for “<?= e($term) ?>” · <a class="link-btn" href="<?= e(url('events.php')) ?>">Clear</a></p><?php endif; ?>

<div class="grid3">
  <?php foreach ($events as $ev) echo event_card($ev, $user, $regs[$ev['id']] ?? null); ?>
</div>
<?php if (!$events): ?><p class="no-results">No events found.</p><?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
