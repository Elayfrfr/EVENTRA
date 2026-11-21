<?php
date_default_timezone_set('Asia/Manila');

define('APP_NAME', 'Eventra');
define('SUPPORT_EMAIL', 'supporteventra@gmail.com');
define('PASSWORD_HINT', 'At least 15 characters, including a number, uppercase, and lowercase');

$local = __DIR__ . '/config.local.php';
if (!file_exists($local)) {
    die('Missing includes/config.local.php. Copy includes/config.sample.php to includes/config.local.php and edit it.');
}
require $local;
