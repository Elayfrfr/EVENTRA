<?php
require __DIR__ . '/includes/bootstrap.php';
$user = require_role('student');
$id  = (int)($_GET['id'] ?? 0);
$r = q_one("SELECT r.*, e.title, e.event_date, e.event_time, e.venue, e.price FROM registrations r JOIN events e ON e.id = r.event_id
            WHERE r.id = ? AND r.user_id = ? AND r.status = 'registered' AND r.payment_status <> 'pending'", [$id, $user['id']]);
if (!$r) { http_response_code(404); exit('Not found'); }

header('Content-Type: text/html; charset=utf-8');
header('Content-Disposition: attachment; filename="eventra-ticket-' . $r['ticket_code'] . '.html"');
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Eventra E-Ticket</title>
<style>body{font-family:Arial,sans-serif;background:#FFF0F9;padding:30px}.t{max-width:420px;margin:auto;background:#fff;border-radius:16px;overflow:hidden;border:1px solid #F2CBE6}.h{background:linear-gradient(135deg,#6B2A9E,#C02AA5);color:#fff;padding:14px 20px;display:flex;justify-content:space-between;font-weight:bold}.b{padding:20px}h2{margin:0 0 14px;color:#2A1535}p{margin:6px 0;color:#7A6683;font-size:13px}p b{color:#2A1535}.q{text-align:center;margin-top:16px}</style></head><body>
<div class="t"><div class="h"><span>Eventra</span><span>E-TICKET</span></div><div class="b">
<h2><?= e($r['title']) ?></h2>
<p>Date &amp; time: <b><?= e(fmt_when($r)) ?></b></p>
<p>Venue: <b><?= e($r['venue']) ?></b></p>
<p>Name: <b><?= e($r['full_name']) ?></b></p>
<p>Ticket no.: <b><?= e($r['ticket_code']) ?></b> · Gate <b><?= e($r['gate']) ?></b> · <b><?= e($r['seat']) ?></b></p>
<div class="q"><img width="160" height="160" alt="QR" src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&margin=0&data=<?= e(urlencode($r['ticket_code'])) ?>"></div>
</div></div></body></html>
