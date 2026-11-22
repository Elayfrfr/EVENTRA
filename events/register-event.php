<?php
require __DIR__ . '/../includes/bootstrap.php';
$user = require_role('student');

$id = (int)($_GET['id'] ?? 0);
$ev = q_one("SELECT e.*, (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id AND r.status = 'registered') AS reg_count
             FROM events e WHERE e.id = ? AND e.status = 'published'", [$id]);
if (!$ev) { flash('error', 'That event is not available.'); redirect('events/events.php'); }

$reg      = q_one('SELECT * FROM registrations WHERE event_id = ? AND user_id = ?', [$id, $user['id']]);
$isActive = $reg && $reg['status'] === 'registered';
$upcoming = is_upcoming($ev);
$full     = $ev['reg_count'] >= $ev['capacity'];
$error    = null;

$f = [
    'full_name'  => user_name($user),
    'student_id' => $user['id_number'],
    'email'      => $user['email'],
    'course'     => $user['course'] ?? '',
    'year_level' => $user['year_level'] ?? '',
];

if (is_post() && !$isActive) {
    csrf_check();
    foreach ($f as $k => $_) $f[$k] = trim($_POST[$k] ?? '');
    if (!$upcoming)                                  $error = 'This event has already ended.';
    elseif ($full)                                   $error = 'Sorry, this event is already full.';
    elseif (in_array('', $f, true))                  $error = 'Please fill in all fields.';
    elseif (!filter_var($f['email'], FILTER_VALIDATE_EMAIL)) $error = 'Please enter a valid email address.';
    elseif (!in_array($f['year_level'], YEAR_LEVELS, true))  $error = 'Please select your year level.';
    elseif (empty($_POST['confirm']))                $error = 'Please confirm your details and accept the Terms and Conditions.';
    else {
        $pay = (float)$ev['price'] > 0 ? 'pending' : 'free';
        if ($reg) {   // previously cancelled: reactivate the same row
            q("UPDATE registrations SET status='registered', full_name=?, student_id=?, email=?, course=?, year_level=?, payment_status=?, generated_at=NULL, checked_in_at=NULL, reminded=0, created_at=NOW() WHERE id=?",
              [$f['full_name'], $f['student_id'], $f['email'], $f['course'], $f['year_level'], $pay, $reg['id']]);
            $rid = (int)$reg['id'];
        } else {
            q('INSERT INTO registrations (event_id, user_id, full_name, student_id, email, course, year_level, payment_status) VALUES (?,?,?,?,?,?,?,?)',
              [$id, $user['id'], $f['full_name'], $f['student_id'], $f['email'], $f['course'], $f['year_level'], $pay]);
            $rid = (int)db()->lastInsertId();
        }
        q('UPDATE registrations SET ticket_code = ? WHERE id = ?', [ticket_code_for($rid), $rid]);
        q('UPDATE users SET course = ?, year_level = ? WHERE id = ?', [$f['course'], $f['year_level'], $user['id']]);

        notify($user['id'], 'Ticket confirmed', "You're registered for {$ev['title']}. Your QR ticket is ready.");
        notify((int)$ev['organizer_id'], 'New registration', "{$f['full_name']} registered for {$ev['title']}.");
        redirect('events/register-event.php?id=' . $id . '&done=1');
    }
}

$page_title = 'Register for Event'; $active = 'events';
require __DIR__ . '/../includes/layout/header.php';
$color = $ev['color'] ?: (CAT_COLORS[$ev['category']] ?? '#B07CE8');
$done  = $isActive && isset($_GET['done']);
?>
<a class="back-link" href="<?= e(url('events/events.php')) ?>">← Back to Events</a>
<h1>Register for Event</h1>
<p class="sub">Fill in your details to get your QR ticket.</p>

<div style="display:grid;grid-template-columns:minmax(280px,380px) 1fr;gap:24px;margin-top:22px;align-items:start" class="reg-grid">
  <div class="card" style="padding:12px 16px 20px">
    <div class="event-img" style="background:<?= e($color) ?>;height:100px"><span class="tag"><?= e($ev['category']) ?></span></div>
    <h3 style="font-size:20px;margin:6px 0 8px"><?= e($ev['title']) ?></h3>
    <p class="sub"><?= e(fmt_when($ev)) ?></p>
    <p class="sub"><?= e($ev['venue']) ?></p>
    <p class="sub"><?= (float)$ev['price'] > 0 ? e(price_label($ev['price'])) . ' admission' : 'Free admission' ?> · Open to all students</p>
    <?php if ($ev['description']): ?><p class="sub" style="margin-top:10px"><?= e($ev['description']) ?></p><?php endif; ?>
    <hr style="border:0;border-top:1px solid var(--border);margin:16px 0 14px">
    <p class="sub" style="font-size:12px">Your QR ticket will be sent to your Tickets tab after you register.</p>
  </div>

  <div class="card" style="padding:22px 26px 26px">
    <h3 style="font-size:16px;margin-bottom:6px">Your details</h3>
    <?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>

    <?php if ($isActive): ?>
      <div class="alert ok">You're already registered for this event.</div>
      <a class="btn btn-primary" href="<?= e(url('tickets/tickets.php?t=' . $reg['id'])) ?>">View my ticket</a>
    <?php elseif (!$upcoming): ?>
      <div class="alert err">This event has already ended.</div>
    <?php elseif ($full): ?>
      <div class="alert err">Sorry, this event is already full.</div>
    <?php else: ?>
    <form method="post" id="regForm" novalidate>
      <?= csrf_field() ?>
      <div class="two">
        <div><label class="lbl" for="full_name">Full Name</label><input class="input" id="full_name" name="full_name" placeholder="Enter your full name" value="<?= e($f['full_name']) ?>" required></div>
        <div><label class="lbl" for="student_id">Student ID</label><input class="input" id="student_id" name="student_id" placeholder="Enter your student ID" value="<?= e($f['student_id']) ?>" required></div>
      </div>
      <label class="lbl" for="email">Email</label>
      <input class="input" type="email" id="email" name="email" placeholder="Enter your email" value="<?= e($f['email']) ?>" required>
      <div class="two">
        <div><label class="lbl" for="course">Course</label><input class="input" id="course" name="course" placeholder="Enter your course" value="<?= e($f['course']) ?>" required></div>
        <div><label class="lbl" for="year_level">Year Level</label>
          <select class="select" id="year_level" name="year_level" required>
            <option value="" disabled <?= $f['year_level'] === '' ? 'selected' : '' ?>>Select year level</option>
            <?php foreach (YEAR_LEVELS as $y): ?><option <?= $f['year_level'] === $y ? 'selected' : '' ?>><?= e($y) ?></option><?php endforeach; ?>
          </select></div>
      </div>
      <label class="check" style="margin-top:16px"><input type="checkbox" name="confirm" id="terms" value="1" required>
        <span>I confirm my details are correct and agree to the <a data-open="m-terms">Terms and Conditions</a></span></label>
      <div class="two" style="margin-top:6px">
        <a class="btn btn-outline btn-lg" href="<?= e(url('events/events.php')) ?>">Cancel</a>
        <button type="button" class="btn btn-primary btn-lg" data-validate-open="m-confirm" data-form="regForm">Register</button>
      </div>
    </form>
    <?php endif; ?>
  </div>
</div>

<!-- Confirm modal -->
<div class="modal" id="m-confirm">
  <div class="modal-card">
    <div class="circle purple"><span class="q-mark">?</span></div>
    <h3>Confirm registration?</h3>
    <p class="msg">You're about to register for <?= e($ev['title']) ?><br>on <?= e(fmt_when($ev)) ?> at <?= e($ev['venue']) ?>.</p>
    <div class="row-btns">
      <button type="button" class="btn btn-outline" data-close>Cancel</button>
      <button type="button" class="btn btn-primary" data-submit-form="regForm">Yes, Register</button>
    </div>
  </div>
</div>

<!-- Success modal -->
<?php if ($done): ?>
<div class="modal open" id="m-done" data-sticky>
  <div class="modal-card">
    <div class="circle purple"><?= icon('check') ?></div>
    <h3>You're registered!</h3>
    <p class="msg"><?= $reg['payment_status'] === 'pending' ? 'Your ticket is reserved. Complete your payment to confirm it.' : 'Your ticket is ready. Show it at the entrance to check in.' ?></p>
    <div class="eticket">
      <div class="top"><span>Eventra</span><span>E-TICKET</span></div>
      <div class="body">
        <h4><?= e($ev['title']) ?></h4>
        <div class="tear" style="margin:0 0 10px"></div>
        <div class="g">
          <div><small>Date &amp; Time</small><b><?= e(fmt_when($ev)) ?></b></div>
          <div><small>Venue</small><b><?= e($ev['venue']) ?></b></div>
          <div><small>Name</small><b><?= e($reg['full_name']) ?></b></div>
          <div><small>Ticket No.</small><b><?= e($reg['ticket_code']) ?></b></div>
        </div>
      </div>
    </div>
    <?php if ($reg['payment_status'] === 'pending'): ?>
      <a class="btn btn-primary" href="<?= e(url('tickets/payment.php?id=' . $reg['id'])) ?>">Pay <?= e(money($ev['price'])) ?></a>
      <a class="btn btn-outline" href="<?= e(url('tickets/tickets.php')) ?>">View My Tickets</a>
    <?php else: ?>
      <a class="btn btn-primary" href="<?= e(url('tickets/tickets.php?t=' . $reg['id'])) ?>">View My Tickets</a>
    <?php endif; ?>
    <p style="margin-top:16px"><a class="link-btn" href="<?= e(url('events/events.php')) ?>">Back to Events</a></p>
  </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/partials/terms_modal.php'; ?>
<style>@media (max-width:860px){.reg-grid{grid-template-columns:1fr!important}}</style>
<?php require __DIR__ . '/../includes/layout/footer.php'; ?>
