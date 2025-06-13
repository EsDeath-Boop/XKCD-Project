<?php
echo "<h2>Debug Information</h2>";

// Check verification codes file
$codesFile = __DIR__ . '/verification_codes.txt';
echo "<h3>verification_codes.txt:</h3>";
if (file_exists($codesFile)) {
    echo "<pre>" . htmlspecialchars(file_get_contents($codesFile)) . "</pre>";
} else {
    echo "<p>File does not exist!</p>";
}

// Check email log file  
$logFile = __DIR__ . '/email_log.txt';
echo "<h3>email_log.txt (last few entries):</h3>";
if (file_exists($logFile)) {
    $content = file_get_contents($logFile);
    $lines = explode("\n", $content);
    $lastLines = array_slice($lines, -20); // Last 20 lines
    echo "<pre>" . htmlspecialchars(implode("\n", $lastLines)) . "</pre>";
} else {
    echo "<p>File does not exist!</p>";
}

// Show current time for comparison
echo "<h3>Current Server Time:</h3>";
echo "<p>" . date('Y-m-d H:i:s') . " (timestamp: " . time() . ")</p>";
?>