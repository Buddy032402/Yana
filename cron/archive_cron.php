<?php
include "../../db.php";

// Log file for cron execution
$log_file = __DIR__ . '/archive_cron.log';

// Start archiving process
try {
    include "../archive_login_history.php";
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Archive process completed successfully\n", FILE_APPEND);
} catch (Exception $e) {
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n", FILE_APPEND);
}
?>