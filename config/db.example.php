<?php
// Copy this file to config/db.php and fill in your own values.
// config/db.php is listed in .gitignore, so passwords are never pushed to GitHub.

$host = 'localhost';   // Laragon: localhost | InfinityFree: e.g. sql123.infinityfree.com
$db   = 'eventra';     // Laragon: eventra   | InfinityFree: e.g. if0_12345678_eventra
$user = 'root';        // Laragon: root      | InfinityFree: e.g. if0_12345678
$pass = 'ptngnamopareh67!';            // Laragon: (empty)   | InfinityFree: your hosting account password

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    exit('Database connection failed. Check config/db.php');
}
