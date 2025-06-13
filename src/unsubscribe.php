<?php
require_once 'functions.php';

$message = '';
$step = 'email'; // email or verify

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['unsubscribe_email']) && !isset($_POST['verification_code'])) {
        // Step 1: Email submission for unsubscribe
        $email = filter_var($_POST['unsubscribe_email'], FILTER_VALIDATE_EMAIL);
        if ($email) {
            $code = generateVerificationCode();
            storeVerificationCode($email, $code, 'unsubscribe');
            if (sendUnsubscribeEmail($email, $code)) {
                $message = "Unsubscribe verification code sent to your email!";
                $step = 'verify';
                $_SESSION['pending_unsubscribe_email'] = $email;
            } else {
                $message = "Failed to send email. Please try again.";
            }
        } else {
            $message = "Please enter a valid email address.";
        }
    } elseif (isset($_POST['verification_code'])) {
        // Step 2: Code verification for unsubscribe
        session_start();
        $email = $_SESSION['pending_unsubscribe_email'] ?? '';
        $code = $_POST['verification_code'];
        
        if (verifyCode($email, $code)) {
            unsubscribeEmail($email);
            $message = "Successfully unsubscribed from XKCD updates!";
            unset($_SESSION['pending_unsubscribe_email']);
            $step = 'email';
        } else {
            $message = "Invalid verification code. Please try again.";
            $step = 'verify';
        }
    }
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe - XKCD Email Subscription</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="email"], input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button {
            background-color: #dc3545;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background-color: #c82333;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .links {
            text-align: center;
            margin-top: 20px;
        }
        .links a {
            color: #007cba;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Unsubscribe from XKCD Comics</h1>
        <p>Enter your email address to unsubscribe from daily XKCD comics.</p>
        
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'Failed') !== false || strpos($message, 'Invalid') !== false ? 'error' : ''; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Unsubscribe Email Form - Always visible -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="unsubscribe_email">Email Address:</label>
                <input type="email" name="unsubscribe_email" id="unsubscribe_email" required>
            </div>
            <button type="submit" id="submit-unsubscribe">Unsubscribe</button>
        </form>

        <!-- Verification Form - Always visible -->
        <form method="POST" action="" style="margin-top: 30px;">
            <div class="form-group">
                <label for="verification_code">Verification Code:</label>
                <input type="text" name="verification_code" id="verification_code" maxlength="6" required>
            </div>
            <button type="submit" id="submit-verification">Verify</button>
        </form>

        <div class="links">
            <a href="index.php">Back to subscription page</a>
        </div>
    </div>
</body>
</html>