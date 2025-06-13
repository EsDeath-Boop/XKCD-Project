<?php
// cron.php - Executes daily to send XKCD comics
require_once 'functions.php';

// Clean up expired verification codes first
cleanupExpiredCodes();

// Log the CRON job execution
$logFile = __DIR__ . '/cron.log';
$logMessage = "[" . date('Y-m-d H:i:s') . "] CRON job started\n";
file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);

try {
    // Send XKCD updates to all subscribers
    sendXKCDUpdatesToSubscribers();
    
    $logMessage = "[" . date('Y-m-d H:i:s') . "] CRON job completed successfully\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    
    echo "XKCD comics sent successfully\n";
} catch (Exception $e) {
    $logMessage = "[" . date('Y-m-d H:i:s') . "] CRON job failed: " . $e->getMessage() . "\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    
    echo "Error: " . $e->getMessage() . "\n";
}
?>