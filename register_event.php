<?php
require_once __DIR__ . '/includes/functions.php';
$user = require_student($pdo);

// 1. Find the event (must still be upcoming)
$eventId = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare('SELECT * FROM events WHERE id = ? AND event_date >= NOW()');
$st->execute([$eventId]);
$event = $st->fetch();
if (!$event) {
    redirect('events.php');
}

// 2. Already registered? Go to the tickets page
$st = $pdo->prepare('SELECT id FROM registrations WHERE user_id = ? AND event_id = ?');
$st->execute([$user['id'], $eventId]);
if ($st->fetch()) {
    redirect('tickets.php');
}

// 3. Form values (pre-filled from the account)
$error = '';
$form = [
    'full_name'  => $user['first_name'] . ' ' . $user['last_name'],
    'student_id' => $user['id_number'],
    'email'      => $user['email'],
    'course'     => (string)$user['course'],
    'year_level' => (string)$user['year_level'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($form as $key => $value) {
        $form[$key] = trim($_POST[$key] ?? '');
    }

    if (!csrf_ok()) {
        $error = 'Session expired. Please try again.';
    } elseif (in_array('', $form, true)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!in_array($form['year_level'], YEAR_LEVELS, true)) {
        $error = 'Please select your year level.';
    } elseif (empty($_POST['confirm'])) {
        $error = 'Please confirm your details and accept the Terms and Conditions.';
    } else {
        $st = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE event_id = ? AND status = 'confirmed'");
        $st->execute([$eventId]);
        if ((int)$st->fetchColumn() >= (int)$event['capacity']) {
            $error = 'Sorry, this event is already full.';
        } else {
            try {
                $st = $pdo->prepare(
                    'INSERT INTO registrations (user_id, event_id, full_name, student_id, email, course, year_level)
                     VALUES (?, ?, ?, ?, ?, ?, ?)'
                );
                $st->execute([
                    $user['id'], $eventId, $form['full_name'], $form['student_id'],
                    $form['email'], $form['course'], $form['year_level'],
                ]);
                $regId = (int)$pdo->lastInsertId();

                // Ticket code, e.g. EVT002-0015
                $code = 'EVT' . str_pad($eventId, 3, '0', STR_PAD_LEFT) . '-' . str_pad($regId, 4, '0', STR_PAD_LEFT);
                $pdo->prepare('UPDATE registrations SET ticket_code = ? WHERE id = ?')->execute([$code, $regId]);

                // Remember course and year level for the Profile page
                $pdo->prepare('UPDATE users SET course = ?, year_level = ? WHERE id = ?')
                    ->execute([$form['course'], $form['year_level'], $user['id']]);

                redirect('tickets.php?new=1');
            } catch (PDOException $ex) {
                $error = 'You are already registered for this event.';
            }
        }
    }
}

$pageTitle = 'Register for Event';
$active    = 'events';
include __DIR__ . '/includes/header.php';
?>
<a class="back" href="events.php">&larr; Back to Events</a>
<h1 class="page-title">Register for Event</h1>
<p class="page-sub">Fill in your details to get your ticket.</p>

<div class="reg-layout">
  <aside class="card reg-event">
    <div class="event-img event-img-lg" style="background: <?= e($event['color']) ?>">
      <span class="chip-white"><?= e($event['category']) ?></span>
    </div>
    <h2><?= e($event['title']) ?></h2>
    <p class="meta"><?= e(fmt_datetime($event['event_date'])) ?></p>
    <p class="meta"><?= e($event['venue']) ?></p>
    <p class="meta"><?= ((float)$event['price'] > 0 ? e(price_text($event['price'])) : 'Free admission') ?> · Open to all students</p>
    <hr>
    <p class="note">Your ticket will be sent to your Tickets tab after you register.</p>
  </aside>

  <section class="card reg-form">
    <h3>Your details</h3>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <form method="post" action="register_event.php?id=<?= (int)$eventId ?>" novalidate>
      <?= csrf_field() ?>
      <div class="row2">
        <div class="field">
          <label for="full_name">Full Name</label>
          <input type="text" id="full_name" name="full_name" placeholder="Enter your full name" value="<?= e($form['full_name']) ?>" maxlength="100">
        </div>
        <div class="field">
          <label for="student_id">Student ID</label>
          <input type="text" id="student_id" name="student_id" placeholder="Enter your student ID" value="<?= e($form['student_id']) ?>" maxlength="30">
        </div>
      </div>
      <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="Enter your email" value="<?= e($form['email']) ?>" maxlength="100">
      </div>
      <div class="row2">
        <div class="field">
          <label for="course">Course</label>
          <input type="text" id="course" name="course" placeholder="Enter your course" value="<?= e($form['course']) ?>" maxlength="100">
        </div>
        <div class="field">
          <label for="year_level">Year Level</label>
          <select id="year_level" name="year_level">
            <option value="">Select year level</option>
            <?php foreach (YEAR_LEVELS as $y): ?>
              <option value="<?= e($y) ?>" <?= $form['year_level'] === $y ? 'selected' : '' ?>><?= e($y) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <label class="check check-sm">
        <input type="checkbox" name="confirm" value="1">
        <span>I confirm my details are correct and agree to the <a href="terms.php" target="_blank">Terms and Conditions</a></span>
      </label>

      <div class="btn-row">
        <a class="btn btn-outline" href="events.php">Cancel</a>
        <button type="submit" class="btn btn-primary">Register</button>
      </div>
    </form>
  </section>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
