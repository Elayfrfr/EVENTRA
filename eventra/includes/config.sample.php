<?php
/**
 * Copy this file to  includes/config.local.php  and fill in YOUR values.
 * config.local.php is ignored by Git, so your passwords never go to GitHub.
 */

// ---- Database ----
define('DB_HOST', 'localhost');        // Laragon: localhost | InfinityFree: sqlXXX.infinityfree.com
define('DB_NAME', 'eventra');          // InfinityFree: if0_12345678_eventra
define('DB_USER', 'root');             // Laragon: root     | InfinityFree: if0_12345678
define('DB_PASS', '');                 // Laragon: (empty)  | InfinityFree: your vPanel password

// ---- App ----
define('BASE_URL', '');                // '' when the site is in the web root (Laragon auto-domain / InfinityFree htdocs)
define('APP_DEBUG', true);             // true on Laragon, false on InfinityFree
define('SHOW_RESET_LINK', true);     // InfinityFree blocks mail(): true shows the reset link on screen (demo). Set false once real email works
define('MAIL_FROM', 'no-reply@eventra.local');
