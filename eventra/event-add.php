<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/event_form.php';
$user = require_role('faculty');
$f = event_form_values(['category' => 'Academic', 'price' => '0']);
$error = null;

if (is_post()) {
    csrf_check();
    $f = event_form_values($_POST);
    $status = ($_POST['status'] ?? '') === 'draft' ? 'draft' : 'published';
    $error = event_form_error($f);
    if (!$error && $status === 'published' && strtotime($f['event_date'] . ' ' . $f['event_time']) < time()) $error = 'The event date and time must be in the future.';

    if (!$error) {
        q('INSERT INTO events (organizer_id, title, category, event_date, event_time, venue, address, description, capacity, price, color, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)',
          [$user['id'], $f['title'], $f['category'], $f['event_date'], $f['event_time'] . ':00', $f['venue'], 'University of Cebu, Cebu City',
           $f['description'] ?: null, (int)$f['capacity'], (float)$f['price'], CAT_COLORS[$f['category']], $status]);
        $newId = (int)db()->lastInsertId();
        if ($status === 'published') {
            q("INSERT INTO notifications (user_id, title, body) SELECT id, 'New event posted', ? FROM users WHERE role = 'student'", [$f['title'] . ' is now open for registration.']);
            redirect('event-add.php?published=' . $newId);
        }
        flash('success', 'Saved as draft. Publish it from Edit Event when ready.');
        redirect('events-created.php');
    }
}

$pub = isset($_GET['published']) ? q_one('SELECT * FROM events WHERE id = ? AND organizer_id = ?', [(int)$_GET['published'], $user['id']]) : null;
$page_title = 'Add Event'; $active = 'events';
require __DIR__ . '/includes/header.php';
?>
<div class="card form-card" style="max-width:720px;margin-top:22px">
  <h2>Add New Event</h2>
  <p class="sub" style="margin-bottom:6px">Fill in the details below</p>
  <?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>
  <form method="post" novalidate>
    <?= csrf_field() ?>
    <?php event_form_fields($f); ?>
    <div class="actions" style="flex-direction:column;gap:12px;margin-top:34px">
      <button class="btn btn-primary btn-lg" type="submit" name="status" value="published">Publish Event</button>
      <button class="link-btn" type="submit" name="status" value="draft">Save as draft</button>
    </div>
  </form>
</div>

<?php if ($pub): ?>
<div class="modal open" data-sticky>
  <div class="modal-card">
    <div class="circle green"><?= icon('check_g') ?></div>
    <h3>Event published!</h3>
    <p class="msg" style="margin-top:8px">My Events</p>
    <p style="font-weight:700;font-size:14px"><?= e($pub['title']) ?></p>
    <p class="msg"><?= e(fmt_when($pub)) ?></p>
    <span class="badge green" style="margin-top:10px">PUBLISHED</span>
    <a class="btn btn-outline" href="<?= e(url('home.php')) ?>">Back to dashboard</a>
  </div>
</div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
