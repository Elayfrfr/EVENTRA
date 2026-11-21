<?php
require __DIR__ . '/includes/bootstrap.php';
$user = require_login();
$tab  = ($_GET['tab'] ?? 'upcoming') === 'past' ? 'past' : 'upcoming';
$isFac = is_faculty($user);
$future = "TIMESTAMP(e.event_date, e.event_time) >= NOW()";

/* ======================= POST ACTIONS ======================= */
if (is_post()) {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $rid    = (int)($_POST['reg_id'] ?? 0);

    if (!$isFac) {
        $reg = q_one("SELECT r.*, e.title, e.organizer_id, e.event_date, e.event_time FROM registrations r JOIN events e ON e.id = r.event_id
                      WHERE r.id = ? AND r.user_id = ? AND r.status = 'registered'", [$rid, $user['id']]);
        if (!$reg) { flash('error', 'Ticket not found.'); redirect('tickets.php'); }

        if ($action === 'generate' && $reg['payment_status'] !== 'pending') {
            q('UPDATE registrations SET generated_at = COALESCE(generated_at, NOW()) WHERE id = ?', [$rid]);
            redirect('tickets.php?gen=' . $rid);
        }
        if ($action === 'cancel' && event_ts($reg) >= time()) {
            q("UPDATE registrations SET status = 'cancelled' WHERE id = ?", [$rid]);
            notify((int)$reg['organizer_id'], 'Registration cancelled', user_name($user) . ' cancelled their registration for ' . $reg['title'] . '.');
            flash('success', 'Your registration for ' . $reg['title'] . ' was cancelled.');
            redirect('tickets.php');
        }
    } else {
        /* faculty: check-in toggle / add attendee manually */
        $evId = (int)($_POST['event_id'] ?? 0);
        $ev = q_one('SELECT * FROM events WHERE id = ? AND organizer_id = ?', [$evId, $user['id']]);
        if (!$ev) { flash('error', 'Event not found.'); redirect('tickets.php'); }

        if ($action === 'checkin') {
            q('UPDATE registrations SET checked_in_at = IF(checked_in_at IS NULL, NOW(), NULL) WHERE id = ? AND event_id = ? AND status = "registered"', [$rid, $evId]);
        } elseif ($action === 'add_attendee') {
            $sid = trim($_POST['student_id'] ?? '');
            $stu = q_one("SELECT * FROM users WHERE id_number = ? AND role = 'student'", [$sid]);
            $cnt = (int)q_val("SELECT COUNT(*) FROM registrations WHERE event_id = ? AND status = 'registered'", [$evId]);
            $old = $stu ? q_one('SELECT * FROM registrations WHERE event_id = ? AND user_id = ?', [$evId, $stu['id']]) : null;
            if (!$stu)                                           flash('error', 'No student found with that ID number.');
            elseif ($old && $old['status'] === 'registered')     flash('error', user_name($stu) . ' is already registered.');
            elseif ($cnt >= $ev['capacity'])                     flash('error', 'This event is already full.');
            else {
                $pay = (float)$ev['price'] > 0 ? 'paid' : 'free';   // added by the organizer = already settled
                if ($old) {
                    q("UPDATE registrations SET status='registered', payment_status=?, generated_at=NULL, checked_in_at=NULL, reminded=0 WHERE id=?", [$pay, $old['id']]);
                    $newId = (int)$old['id'];
                } else {
                    q('INSERT INTO registrations (event_id, user_id, full_name, student_id, email, course, year_level, payment_status) VALUES (?,?,?,?,?,?,?,?)',
                      [$evId, $stu['id'], user_name($stu), $stu['id_number'], $stu['email'], $stu['course'], $stu['year_level'], $pay]);
                    $newId = (int)db()->lastInsertId();
                }
                q('UPDATE registrations SET ticket_code = ? WHERE id = ?', [ticket_code_for($newId), $newId]);
                notify((int)$stu['id'], 'Ticket confirmed', 'You were added to ' . $ev['title'] . ' by the organizer. Your QR ticket is ready.');
                flash('success', user_name($stu) . ' was added to the attendee list.');
            }
        }
        redirect('tickets.php?t=' . $evId . '&att=1');
    }
    redirect('tickets.php');
}

$page_title = 'Tickets'; $active = 'tickets';

/* ======================= FACULTY VIEW ======================= */
if ($isFac) {
    $sel = "e.*, (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id AND r.status='registered') AS reg_count,
                 (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id AND r.status='registered' AND r.checked_in_at IS NOT NULL) AS in_count";
    require __DIR__ . '/includes/header.php';

    if ($tab === 'upcoming') {
        $events = q_all("SELECT $sel FROM events e WHERE e.organizer_id = ? AND e.status='published' AND $future ORDER BY e.event_date, e.event_time", [$user['id']]);
        $feat = $events[0] ?? null; $t = (int)($_GET['t'] ?? 0);
        foreach ($events as $x) if ((int)$x['id'] === $t) $feat = $x;
    } else {
        $events = q_all("SELECT $sel FROM events e WHERE e.organizer_id = ? AND e.status='published' AND NOT ($future) ORDER BY e.event_date DESC, e.event_time DESC", [$user['id']]);
        $held = count($events); $checked = array_sum(array_column($events, 'in_count'));
    }
    ?>
    <div class="page-head tabbed">
      <div><h1>Tickets</h1><p class="sub"><?= $tab === 'upcoming' ? count($events) . ' events with active tickets' : 'Your past events' ?></p></div>
      <div class="tabs">
        <a class="pill <?= $tab === 'upcoming' ? 'active' : '' ?>" href="<?= e(url('tickets.php')) ?>">Upcoming</a>
        <a class="pill <?= $tab === 'past' ? 'active' : '' ?>" href="<?= e(url('tickets.php?tab=past')) ?>">Past</a>
      </div>
    </div>

    <?php if ($tab === 'upcoming'): ?>
      <?php if ($feat):
        $attendees = q_all("SELECT * FROM registrations WHERE event_id = ? AND status='registered' ORDER BY full_name", [$feat['id']]);
        $isOpen = $feat['reg_count'] < $feat['capacity']; ?>
        <div class="card ticket-card" style="margin-top:18px;display:block">
          <div class="badges" style="display:flex;margin-bottom:10px">
            <span class="badge"><?= e($feat['category']) ?></span>
            <span class="badge <?= $isOpen ? 'green' : 'red' ?>" style="margin-left:6px"><?= $isOpen ? 'Open' : 'Full' ?> · <?= e(days_label($feat)) ?></span>
          </div>
          <h2 style="font-size:24px;margin-bottom:16px"><?= e($feat['title']) ?></h2>
          <div class="kv-grid">
            <div class="kv" style="width:200px"><small>Date</small><b><?= e(fmt_date($feat)) ?></b></div>
            <div class="kv" style="width:200px"><small>Time</small><b><?= e(fmt_time($feat)) ?></b></div>
            <div class="kv"><small>Venue</small><b><?= e($feat['venue']) ?></b></div>
          </div>
          <div class="kv-grid">
            <div class="kv" style="width:150px"><small>Registered</small><b><?= (int)$feat['reg_count'] ?> / <?= (int)$feat['capacity'] ?></b></div>
            <div class="kv" style="width:150px"><small>Checked in</small><b><?= (int)$feat['in_count'] ?></b></div>
            <div class="kv" style="width:150px"><small>Price</small><b><?= e(price_label($feat['price'])) ?></b></div>
            <div class="kv"><small>Event ID</small><b><?= e(event_code((int)$feat['id'])) ?></b></div>
          </div>
          <div class="ticket-actions">
            <button class="btn btn-primary" data-open="m-att">Attendees</button>
            <button class="btn btn-outline" data-open="m-share">Share Link</button>
          </div>
        </div>

        <?php $others = array_filter($events, fn($x) => (int)$x['id'] !== (int)$feat['id']); ?>
        <?php if ($others): ?><h2 class="section-title">Other Events</h2><?php endif; ?>
        <?php foreach ($others as $ev): $open = $ev['reg_count'] < $ev['capacity']; ?>
          <div class="list-row">
            <span class="sq" style="background:<?= e($ev['color']) ?>"></span>
            <div class="grow"><a href="<?= e(url('tickets.php?t=' . $ev['id'])) ?>"><b><?= e($ev['title']) ?></b></a>
              <small><?= e(fmt_when($ev)) ?> · <?= e($ev['venue']) ?> · <?= (int)$ev['reg_count'] ?> registered</small></div>
            <span class="badge <?= $open ? 'green' : 'red' ?>" style="margin-right:20px"><?= $open ? 'Open' : 'Full' ?></span>
          </div>
        <?php endforeach; ?>
        <p class="foot-note">Attendee missing a ticket? Add them manually from the Attendees list.</p>

        <!-- Attendee list modal -->
        <div class="modal <?= isset($_GET['att']) ? 'open' : '' ?>" id="m-att">
          <div class="modal-card mid">
            <button type="button" class="modal-x" data-close aria-label="Close">x</button>
            <h3>Attendee List</h3>
            <p class="msg"><?= e($feat['title']) ?> · <?= e(fmt_date($feat)) ?> · <?= e($feat['venue']) ?></p>
            <div class="stat3">
              <div class="stat-tile"><b><?= (int)$feat['reg_count'] ?></b><span>Registered</span></div>
              <div class="stat-tile"><b><?= (int)$feat['in_count'] ?></b><span>Checked in</span></div>
              <div class="stat-tile"><b><?= max(0, (int)$feat['capacity'] - (int)$feat['reg_count']) ?></b><span>Slots left</span></div>
            </div>
            <div class="attendee-scroll">
              <?php foreach ($attendees as $a): ?>
                <div class="att">
                  <span class="ini"><?= e(initials($a['full_name'])) ?></span>
                  <div class="grow"><b><?= e($a['full_name']) ?></b><small><?= e($a['student_id']) ?> · <?= e(trim($a['course'] . ' ' . preg_replace('/\D/', '', (string)$a['year_level']))) ?></small></div>
                  <form method="post" title="Click to toggle check-in">
                    <?= csrf_field() ?><input type="hidden" name="action" value="checkin"><input type="hidden" name="reg_id" value="<?= (int)$a['id'] ?>"><input type="hidden" name="event_id" value="<?= (int)$feat['id'] ?>">
                    <button class="badge <?= $a['checked_in_at'] ? 'green' : '' ?>" type="submit"><?= $a['checked_in_at'] ? 'Checked in' : 'Registered' ?></button>
                  </form>
                </div>
              <?php endforeach; ?>
              <?php if (!$attendees): ?><p class="empty">No one has registered yet.</p><?php endif; ?>
            </div>
            <form method="post" class="add-att">
              <?= csrf_field() ?><input type="hidden" name="action" value="add_attendee"><input type="hidden" name="event_id" value="<?= (int)$feat['id'] ?>">
              <input class="input" name="student_id" placeholder="Add by student ID, e.g. 2023-00123" required>
              <button class="btn btn-primary" type="submit">Add</button>
            </form>
          </div>
        </div>

        <!-- Share modal -->
        <div class="modal" id="m-share">
          <div class="modal-card left">
            <h3>Share event</h3>
            <p class="msg" style="margin-bottom:16px">Invite friends to join this event.</p>
            <div class="copy-box" id="shareLinkText"><?= e(full_url('register-event.php?id=' . $feat['id'])) ?></div>
            <p class="copied msg" style="display:none;text-align:center;margin-top:20px;font-weight:600">Successfully Copied!</p>
            <button class="btn btn-primary copy-btn" data-copy="#shareLinkText">Copy</button>
            <a class="btn btn-outline" href="<?= e(url('home.php')) ?>">Back to dashboard</a>
          </div>
        </div>
      <?php else: ?>
        <p class="no-results" style="margin-top:20px">No upcoming events with tickets. <a class="link-btn" href="<?= e(url('event-add.php')) ?>">Add an event</a></p>
      <?php endif; ?>

    <?php else: /* faculty past */ ?>
      <div class="stats">
        <div class="stat"><b><?= $held ?></b><span>Events held</span></div>
        <div class="stat"><b><?= (int)$checked ?></b><span>Checked in</span></div>
      </div>
      <h2 class="section-title">Past Events</h2>
      <?php foreach ($events as $ev):
        $noShow = $ev['reg_count'] - $ev['in_count'];
        $pct = $ev['reg_count'] ? round($ev['in_count'] / $ev['reg_count'] * 100) : 0; ?>
        <div class="list-row">
          <span class="sq" style="background:<?= e($ev['color']) ?>"></span>
          <div class="grow"><b><?= e($ev['title']) ?></b><small><?= e(fmt_when($ev)) ?> · <?= e($ev['venue']) ?> · <?= (int)$ev['in_count'] ?> of <?= (int)$ev['reg_count'] ?> checked in</small></div>
          <span class="badge"><?= e($ev['category']) ?></span><span class="badge green" style="margin-left:14px">Completed</span>
          <button class="btn btn-outline btn-sm" style="margin-left:24px" data-open="m-rep-<?= (int)$ev['id'] ?>">View details</button>
        </div>

        <div class="modal" id="m-rep-<?= (int)$ev['id'] ?>">
          <div class="modal-card mid">
            <button type="button" class="modal-x" data-close aria-label="Close">x</button>
            <h3 style="font-size:22px">Event Report</h3>
            <h3 style="margin:10px 0 8px"><?= e($ev['title']) ?></h3>
            <span class="badge"><?= e($ev['category']) ?></span> <span class="badge green">Completed</span>
            <div class="kv-list" style="margin-top:20px">
              <div class="r"><span>Date &amp; time</span><b><?= e(fmt_when($ev)) ?></b></div>
              <div class="r"><span>Venue</span><b><?= e($ev['venue']) ?></b></div>
              <div class="r"><span>Event ID</span><b><?= e(event_code((int)$ev['id'])) ?></b></div>
              <div class="r"><span>Registered</span><b><?= (int)$ev['reg_count'] ?></b></div>
              <div class="r"><span>Checked in</span><b><?= (int)$ev['in_count'] ?></b></div>
              <div class="r"><span>No shows</span><b><?= (int)$noShow ?></b></div>
              <div class="r"><span>Price</span><b><?= e(price_label($ev['price'])) ?></b></div>
            </div>
            <div class="big-pct"><?= $pct ?>%</div>
            <small style="color:var(--muted)">Attendance</small>
            <div class="meter"><i style="width:<?= $pct ?>%"></i></div>
            <p class="msg" style="font-size:12px"><?= (int)$ev['in_count'] ?> of <?= (int)$ev['reg_count'] ?> registered students checked in.<br>Ticket scanning is closed because the event has ended.</p>
            <a class="btn btn-primary btn-lg" href="#" data-download="<?= e(url('report-download.php?id=' . $ev['id'])) ?>" data-after="m-repdl">Download Report</a>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (!$events): ?><p class="no-results">No past events yet.</p><?php endif; ?>
      <div class="modal" id="m-repdl"><div class="modal-card"><h3>Report Downloaded!</h3><p class="msg">Please check your storage</p><button class="btn btn-primary" data-close style="margin-top:46px">OKAY</button></div></div>
    <?php endif; ?>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

/* ======================= STUDENT VIEW ======================= */
require __DIR__ . '/includes/header.php';
$cols = "r.*, e.title, e.category, e.event_date, e.event_time, e.venue, e.color, e.price";
if ($tab === 'upcoming') {
    $tickets = q_all("SELECT $cols FROM registrations r JOIN events e ON e.id = r.event_id
                      WHERE r.user_id = ? AND r.status='registered' AND $future ORDER BY e.event_date, e.event_time", [$user['id']]);
    $feat = $tickets[0] ?? null; $t = (int)($_GET['t'] ?? 0);
    foreach ($tickets as $x) if ((int)$x['id'] === $t) $feat = $x;
    $others = array_values(array_filter($tickets, fn($x) => !$feat || (int)$x['id'] !== (int)$feat['id']));
    $genId = (int)($_GET['gen'] ?? 0);
} else {
    $tickets = q_all("SELECT $cols FROM registrations r JOIN events e ON e.id = r.event_id
                      WHERE r.user_id = ? AND r.status='registered' AND NOT ($future) ORDER BY e.event_date DESC, e.event_time DESC", [$user['id']]);
    $attended = count(array_filter($tickets, fn($x) => $x['checked_in_at']));
    $expired  = count($tickets) - $attended;
}
?>
<div class="page-head tabbed">
  <div><h1>My Tickets</h1><p class="sub"><?= $tab === 'upcoming' ? count($tickets) . ' tickets ready to scan' : 'Your ticket history' ?></p></div>
  <div class="tabs">
    <a class="pill <?= $tab === 'upcoming' ? 'active' : '' ?>" href="<?= e(url('tickets.php')) ?>">Upcoming</a>
    <a class="pill <?= $tab === 'past' ? 'active' : '' ?>" href="<?= e(url('tickets.php?tab=past')) ?>">Past</a>
  </div>
</div>

<?php if ($tab === 'upcoming'): ?>
  <?php if ($feat): $pending = $feat['payment_status'] === 'pending'; ?>
    <div class="card ticket-card" style="margin-top:18px">
      <div class="ticket-main">
        <div class="badges">
          <span class="badge <?= $pending ? 'yellow' : 'green' ?>"><?= $pending ? 'Payment pending' : 'Confirmed' ?></span>
          <span class="badge" style="margin-left:0"><?= e(days_label($feat)) ?></span>
        </div>
        <h2><?= e($feat['title']) ?></h2>
        <div class="kv-grid">
          <div class="kv" style="width:200px"><small>Date</small><b><?= e(fmt_date($feat)) ?></b></div>
          <div class="kv" style="width:200px"><small>Time</small><b><?= e(fmt_time($feat)) ?></b></div>
          <div class="kv"><small>Venue</small><b><?= e($feat['venue']) ?></b></div>
        </div>
        <div class="kv-grid">
          <div class="kv" style="width:150px"><small>Gate</small><b><?= e($feat['gate']) ?></b></div>
          <div class="kv" style="width:150px"><small>Seat</small><b><?= e($feat['seat']) ?></b></div>
          <div class="kv" style="width:150px"><small>Price</small><b><?= e(price_label($feat['price'])) ?></b></div>
          <div class="kv"><small>Ticket ID</small><b><?= e($feat['ticket_code']) ?></b></div>
        </div>
        <div class="ticket-actions">
          <a class="btn btn-outline" href="<?= e(url('calendar.php?id=' . $feat['id'])) ?>">Add to Calendar</a>
          <a class="btn btn-primary" href="<?= e(url('directions.php?id=' . $feat['id'])) ?>">Directions</a>
          <?php if ($pending): ?><a class="btn btn-outline" href="<?= e(url('payment.php?id=' . $feat['id'])) ?>">Pay</a><?php endif; ?>
          <button class="link-btn" style="color:var(--red);margin-left:6px" data-open="m-cancel">Cancel registration</button>
        </div>
      </div>
      <div class="ticket-qr">
        <div class="qr-box">
          <?php if ($pending): ?><span style="font-size:11px;color:var(--muted);text-align:center;padding:10px">Pay to unlock your QR</span>
          <?php else: ?><img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&margin=0&data=<?= e(urlencode($feat['ticket_code'])) ?>" alt="QR code for <?= e($feat['ticket_code']) ?>"><?php endif; ?>
        </div>
        <div class="qr-id"><?= e($feat['ticket_code']) ?></div>
      </div>
    </div>

    <div class="modal" id="m-cancel">
      <div class="modal-card modal-pop">
        <div class="circle pink"><?= icon('warn') ?></div>
        <h3>Cancel registration?</h3>
        <p class="msg">Your ticket for <?= e($feat['title']) ?> will be voided.<br>This action cannot be undone.</p>
        <form method="post" class="row-btns">
          <?= csrf_field() ?><input type="hidden" name="action" value="cancel"><input type="hidden" name="reg_id" value="<?= (int)$feat['id'] ?>">
          <button type="button" class="btn btn-grey" data-close>Keep it</button>
          <button type="submit" class="btn btn-red">Yes, Cancel</button>
        </form>
      </div>
    </div>
  <?php endif; ?>

  <?php if ($others): ?><h2 class="section-title">Other Tickets</h2><?php endif; ?>
  <?php foreach ($others as $tk): $pend = $tk['payment_status'] === 'pending'; ?>
    <div class="list-row">
      <span class="sq" style="background:<?= e($tk['color']) ?>"></span>
      <div class="grow"><a href="<?= e(url('tickets.php?t=' . $tk['id'])) ?>"><b><?= e($tk['title']) ?></b></a>
        <small><?= e(fmt_when($tk)) ?> · <?= e($tk['venue']) ?> · <?= e(price_label($tk['price'])) ?></small></div>
      <span class="badge <?= $pend ? 'yellow' : 'green' ?>" style="margin-right:30px"><?= $pend ? 'Pending' : 'Valid' ?></span>
      <?php if ($pend): ?>
        <a class="btn btn-outline" href="<?= e(url('payment.php?id=' . $tk['id'])) ?>">Pay</a>
      <?php elseif ($tk['generated_at']): ?>
        <button class="btn btn-outline" data-open="m-gen-<?= (int)$tk['id'] ?>">View Ticket</button>
      <?php else: ?>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="generate"><input type="hidden" name="reg_id" value="<?= (int)$tk['id'] ?>">
          <button class="btn btn-primary" type="submit">Generate Ticket</button></form>
      <?php endif; ?>
    </div>

  <?php endforeach; ?>
  <?php foreach ($tickets as $tk): if (!$tk['generated_at']) continue; ?>
    <div class="modal <?= $genId === (int)$tk['id'] ? 'open' : '' ?>" id="m-gen-<?= (int)$tk['id'] ?>">
      <div class="modal-card">
        <div class="circle green"><?= icon('check_g') ?></div>
        <h3>Ticket Generated!</h3>
        <p class="msg"><?= (float)$tk['price'] > 0 ? 'Your ticket was issued for' : 'A free ticket was issued for' ?><br><?= e($tk['title']) ?>.</p>
        <div class="kv-list">
          <div class="r"><span>Ticket ID</span><b><?= e($tk['ticket_code']) ?></b></div>
          <div class="r"><span>Attendee</span><b><?= e($tk['full_name']) ?></b></div>
          <div class="r"><span>Date</span><b><?= e(fmt_when($tk)) ?></b></div>
          <div class="r"><span>Venue</span><b><?= e($tk['venue']) ?></b></div>
          <div class="r"><span>Price</span><b><?= e(price_label($tk['price'])) ?></b></div>
        </div>
        <div class="row-btns">
          <a class="btn btn-outline" href="#" data-download="<?= e(url('ticket-download.php?id=' . $tk['id'])) ?>" data-after="m-tkdl">Download</a>
          <button class="btn btn-primary" data-close>Done</button>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$tickets): ?><p class="no-results" style="margin-top:20px">You have no upcoming tickets. <a class="link-btn" href="<?= e(url('events.php')) ?>">Browse events</a></p><?php endif; ?>
  <p class="foot-note">Missing a ticket? Check your email or contact the event organizer.</p>

  <div class="modal" id="m-tkdl"><div class="modal-card"><h3>Ticket Downloaded!</h3><p class="msg">Please check your storage</p><button class="btn btn-primary" data-close style="margin-top:46px">OKAY</button></div></div>

<?php else: /* student past */ ?>
  <div class="stats">
    <div class="stat"><b><?= $attended ?></b><span>Attended</span></div>
    <div class="stat"><b><?= $expired ?></b><span>Expired</span></div>
  </div>
  <h2 class="section-title">Past Tickets</h2>
  <?php foreach ($tickets as $tk): $used = (bool)$tk['checked_in_at']; ?>
    <div class="list-row">
      <span class="sq" style="background:<?= e($tk['color']) ?>"></span>
      <div class="grow"><b><?= e($tk['title']) ?></b><small><?= e(fmt_when($tk)) ?> · <?= e($tk['venue']) ?></small></div>
      <span class="badge"><?= e($tk['category']) ?></span>
      <span class="badge gray" style="margin-left:14px"><?= $used ? 'Used' : 'Expired' ?></span>
      <button class="btn btn-outline btn-sm" style="margin-left:24px" data-open="m-det-<?= (int)$tk['id'] ?>">View details</button>
    </div>

    <div class="modal" id="m-det-<?= (int)$tk['id'] ?>">
      <div class="modal-card mid">
        <button type="button" class="modal-x" data-close aria-label="Close">x</button>
        <h3 style="font-size:22px">Ticket Details</h3>
        <h3 style="margin:14px 0 4px"><?= e($tk['title']) ?></h3>
        <p class="sub"><?= e(fmt_when($tk)) ?></p>
        <div style="margin:10px 0"><span class="badge"><?= e($tk['category']) ?></span> <span class="badge gray"><?= $used ? 'Used' : 'Expired' ?></span></div>
        <div class="kv-list">
          <div class="r"><span>Date &amp; time</span><b><?= e(fmt_when($tk)) ?></b></div>
          <div class="r"><span>Venue</span><b><?= e($tk['venue']) ?></b></div>
          <div class="r"><span>Ticket ID</span><b><?= e($tk['ticket_code']) ?></b></div>
          <div class="r"><span>Gate / Seat</span><b><?= e($tk['gate']) ?> / <?= e($tk['seat']) ?></b></div>
          <div class="r"><span>Price</span><b><?= e(price_label($tk['price'])) ?></b></div>
          <div class="r"><span>Checked in</span><b><?= $used ? e(date('M j, Y · g:i A', strtotime($tk['checked_in_at']))) : 'Not checked in' ?></b></div>
        </div>
        <div class="card" style="width:160px;height:160px;margin:16px auto;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:12px">
          <b style="font-size:15px">Ticket <?= $used ? 'used' : 'expired' ?></b>
          <span class="msg" style="font-size:10.5px;color:var(--muted);margin-top:6px">This QR code is no longer valid for entry.</span>
        </div>
        <a class="btn btn-primary btn-lg" href="#" data-download="<?= e(url('receipt.php?id=' . $tk['id'])) ?>" data-after="m-rcdl">Download Receipt</a>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$tickets): ?><p class="no-results">No past tickets yet.</p><?php endif; ?>
  <div class="modal" id="m-rcdl"><div class="modal-card"><h3>Receipt Downloaded!</h3><p class="msg">Please check your storage</p><button class="btn btn-primary" data-close style="margin-top:46px">OKAY</button></div></div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
