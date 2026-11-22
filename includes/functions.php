<?php
/* ------------------------------------------------------------------
 * Eventra helper functions
 * ------------------------------------------------------------------ */

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

const CATEGORIES = ['Academic', 'Music', 'Sports', 'Arts'];
const CAT_COLORS = [
    'Sports'   => '#F59AB8',
    'Music'    => '#B07CE8',
    'Academic' => '#6FC7C0',
    'Arts'     => '#E8D26A',
];
const YEAR_LEVELS = ['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'];

/* ---------- output / routing ---------- */
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function url(string $path = ''): string { return BASE_URL . '/' . ltrim($path, '/'); }
function redirect(string $path): void { header('Location: ' . url($path)); exit; }

function full_url(string $path = ''): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    return ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . url($path);
}

/* ---------- CSRF ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
function csrf_check(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419);
        die('Your session expired. Please go back, refresh the page and try again.');
    }
}
function is_post(): bool { return $_SERVER['REQUEST_METHOD'] === 'POST'; }

/* ---------- flash messages ---------- */
function flash(string $type, ?string $msg = null): ?string
{
    if ($msg !== null) { $_SESSION['flash'][$type] = $msg; return null; }
    $m = $_SESSION['flash'][$type] ?? null;
    unset($_SESSION['flash'][$type]);
    return $m;
}

/* ---------- auth ---------- */
function current_user(bool $refresh = false): ?array
{
    static $user = false;
    if ($refresh) $user = false;
    if ($user === false) {
        $user = !empty($_SESSION['uid']) ? q_one('SELECT * FROM users WHERE id = ?', [$_SESSION['uid']]) : null;
        if (!$user) unset($_SESSION['uid']);
    }
    return $user;
}
function require_login(): array
{
    $u = current_user();
    if (!$u) redirect('landing.php');
    return $u;
}
function require_role(string $role): array
{
    $u = require_login();
    if ($u['role'] !== $role) redirect('dashboard/home.php');
    return $u;
}
function is_faculty(?array $u): bool { return $u && $u['role'] === 'faculty'; }

function user_name(array $u): string { return trim($u['first_name'] . ' ' . $u['last_name']); }
function display_name(array $u): string
{
    // Faculty are shown with the "Prof." prefix once, e.g. "Prof. Zemary Dumabog"
    $n = user_name($u);
    return ($u['role'] === 'faculty' && stripos($n, 'Prof.') !== 0) ? 'Prof. ' . $n : $n;
}
function initials(string $name): string
{
    $name = trim(preg_replace('/^Prof\.\s*/i', '', $name));
    $parts = preg_split('/\s+/', $name);
    $s = strtoupper(substr($parts[0] ?? '', 0, 1));
    if (count($parts) > 1) $s .= strtoupper(substr(end($parts), 0, 1));
    return $s;
}
function avatar(array $u, int $size = 36, bool $single = false): string
{
    $style = 'width:' . $size . 'px;height:' . $size . 'px;font-size:' . round($size * 0.38) . 'px';
    if (!empty($u['avatar'])) {
        return '<span class="avatar" style="' . $style . '"><img src="' . e(url($u['avatar'])) . '" alt=""></span>';
    }
    $txt = $single ? strtoupper(substr(user_name($u), 0, 1)) : initials(user_name($u));
    return '<span class="avatar" style="' . $style . '">' . e($txt) . '</span>';
}

/* ---------- validation ---------- */
function password_error(string $pw): ?string
{
    if (strlen($pw) < 15 || !preg_match('/[0-9]/', $pw) || !preg_match('/[A-Z]/', $pw) || !preg_match('/[a-z]/', $pw)) {
        return PASSWORD_HINT . '.';
    }
    return null;
}

/* ---------- dates / money ---------- */
function event_ts(array $ev): int { return strtotime($ev['event_date'] . ' ' . $ev['event_time']); }
function is_upcoming(array $ev): bool { return event_ts($ev) >= time(); }
function fmt_date(array $ev): string { return date('M j, Y', strtotime($ev['event_date'])); }
function fmt_time(array $ev): string { return date('g:i A', strtotime($ev['event_time'])); }
function fmt_when(array $ev): string { return fmt_date($ev) . ' · ' . fmt_time($ev); }
function money($n): string { return '₱' . number_format((float)$n, 2); }
function price_label($n): string { return (float)$n > 0 ? '₱' . rtrim(rtrim(number_format((float)$n, 2), '0'), '.') : 'Free'; }

function days_label(array $ev): string
{
    $d = (int)floor((strtotime($ev['event_date']) - strtotime(date('Y-m-d'))) / 86400);
    if ($d <= 0) return 'Today';
    if ($d === 1) return 'Tomorrow';
    return 'In ' . $d . ' days';
}
function ago_label(string $datetime): string
{
    $d = (int)floor((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime($datetime)))) / 86400);
    if ($d <= 0) return 'Today';
    if ($d === 1) return 'Yesterday';
    return $d . ' days ago';
}

