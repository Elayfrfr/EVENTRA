<?php
require __DIR__ . '/includes/bootstrap.php';
$user = require_login();
$fac  = is_faculty($user);
$page_title = 'Profile'; $active = 'profile';

if ($fac) {
    $created  = (int)q_val('SELECT COUNT(*) FROM events WHERE organizer_id = ?', [$user['id']]);
    $totalReg = (int)q_val("SELECT COUNT(*) FROM registrations r JOIN events e ON e.id = r.event_id WHERE e.organizer_id = ? AND r.status = 'registered'", [$user['id']]);
    $week     = (int)q_val("SELECT COUNT(*) FROM registrations r JOIN events e ON e.id = r.event_id WHERE e.organizer_id = ? AND r.status = 'registered' AND r.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)", [$user['id']]);
    $upcoming = (int)q_val("SELECT COUNT(*) FROM events WHERE organizer_id = ? AND status = 'published' AND TIMESTAMP(event_date, event_time) >= NOW()", [$user['id']]);
    $byEvent  = q_all("SELECT e.*, (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id AND r.status = 'registered') AS reg_count
                       FROM events e WHERE e.organizer_id = ? AND e.status = 'published' ORDER BY e.event_date DESC LIMIT 6", [$user['id']]);
}
require __DIR__ . '/includes/header.php';
?>
<div class="card narrow" style="padding-top:22px">
  <div class="profile-top">
    <?= avatar($user, 84) ?>
    <h2><?= e(display_name($user)) ?> <?php if ($fac): ?><a href="<?= e(url('edit-profile.php')) ?>" title="Edit profile" style="vertical-align:middle;margin-left:6px"><?= icon('pencil') ?></a><?php endif; ?></h2>
    <p class="mail"><?= e($user['email']) ?></p>
    <span class="badge"><?= $fac ? 'Faculty' : 'Student' ?></span>
  </div>

  <?php if ($fac): ?>
    <div class="stat-tiles">
      <a class="stat-tile" href="<?= e(url('events-created.php')) ?>"><b><?= $created ?></b><span>Events Created</span></a>
      <button type="button" class="stat-tile" data-open="m-regs"><b><?= $totalReg ?></b><span>Registrations</span></button>
      <a class="stat-tile" href="<?= e(url('events.php')) ?>"><b><?= $upcoming ?></b><span>Upcoming</span></a>
    </div>
  <?php else: ?>
    <div class="info-head"><div><b>Account Information</b><span><?= e(trim(($user['course'] ?? '') . ($user['year_level'] ? ' · ' . $user['year_level'] : ''))) ?></span></div><a class="link-btn" href="<?= e(url('edit-profile.php')) ?>">Edit</a></div>
    <div class="info-row"><span>Student ID</span><b><?= e($user['id_number']) ?></b></div>
    <div class="info-row"><span>Department</span><b><?= e($user['department'] ?: '—') ?></b></div>
    <div class="info-row"><span>Contact Number</span><b><?= e($user['contact'] ?: '—') ?></b></div>
  <?php endif; ?>

  <p class="set-title">Settings</p>
  <a class="set-row" href="<?= e(url('change-password.php')) ?>">Change Password <span class="arrow">&gt;</span></a>
  <a class="set-row" href="<?= e(url('help.php')) ?>">Help &amp; Support <span class="arrow">&gt;</span></a>
  <a class="set-row" href="<?= e(url('terms.php')) ?>">Terms and Conditions <span class="arrow">&gt;</span></a>
  <button type="button" class="set-row danger" data-open="m-logout">Log Out <span class="arrow">&gt;</span></button>
</div>

<div class="modal" id="m-logout">
  <div class="modal-card modal-pop">
    <div class="circle pink"><?= icon('logout') ?></div>
    <h3>Log out?</h3>
    <p class="msg">You'll need to sign in again to view your tickets and register for events.</p>
    <form method="post" action="<?= e(url('logout.php')) ?>" class="row-btns">
      <?= csrf_field() ?>
      <button type="button" class="btn btn-grey" data-close>Cancel</button>
      <button type="submit" class="btn btn-red">Log Out</button>
    </form>
  </div>
</div>

<?php if ($fac): ?>
<div class="modal" id="m-regs">
  <div class="modal-card mid">
    <button type="button" class="modal-x" data-close aria-label="Close">x</button>
    <h3>Registrations</h3>
    <div style="display:flex;align-items:baseline;gap:12px;margin-top:10px"><span style="font-size:36px;font-weight:700;color:var(--purple)"><?= $totalReg ?></span><span class="msg">Total registrations</span></div>
    <span class="badge green" style="margin-top:8px">+<?= $week ?> this week</span>
    <h4 style="margin:22px 0 4px;font-size:15px">By Event</h4>
    <?php foreach ($byEvent as $ev): $pct = $ev['capacity'] ? min(100, round($ev['reg_count'] / $ev['capacity'] * 100)) : 0; ?>
      <div class="reg-row">
        <div class="top"><span><?= e($ev['title']) ?></span><span style="color:var(--muted)"><?= (int)$ev['reg_count'] ?> / <?= (int)$ev['capacity'] ?></span></div>
        <div class="meter" style="margin:6px 0"><i style="width:<?= $pct ?>%"></i></div>
        <div class="bot"><span><?= e(fmt_date($ev)) ?> · <?= is_upcoming($ev) ? 'Upcoming' : 'Ended' ?></span><span><?= $pct ?>% full</span></div>
      </div>
    <?php endforeach; ?>
    <?php if (!$byEvent): ?><p class="empty">No published events yet.</p><?php endif; ?>
  </div>
</div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
