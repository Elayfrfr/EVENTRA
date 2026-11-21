<?php
require __DIR__ . '/includes/bootstrap.php';
$user = require_role('faculty');
$rows = q_all('SELECT * FROM events WHERE organizer_id = ? ORDER BY event_date, event_time', [$user['id']]);
$up = array_values(array_filter($rows, 'is_upcoming'));
$past = array_reverse(array_values(array_filter($rows, fn($r) => !is_upcoming($r))));
$page_title = 'Events Created'; $active = 'profile';
require __DIR__ . '/includes/header.php';
?>
<h1>Events Created</h1>
<p class="sub">Everything you have posted</p>
<div class="pills"><span class="pill active">Upcoming •<?= count($up) ?></span></div>
<?php foreach (array_merge($up, $past) as $ev):
  $ended = !is_upcoming($ev);
  [$cls, $lbl] = $ended ? ['gray', 'Ended'] : ($ev['status'] === 'draft' ? ['yellow', 'Draft'] : ['green', 'Published']); ?>
  <a class="list-row" href="<?= e(url('event-edit.php?id=' . $ev['id'])) ?>" style="padding:10px 16px">
    <span class="date-box"><small><?= e(strtoupper(date('M', strtotime($ev['event_date'])))) ?></small><b><?= e(date('j', strtotime($ev['event_date']))) ?></b></span>
    <div class="grow"><b><?= e($ev['title']) ?></b><small><?= e(fmt_time($ev)) ?> · <?= e($ev['venue']) ?></small></div>
    <span class="badge <?= $cls ?>" style="margin-right:20px"><?= $lbl ?></span>
  </a>
<?php endforeach; ?>
<?php if (!$rows): ?><p class="no-results">You haven't created any events yet.</p><?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
