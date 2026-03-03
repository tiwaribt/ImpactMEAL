<?php
// PHP Configuration for Impact MEAL
define('DB_HOST', 'localhost');
define('DB_NAME', 'impact_meal');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// App Settings
define('APP_NAME', 'Impact MEAL');
define('APP_VERSION', '1.0.0');

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session Start
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
