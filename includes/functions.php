<?php
session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax']);
require_once __DIR__ . '/../config/db.php';

const CATEGORIES  = ['Academic', 'Music', 'Sports', 'Arts'];
const YEAR_LEVELS = ['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'];

/* ---------- small helpers ---------- */
function e($value) {                       // escape text before printing (prevents XSS)
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
function redirect($url) {
    header('Location: ' . $url);
    exit;
}
function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}
function csrf_ok() {
    return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}

/* ---------- login checks ---------- */
function current_user($pdo) {
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $st = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $st->execute([$_SESSION['user_id']]);
    $user = $st->fetch();
    return $user ? $user : null;
}
function require_student($pdo) {
    $user = current_user($pdo);
    if (!$user) {
        redirect('login.php');
    }
    if ($user['role'] !== 'student') {
        redirect('faculty.php');
    }
    return $user;
}

/* ---------- formatting ---------- */
function fmt_date($d)     { return date('M j, Y', strtotime($d)); }
function fmt_datetime($d) { return date('M j, Y · g:i A', strtotime($d)); }
function fmt_time($d)     { return date('g:i A', strtotime($d)); }
function initials($user) {
    return strtoupper(mb_substr($user['first_name'], 0, 1, 'UTF-8') . mb_substr($user['last_name'], 0, 1, 'UTF-8'));
}
function first_initial($user) {
    return strtoupper(mb_substr($user['first_name'], 0, 1, 'UTF-8'));
}
function price_text($price) {
    return ((float)$price > 0) ? '₱' . number_format((float)$price, 0) : 'Free';
}

/* ---------- events ---------- */
// Upcoming events. "joined" = 1 when this student already registered.
function get_events($pdo, $userId, $category = '', $search = '', $limit = 0) {
    $sql = "SELECT e.*,
                   (SELECT COUNT(*) FROM registrations r
                     WHERE r.event_id = e.id AND r.user_id = ? AND r.status = 'confirmed') AS joined
            FROM events e
            WHERE e.event_date >= NOW()";
    $params = [$userId];
    if ($category !== '') {
        $sql .= ' AND e.category = ?';
        $params[] = $category;
    }
    if ($search !== '') {
        $sql .= ' AND (e.title LIKE ? OR e.venue LIKE ?)';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }
    $sql .= ' ORDER BY e.event_date ASC';
    if ($limit > 0) {
        $sql .= ' LIMIT ' . (int)$limit;
    }
    $st = $pdo->prepare($sql);
    $st->execute($params);
    return $st->fetchAll();
}
