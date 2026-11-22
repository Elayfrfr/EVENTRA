<?php
require __DIR__ . '/../includes/bootstrap.php';
$user = require_login();

if (is_post()) {
    csrf_check();
    $action = $_POST['action'] ?? '';
    if ($action === 'mark_all') {
        q('UPDATE notifications SET is_read = 1 WHERE user_id = ?', [$user['id']]);
    } elseif ($action === 'mark_one') {
        q('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?', [(int)($_POST['id'] ?? 0), $user['id']]);
    }
    $ret = $_POST['return'] ?? '';
    if (!preg_match('#^/[A-Za-z0-9_\-./?=&%]*$#', $ret) || strpos($ret, '//') === 0) $ret = url('dashboard/notifications.php' . (($_GET['f'] ?? '') === 'unread' ? '?f=unread' : ''));
    header('Location: ' . $ret); exit;
}

$filter = ($_GET['f'] ?? '') === 'unread' ? 'unread' : 'all';
$unread = unread_count($user['id']);
$rows = q_all('SELECT * FROM notifications WHERE user_id = ?' . ($filter === 'unread' ? ' AND is_read = 0' : '') . ' ORDER BY created_at DESC, id DESC', [$user['id']]);
$today = []; $earlier = [];
foreach ($rows as $r) { if (date('Y-m-d', strtotime($r['created_at'])) === date('Y-m-d')) $today[] = $r; else $earlier[] = $r; }

$upc = (int)q_val(is_faculty($user)
    ? "SELECT COUNT(*) FROM events e WHERE e.organizer_id = ? AND e.status='published' AND TIMESTAMP(e.event_date,e.event_time) >= NOW()"
    : "SELECT COUNT(*) FROM events e WHERE e.status='published' AND TIMESTAMP(e.event_date,e.event_time) >= NOW() AND ? > 0", [$user['id']]);
$second = is_faculty($user)
    ? (int)q_val("SELECT COUNT(*) FROM registrations r JOIN events e ON e.id = r.event_id WHERE e.organizer_id = ? AND r.status='registered'", [$user['id']]) . ' Registrations'
    : (int)q_val("SELECT COUNT(*) FROM registrations r JOIN events e ON e.id = r.event_id WHERE r.user_id = ? AND r.status='registered' AND TIMESTAMP(e.event_date,e.event_time) >= NOW()", [$user['id']]) . ' My Tickets';

$page_title = 'Notifications'; $active = 'home';
require __DIR__ . '/../includes/layout/header.php';

function notif_row(array $n): void { ?>
  <?php if (!$n['is_read']): ?>
  <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="mark_one"><input type="hidden" name="id" value="<?= (int)$n['id'] ?>">
    <button type="submit" class="card notif-item" style="width:100%;text-align:left" title="Mark as read">
  <?php else: ?><div class="card notif-item"><?php endif; ?>
      <i class="dot <?= $n['is_read'] ? '' : 'on' ?>"></i>
      <div class="grow"><b><?= e($n['title']) ?></b><p><?= e($n['body']) ?></p></div>
      <span class="when"><?= e(ago_label($n['created_at'])) ?></span>
  <?php if (!$n['is_read']): ?></button></form><?php else: ?></div><?php endif; ?>
<?php } ?>

<div class="page-head">
  <div><h1>Notifications</h1><p class="sub"><?= $upc ?> Upcoming Events · <?= e($second) ?></p></div>
  <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="mark_all"><button class="link-btn" type="submit">Mark all read</button></form>
</div>
<div class="pills">
  <a class="pill <?= $filter === 'all' ? 'active' : '' ?>" href="<?= e(url('dashboard/notifications.php')) ?>">All</a>
  <a class="pill <?= $filter === 'unread' ? 'active' : '' ?>" href="<?= e(url('dashboard/notifications.php?f=unread')) ?>"><?= $filter === 'unread' ? 'Unread' : 'Unread · ' . $unread ?></a>
</div>

<?php if ($today): ?><p class="group-title">Today</p><?php foreach ($today as $n) notif_row($n); endif; ?>
<?php if ($earlier): ?><p class="group-title">Earlier</p><?php foreach ($earlier as $n) notif_row($n); endif; ?>
<?php if (!$rows): ?><p class="no-results"><?= $filter === 'unread' ? "You're all caught up." : 'No notifications yet.' ?></p><?php endif; ?>
<?php require __DIR__ . '/../includes/layout/footer.php'; ?>
