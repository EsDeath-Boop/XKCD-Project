<?php
// Production Configuration
define('SMTP_FROM', 'no-reply@gmail.com'); // Change to your domain email
define('SMTP_FROM_NAME', 'XKCD Daily Comics');
define('DOMAIN', 'http://localhost/xkcd-project/src'); // Update to your actual domain
define('ENABLE_REAL_EMAIL', true); // Set to true for production

// Store verification codes temporarily (in a real app, use database or session)
function generateVerificationCode() {
    // Generate and return a 6-digit numeric code
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

function sendUnsubscribeConfirmationEmail($email, $code) {
    // This calls your existing sendUnsubscribeEmail function
    return sendUnsubscribeEmail($email, $code);
}

function registerEmail($email) {
    $file = __DIR__ . '/registered_emails.txt';
    // Save verified email to registered_emails.txt
    if (!file_exists($file)) {
        touch($file);
    }
    
    // Check if email already exists
    $emails = file_get_contents($file);
    if (strpos($emails, $email) === false) {
        file_put_contents($file, $email . "\n", FILE_APPEND | LOCK_EX);
    }
}

function unsubscribeEmail($email) {
    $file = __DIR__ . '/registered_emails.txt';
    // Remove email from registered_emails.txt
    if (!file_exists($file)) {
        return;
    }
    
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $filteredEmails = array_filter($emails, function($registeredEmail) use ($email) {
        return trim($registeredEmail) !== trim($email);
    });
    
    file_put_contents($file, implode("\n", $filteredEmails) . "\n");
}

function sendVerificationEmail($email, $code) {
    // Send an email containing the verification code
    $subject = "Your Verification Code";
    $body = "<p>Your verification code is: <strong>$code</strong></p>";
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM . ">" . "\r\n";
    
    $success = false;
    
    // Production: Send real emails
    if (ENABLE_REAL_EMAIL) {
        $success = mail($email, $subject, $body, $headers);
        
        // Log success/failure for debugging
        $logFile = __DIR__ . '/email_log.txt';
        $status = $success ? 'SUCCESS' : 'FAILED';
        $logMessage = "[" . date('Y-m-d H:i:s') . "] [$status] Verification Email to: $email | Code: $code\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    } else {
        // Testing: Log the email instead of sending
        $logFile = __DIR__ . '/email_log.txt';
        $logMessage = "[" . date('Y-m-d H:i:s') . "] [TEST] Verification Email\n";
        $logMessage .= "To: $email\n";
        $logMessage .= "Subject: $subject\n";
        $logMessage .= "Code: $code\n";
        $logMessage .= "Body: $body\n\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
        $success = true; // Simulate success in test mode
    }
    
    return $success;
}

function sendUnsubscribeEmail($email, $code) {
    // Send unsubscribe confirmation email
    $subject = "Confirm Un-subscription";
    $body = "<p>To confirm un-subscription, use this code: <strong>$code</strong></p>";
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM . ">" . "\r\n";
    
    $success = false;
    
    // Production: Send real emails
    if (ENABLE_REAL_EMAIL) {
        $success = mail($email, $subject, $body, $headers);
        
        // Log success/failure for debugging
        $logFile = __DIR__ . '/email_log.txt';
        $status = $success ? 'SUCCESS' : 'FAILED';
        $logMessage = "[" . date('Y-m-d H:i:s') . "] [$status] Unsubscribe Email to: $email | Code: $code\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    } else {
        // Testing: Log the email instead of sending
        $logFile = __DIR__ . '/email_log.txt';
        $logMessage = "[" . date('Y-m-d H:i:s') . "] [TEST] Unsubscribe Email\n";
        $logMessage .= "To: $email\n";
        $logMessage .= "Subject: $subject\n";
        $logMessage .= "Code: $code\n";
        $logMessage .= "Body: $body\n\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
        $success = true; // Simulate success in test mode
    }
    
    return $success;
}

function verifyCode($email, $code) {
    // Check if the provided code matches the sent one
    // FIXED: Now properly handles the 4-part format: email|code|type|timestamp
    $codesFile = __DIR__ . '/verification_codes.txt';
    
    if (!file_exists($codesFile)) {
        return false;
    }
    
    $lines = file($codesFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $parts = explode('|', $line);
        // FIXED: Check for 4 parts and verify email and code match
        if (count($parts) >= 4 && $parts[0] === $email && $parts[1] === $code) {
            // Optional: Check if code is still valid (within 10 minutes)
            $timestamp = intval($parts[3]);
            $currentTime = time();
            if (($currentTime - $timestamp) > 600) { // 10 minutes = 600 seconds
                // Code expired, remove it and return false
                $remainingLines = array_filter($lines, function($l) use ($line) {
                    return $l !== $line;
                });
                file_put_contents($codesFile, implode("\n", $remainingLines) . "\n");
                return false;
            }
            
            // Remove the used code
            $remainingLines = array_filter($lines, function($l) use ($line) {
                return $l !== $line;
            });
            file_put_contents($codesFile, implode("\n", $remainingLines) . "\n");
            return true;
        }
    }
    return false;
}

function storeVerificationCode($email, $code, $type = 'register') {
    // Store verification code temporarily
    $codesFile = __DIR__ . '/verification_codes.txt';
    $data = $email . '|' . $code . '|' . $type . '|' . time() . "\n";
    file_put_contents($codesFile, $data, FILE_APPEND | LOCK_EX);
}

function fetchAndFormatXKCDData() {
    // Fetch random XKCD comic data and format as HTML
    $randomId = rand(1, 2800); // XKCD has around 2800+ comics
    $url = "https://xkcd.com/$randomId/info.0.json";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => 'XKCD-Email-Bot/1.0'
        ]
    ]);
    
    $json = file_get_contents($url, false, $context);
    if ($json === false) {
        return false;
    }
    
    $data = json_decode($json, true);
    if (!$data) {
        return false;
    }
    
    // Create unsubscribe link with proper domain
    $unsubscribeUrl = DOMAIN . '/unsubscribe.php';
    
    $html = "<h2>XKCD Comic</h2>\n";
    $html .= "<img src=\"{$data['img']}\" alt=\"XKCD Comic\">\n";
    $html .= "<p><a href=\"$unsubscribeUrl\" id=\"unsubscribe-button\">Unsubscribe</a></p>";
    
    return $html;
}

