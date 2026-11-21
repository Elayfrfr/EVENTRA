<?php
require __DIR__ . '/includes/bootstrap.php';
if (is_post()) {
    csrf_check();
    $_SESSION = [];
    session_destroy();
}
redirect('landing.php');
