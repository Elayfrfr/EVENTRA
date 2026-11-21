<?php
require __DIR__ . '/includes/bootstrap.php';
$user = require_role('student');
$id  = (int)($_GET['id'] ?? 0);
$reg = q_one("SELECT r.id, r.ticket_code, e.title, e.venue, e.address, e.event_date, e.event_time FROM registrations r JOIN events e ON e.id = r.event_id
              WHERE r.id = ? AND r.user_id = ? AND r.status = 'registered'", [$id, $user['id']]);
if (!$reg) { http_response_code(404); exit('Not found'); }

$esc = fn($s) => str_replace(["\\", ";", ",", "\r\n", "\n"], ["\\\\", "\;", "\\,", "\\n", "\\n"], $s);
$start = event_ts($reg);
$lines = [
    'BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//Eventra//EN', 'CALSCALE:GREGORIAN', 'BEGIN:VEVENT',
    'UID:' . $reg['ticket_code'] . '@eventra',
    'DTSTAMP:' . gmdate('Ymd\THis\Z'),
    'DTSTART:' . gmdate('Ymd\THis\Z', $start),
    'DTEND:' . gmdate('Ymd\THis\Z', $start + 7200),
    'SUMMARY:' . $esc($reg['title']),
    'LOCATION:' . $esc(trim($reg['venue'] . ($reg['address'] ? ', ' . $reg['address'] : ''))),
    'DESCRIPTION:' . $esc('Ticket ' . $reg['ticket_code'] . ' - booked on Eventra'),
    'END:VEVENT', 'END:VCALENDAR',
];
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="eventra-' . $reg['ticket_code'] . '.ics"');
echo implode("\r\n", $lines) . "\r\n";
