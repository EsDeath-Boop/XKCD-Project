<?php
// test_email.php - Place this in your src/ directory to test email functionality

require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_email'])) {
    $testEmail = $_POST['test_email'];
    $testCode = generateVerificationCode();
    
    echo "<h2>Testing Email Functionality</h2>";
    
    // Test verification email
    if (sendVerificationEmail($testEmail, $testCode)) {
        echo "<p style='color: green;'>✅ Verification email sent successfully to: " . htmlspecialchars($testEmail) . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to send verification email</p>";
    }
    
    // Test XKCD fetch
    $xkcdData = fetchAndFormatXKCDData();
    if ($xkcdData) {
        echo "<p style='color: green;'>✅ XKCD data fetched successfully</p>";
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
        echo "<h3>Sample XKCD Content:</h3>";
        echo $xkcdData;
        echo "</div>";
    } else {
        echo "<p style='color: red;'>❌ Failed to fetch XKCD data</p>";
    }
    
    echo "<p><a href='test_email.php'>Test Again</a></p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Email Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        input, button { padding: 10px; margin: 5px; }
        button { background: #007cba; color: white; border: none; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Test Email Functionality</h1>
    <form method="POST">
        <label>Enter your email to test:</label><br>
        <input type="email" name="test_email" required style="width: 300px;">
        <button type="submit">Send Test Email</button>
    </form>
    
    <h2>Configuration Check:</h2>
    <p><strong>SMTP From:</strong> <?php echo SMTP_FROM; ?></p>
    <p><strong>Domain:</strong> <?php echo DOMAIN; ?></p>
    <p><strong>Real Email Enabled:</strong> <?php echo ENABLE_REAL_EMAIL ? 'Yes' : 'No'; ?></p>
</body>
</html>