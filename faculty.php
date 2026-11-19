<?php
// Temporary page. The Faculty side is a separate Trello card ("UI for Faculty").
require_once __DIR__ . '/includes/functions.php';

$user = current_user($pdo);
if (!$user) {
    redirect('login.php');
}
if ($user['role'] === 'student') {
    redirect('dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Faculty | Eventra</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="plain">
<main class="terms card">
  <h1>Hello, <?= e($user['first_name']) ?></h1>
  <p>The Faculty side is coming soon.</p>
  <p><a class="btn btn-primary" href="logout.php">Log Out</a></p>
</main>
</body>
</html>