function sendXKCDUpdatesToSubscribers() {
    $file = __DIR__ . '/registered_emails.txt';
    // Send formatted XKCD data to all registered emails
    
    if (!file_exists($file)) {
        return;
    }
    
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (empty($emails)) {
        return;
    }
    
    $xkcdContent = fetchAndFormatXKCDData();
    if ($xkcdContent === false) {
        error_log("Failed to fetch XKCD data");
        return;
    }
    
    $subject = "Your XKCD Comic";
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM . ">" . "\r\n";
    
    $successCount = 0;
    $failureCount = 0;
    
    // Production: Send real emails
    if (ENABLE_REAL_EMAIL) {
        foreach ($emails as $email) {
            $email = trim($email);
            if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $success = mail($email, $subject, $xkcdContent, $headers);
                if ($success) {
                    $successCount++;
                } else {
                    $failureCount++;
                }
            }
        }
        
        // Log production results
        $logFile = __DIR__ . '/cron.log';
        $logMessage = "[" . date('Y-m-d H:i:s') . "] XKCD Comics sent: $successCount success, $failureCount failed\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    } else {
        // Testing: Log the emails instead of sending
        $logFile = __DIR__ . '/email_log.txt';
        $logMessage = "[" . date('Y-m-d H:i:s') . "] [TEST] XKCD Comic Email\n";
        $logMessage .= "Subject: $subject\n";
        $logMessage .= "Recipients: " . implode(', ', $emails) . "\n";
        $logMessage .= "Content: $xkcdContent\n\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
        
        // Log cron execution
        $cronLogFile = __DIR__ . '/cron.log';
        $cronLogMessage = "[" . date('Y-m-d H:i:s') . "] [TEST] XKCD Comics logged for " . count($emails) . " subscribers\n";
        file_put_contents($cronLogFile, $cronLogMessage, FILE_APPEND | LOCK_EX);
    }
}

// Helper function to clean up expired verification codes
function cleanupExpiredCodes() {
    $codesFile = __DIR__ . '/verification_codes.txt';
    
    if (!file_exists($codesFile)) {
        return;
    }
    
    $lines = file($codesFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $validLines = [];
    $currentTime = time();
    
    foreach ($lines as $line) {
        $parts = explode('|', $line);
        if (count($parts) >= 4) {
            $timestamp = intval($parts[3]);
            // Keep codes that are less than 10 minutes old
            if (($currentTime - $timestamp) <= 600) {
                $validLines[] = $line;
            }
        }
    }
    
    file_put_contents($codesFile, implode("\n", $validLines) . "\n");
}
?>