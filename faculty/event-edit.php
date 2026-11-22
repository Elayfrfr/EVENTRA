<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/partials/event_form.php';
$user = require_role('faculty');

/* After a delete we come back here with ?deleted=1 just to show the confirmation modal */
if (isset($_GET['deleted'])) {
    $page_title = 'Event Deleted'; $active = 'events';
    require __DIR__ . '/../includes/layout/header.php'; ?>
    <div class="modal open modal-pop" data-sticky>
      <div class="modal-card">
        <h3>Event Deleted</h3>
        <p class="msg">Successfully Deleted<br>The event was removed from your events.</p>
        <a class="btn btn-primary" style="margin-top:46px" href="<?= e(url('dashboard/home.php')) ?>">Back to dashboard</a>
      </div>
    </div>
    <?php require __DIR__ . '/../includes/layout/footer.php'; exit;
}

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$ev = q_one("SELECT e.*, (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id AND r.status = 'registered') AS reg_count
             FROM events e WHERE e.id = ? AND e.organizer_id = ?", [$id, $user['id']]);
if (!$ev) { flash('error', 'Event not found.'); redirect('events/events.php'); }

$f = event_form_values(['title' => $ev['title'], 'category' => $ev['category'], 'event_date' => $ev['event_date'], 'event_time' => substr($ev['event_time'], 0, 5),
                        'capacity' => $ev['capacity'], 'venue' => $ev['venue'], 'price' => $ev['price'] + 0, 'description' => $ev['description']]);
$status = $ev['status'];
$error = null;

if (is_post()) {
    csrf_check();
    $action = $_POST['action'] ?? 'save';

    if ($action === 'delete') {
        $regs = q_all("SELECT user_id FROM registrations WHERE event_id = ? AND status = 'registered'", [$id]);
        foreach ($regs as $r) notify((int)$r['user_id'], 'Event cancelled', $ev['title'] . ' has been cancelled by the organizer.');
        q('DELETE FROM events WHERE id = ?', [$id]);
        redirect('faculty/event-edit.php?deleted=1');
    }

    $f = event_form_values($_POST);
    $status = ($_POST['status'] ?? 'published') === 'draft' ? 'draft' : 'published';
    $error = event_form_error($f, (int)$ev['reg_count']);
    if (!$error) {
        $color = ($f['category'] === $ev['category']) ? $ev['color'] : CAT_COLORS[$f['category']];
        q('UPDATE events SET title=?, category=?, event_date=?, event_time=?, venue=?, description=?, capacity=?, price=?, color=?, status=? WHERE id=?',
          [$f['title'], $f['category'], $f['event_date'], $f['event_time'] . ':00', $f['venue'], $f['description'] ?: null, (int)$f['capacity'], (float)$f['price'], $color, $status, $id]);

        $changed = $f['title'] !== $ev['title'] || $f['event_date'] !== $ev['event_date'] || $f['event_time'] . ':00' !== $ev['event_time'] || $f['venue'] !== $ev['venue'];
        if ($changed && $status === 'published') {
            foreach (q_all("SELECT user_id FROM registrations WHERE event_id = ? AND status = 'registered'", [$id]) as $r)
                notify((int)$r['user_id'], 'Event updated', $f['title'] . ' details were updated. Check the new details.');
        }
        if ($ev['status'] === 'draft' && $status === 'published')
            q("INSERT INTO notifications (user_id, title, body) SELECT id, 'New event posted', ? FROM users WHERE role = 'student'", [$f['title'] . ' is now open for registration.']);
        flash('success', 'Changes saved.');
        redirect('faculty/event-edit.php?id=' . $id);
    }
}

$page_title = 'Edit Event'; $active = 'events';
require __DIR__ . '/../includes/layout/header.php';
?>
<div class="card form-card" style="max-width:600px;margin-top:22px">
  <h2>Edit Event</h2>
  <p class="sub" style="margin-bottom:6px"><?= (int)$ev['reg_count'] ?> registered · Update the details below</p>
  <?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>
  <form method="post" id="editForm" novalidate>
    <?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="action" value="save">
    <?php event_form_fields($f, true, $status); ?>
    <div class="actions">
      <button type="button" class="btn btn-danger-outline" data-open="m-delete" style="width:150px">Delete</button>
      <button type="submit" class="btn btn-primary">Save Changes</button>
    </div>
  </form>
</div>

<div class="modal" id="m-delete">
  <div class="modal-card modal-pop">
    <div class="circle pink"><?= icon('warn') ?></div>
    <h3>Delete this event?</h3>
    <p class="msg"><?= e($ev['title']) ?> will be removed and<br>the <?= (int)$ev['reg_count'] ?> registered attendees will be notified.<br>This action cannot be undone.</p>
    <form method="post" class="row-btns">
      <?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="action" value="delete">
      <button type="button" class="btn btn-grey" data-close>Cancel</button>
      <button type="submit" class="btn btn-red">Delete Event</button>
    </form>
  </div>
</div>
<?php require __DIR__ . '/../includes/layout/footer.php'; ?>
