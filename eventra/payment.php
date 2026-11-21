<?php
require __DIR__ . '/includes/bootstrap.php';
$user = require_role('student');

$id  = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$reg = q_one("SELECT r.*, e.title, e.category, e.event_date, e.event_time, e.venue, e.color, e.price, e.organizer_id
              FROM registrations r JOIN events e ON e.id = r.event_id WHERE r.id = ? AND r.user_id = ? AND r.status = 'registered'", [$id, $user['id']]);
if (!$reg || $reg['payment_status'] !== 'pending') { flash('error', 'Nothing to pay for this ticket.'); redirect('tickets.php'); }

$error = null;
if (is_post()) {
    csrf_check();
    $m = $_POST['method'] ?? '';
    if (!in_array($m, ['gcash', 'maya', 'card'], true)) {
        $error = 'Please choose a payment method.';
    } else {
        /* DEMO PAYMENT: no real money moves. Connect a gateway (PayMongo, Xendit...) here later. */
        $ref = 'PAY-' . strtoupper(bin2hex(random_bytes(4)));
        q('INSERT INTO payments (registration_id, method, amount, reference) VALUES (?,?,?,?)', [$id, $m, $reg['price'], $ref]);
        q("UPDATE registrations SET payment_status = 'paid' WHERE id = ?", [$id]);
        notify($user['id'], 'Payment received', 'We received ' . money($reg['price']) . ' for ' . $reg['title'] . '. Ref: ' . $ref . '.');
        notify((int)$reg['organizer_id'], 'Payment received', user_name($user) . ' paid ' . money($reg['price']) . ' for ' . $reg['title'] . '.');
        flash('success', 'Payment successful! Your ticket is confirmed.');
        redirect('tickets.php?t=' . $id);
    }
}

$page_title = 'Payment'; $active = 'tickets';
require __DIR__ . '/includes/header.php';
$methods = ['gcash' => ['G', 'GCash', 'Pay with your GCash wallet'], 'maya' => ['M', 'Maya', 'Pay with your Maya wallet'], 'card' => ['$', 'Credit or debit card', 'Visa or Mastercard']];
?>
<h1>Payment</h1>
<p class="sub">Review your order and choose how to pay</p>
<?php if ($error): ?><div class="alert err" style="max-width:520px"><?= e($error) ?></div><?php endif; ?>

<form method="post" class="pay-grid">
  <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$id ?>">
  <div class="card pay-card">
    <div class="top">
      <span class="sq" style="background:<?= e($reg['color']) ?>"></span>
      <div><h3><?= e($reg['title']) ?></h3><small><?= e(fmt_when($reg)) ?></small><small><?= e($reg['venue']) ?> · <?= e(price_label($reg['price'])) ?></small></div>
      <span class="badge"><?= e($reg['category']) ?></span>
    </div>
    <div style="height:18px"></div>
    <div class="pay-line"><span>Ticket (1×)</span><b><?= e(money($reg['price'])) ?></b></div>
    <div class="pay-line"><span>Service fee</span><b>₱0.00</b></div>
    <div class="pay-total"><span>Total</span><b><?= e(money($reg['price'])) ?></b></div>
    <div style="margin-top:18px"><span class="badge yellow" style="height:24px;padding:0 12px">Pending</span></div>
  </div>

  <div class="card" style="padding:24px 32px 28px">
    <h3 style="font-size:18px;margin-bottom:16px">Payment method</h3>
    <?php foreach ($methods as $key => [$ic, $name, $desc]): ?>
      <label class="method"><input type="radio" name="method" value="<?= $key ?>" <?= ($_POST['method'] ?? '') === $key ? 'checked' : '' ?>>
        <span class="ic"><?= e($ic) ?></span><span><b><?= e($name) ?></b><small><?= e($desc) ?></small></span><span class="radio"></span></label>
    <?php endforeach; ?>
    <p class="secure">Payments are encrypted and secure.</p>
    <button class="btn btn-primary btn-lg btn-block" id="payBtn" type="submit">Pay <?= e(money($reg['price'])) ?></button>
  </div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