/* ---------- codes ---------- */
function event_code(int $id): string { return 'UC-EV-' . str_pad((string)$id, 4, '0', STR_PAD_LEFT); }
function ticket_code_for(int $regId): string { return 'EVT-' . date('Y') . '-' . str_pad((string)$regId, 4, '0', STR_PAD_LEFT); }

/* ---------- notifications ---------- */
function notify(int $userId, string $title, string $body): void
{
    q('INSERT INTO notifications (user_id, title, body) VALUES (?,?,?)', [$userId, $title, $body]);
}
function unread_count(int $userId): int
{
    return (int)q_val('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0', [$userId]);
}
/** Lazily create "Event reminder" notifications for tickets starting within 24h (no cron needed on free hosting). */
function generate_reminders(array $u): void
{
    if ($u['role'] !== 'student') return;
    $rows = q_all(
        "SELECT r.id, ev.title, ev.event_time, ev.venue, ev.event_date FROM registrations r
         JOIN events ev ON ev.id = r.event_id
         WHERE r.user_id = ? AND r.status = 'registered' AND r.reminded = 0
           AND TIMESTAMP(ev.event_date, ev.event_time) BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 24 HOUR)",
        [$u['id']]
    );
    foreach ($rows as $r) {
        notify($u['id'], 'Event reminder', $r['title'] . ' starts at ' . date('g:i A', strtotime($r['event_time'])) . ', ' . $r['venue'] . '.');
        q('UPDATE registrations SET reminded = 1 WHERE id = ?', [$r['id']]);
    }
}

/* ---------- reusable UI pieces ---------- */
function category_pills(string $current, string $base, string $extra = ''): string
{
    $out = '<div class="pills">';
    foreach (array_merge(['All'], CATEGORIES) as $c) {
        $href = $base . '?cat=' . urlencode($c) . $extra;
        $out .= '<a class="pill' . ($c === $current ? ' active' : '') . '" href="' . e(url($href)) . '">' . e($c) . '</a>';
    }
    return $out . '</div>';
}

function event_card(array $ev, ?array $user, ?array $reg = null): string
{
    $color = $ev['color'] ?: (CAT_COLORS[$ev['category']] ?? '#B07CE8');
    $h  = '<div class="card event-card">';
    $h .= '<div class="event-img" style="background:' . e($color) . '"><span class="tag">' . e($ev['category']) . '</span></div>';
    $h .= '<h3>' . e($ev['title']) . '</h3>';
    $h .= '<p class="meta">' . e(fmt_when($ev)) . '</p>';
    if (is_faculty($user)) {
        $h .= '<p class="meta">' . e($ev['venue']) . ' · ' . (int)$ev['reg_count'] . ' registered' . ($ev['status'] === 'draft' ? ' · Draft' : '') . '</p>';
        $h .= '<a class="btn btn-primary btn-block" href="' . e(url('faculty/event-edit.php?id=' . $ev['id'])) . '">Edit</a>';
    } else {
        $h .= '<p class="meta">' . e($ev['venue']) . '</p>';
        if ($reg) {
            $h .= '<a class="btn btn-outline btn-block" href="' . e(url('tickets/tickets.php?t=' . $reg['id'])) . '">View Ticket</a>';
        } else {
            $h .= '<a class="btn btn-primary btn-block" href="' . e(url('events/register-event.php?id=' . $ev['id'])) . '">Register</a>';
        }
    }
    return $h . '</div>';
}

/** Small inline SVG icons */
function icon(string $name): string
{
    $icons = [
        'bell'   => '<svg viewBox="0 0 24 24" width="16" height="16" fill="#9B2FB8"><path d="M12 22a2.5 2.5 0 0 0 2.4-2h-4.8A2.5 2.5 0 0 0 12 22zm7-6V11a7 7 0 0 0-5-6.7V3a2 2 0 0 0-4 0v1.3A7 7 0 0 0 5 11v5l-2 2v1h18v-1z"/></svg>',
        'check'  => '<svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>',
        'check_g'=> '<svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="#1E8E4E" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>',
        'warn'   => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="#D63B4A" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"><path d="M12 3L2 20h20L12 3z"/><path d="M12 10v4M12 17v.5"/></svg>',
        'logout' => '<svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="#D63B4A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 4H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h4"/><path d="M16 8l4 4-4 4M20 12H9"/></svg>',
        'pencil' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="#000"><path d="M3 17.25V21h3.75L17.8 9.94l-3.75-3.75L3 17.25zM20.7 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>',
    ];
    return $icons[$name] ?? '';
}
