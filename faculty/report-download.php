<?php
require __DIR__ . '/../includes/bootstrap.php';
$user = require_role('faculty');
$id = (int)($_GET['id'] ?? 0);
$ev = q_one('SELECT * FROM events WHERE id = ? AND organizer_id = ?', [$id, $user['id']]);
if (!$ev) { http_response_code(404); exit('Not found'); }
$rows = q_all("SELECT * FROM registrations WHERE event_id = ? AND status = 'registered' ORDER BY full_name", [$id]);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="eventra-report-' . event_code($id) . '.csv"');
$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF");   // UTF-8 BOM so Excel opens it correctly
fputcsv($out, ['Event', $ev['title']]);
fputcsv($out, ['Date & time', fmt_when($ev)]);
fputcsv($out, ['Venue', $ev['venue']]);
fputcsv($out, []);
fputcsv($out, ['Ticket ID', 'Name', 'Student ID', 'Email', 'Course', 'Year', 'Payment', 'Checked in']);
foreach ($rows as $r) fputcsv($out, [$r['ticket_code'], $r['full_name'], $r['student_id'], $r['email'], $r['course'], $r['year_level'], $r['payment_status'], $r['checked_in_at'] ?: 'No show']);
