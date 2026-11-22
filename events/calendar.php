<?php
require __DIR__ . '/../includes/bootstrap.php';
$user = require_role('student');
$id  = (int)($_GET['id'] ?? 0);
$reg = q_one("SELECT r.id, e.title, e.venue, e.address, e.description, e.event_date, e.event_time FROM registrations r JOIN events e ON e.id = r.event_id
              WHERE r.id = ? AND r.user_id = ? AND r.status = 'registered'", [$id, $user['id']]);
if (!$reg) { flash('error', 'Ticket not found.'); redirect('tickets/tickets.php'); }

$start = event_ts($reg); $end = $start + 2 * 3600;           // events default to 2 hours
$fmt   = fn($t) => gmdate('Ymd\THis\Z', $t);
$where = trim($reg['venue'] . ($reg['address'] ? ', ' . $reg['address'] : ''));
$google  = 'https://calendar.google.com/calendar/render?action=TEMPLATE&text=' . urlencode($reg['title']) . '&dates=' . $fmt($start) . '/' . $fmt($end)
         . '&location=' . urlencode($where) . '&details=' . urlencode('Ticket booked on Eventra');
$outlook = 'https://outlook.live.com/calendar/0/deeplink/compose?path=/calendar/action/compose&rru=addevent&subject=' . urlencode($reg['title'])
         . '&startdt=' . urlencode(gmdate('Y-m-d\TH:i:s\Z', $start)) . '&enddt=' . urlencode(gmdate('Y-m-d\TH:i:s\Z', $end)) . '&location=' . urlencode($where);
$ics     = url('events/ics.php?id=' . $id);

$page_title = 'Add to Calendar'; $active = 'tickets';
require __DIR__ . '/../includes/layout/header.php';
$opts = [['google', 'Google Calendar', '', $google], ['apple', 'Apple Calendar', '', $ics], ['outlook', 'Outlook', '', $outlook], ['ics', 'Download .ics file', 'Works with any calendar app', $ics]];
?>
<div class="card narrow slim" style="max-width:400px;margin-top:34px;padding:26px 32px 30px;min-height:660px;display:flex;flex-direction:column">
  <h2 style="font-size:20px;margin-bottom:12px">Add to Calendar</h2>
  <b style="font-size:13px"><?= e($reg['title']) ?></b>
  <p class="sub" style="font-size:11.5px"><?= e(fmt_when($reg)) ?> · <?= e($reg['venue']) ?></p>
  <p style="font-weight:700;color:var(--muted);font-size:12.5px;margin:22px 0 10px">Choose calendar</p>
  <?php foreach ($opts as [$val, $name, $note, $link]): ?>
    <label class="opt"><input type="radio" name="cal" value="<?= $val ?>" data-url="<?= e($link) ?>">
      <span><b><?= e($name) ?></b><?php if ($note): ?><small><?= e($note) ?></small><?php endif; ?></span><span class="radio"></span></label>
  <?php endforeach; ?>
  <div style="flex:1"></div>
  <button class="btn btn-primary btn-lg btn-block" id="calBtn" type="button">Add to Calendar</button>
</div>

<div class="modal" id="m-added">
  <div class="modal-card">
    <div class="circle soft"><span class="circle purple" style="width:42px;height:42px;margin:0"><?= str_replace(['width="30"', 'height="30"'], ['width="22"', 'height="22"'], icon('check')) ?></span></div>
    <h3>Added to Calendar!</h3>
    <p class="msg">Your event has been successfully added to<br>your calendar.</p>
    <div class="event-chip"><i></i><span><b><?= e($reg['title']) ?></b><?= e(date('F j, Y', strtotime($reg['event_date']))) ?> · <?= e(fmt_time($reg)) ?></span></div>
    <a class="btn btn-primary" href="<?= e(url('tickets/tickets.php')) ?>">Done</a>
  </div>
</div>
<?php require __DIR__ . '/../includes/layout/footer.php'; ?>
