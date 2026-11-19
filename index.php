<?php
require_once __DIR__ . '/includes/functions.php';

$user = current_user($pdo);
if ($user) {
    redirect($user['role'] === 'student' ? 'dashboard.php' : 'faculty.php');
}
redirect('login.php');
