<?php
require_once 'functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = '';
$step = 'email'; // email or verify

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email']) && !isset($_POST['verification_code'])) {
        // Step 1: Email submission
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        if ($email) {
            $code = generateVerificationCode();
            storeVerificationCode($email, $code, 'register');
            if (sendVerificationEmail($email, $code)) {
                $message = "Verification code sent to your email!";
                $step = 'verify';
                $_SESSION['pending_email'] = $email;
            } else {
                $message = "Failed to send email. Please try again.";
            }
        } else {
            $message = "Please enter a valid email address.";
        }
    } elseif (isset($_POST['verification_code'])) {
        // Step 2: Code verification
        $email = $_SESSION['pending_email'] ?? '';
        $code = $_POST['verification_code'];
        
        if (verifyCode($email, $code)) {
            registerEmail($email);
            $message = "Successfully registered for XKCD updates!";
            unset($_SESSION['pending_email']);
            $step = 'email';
        } else {
            $message = "Invalid verification code. Please try again.";
            $step = 'verify';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XKCD Email Subscription</title>
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
            background-color: #007cba;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background-color: #005a8a;
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
        <h1>XKCD Daily Comics</h1>
        <p>Subscribe to receive a random XKCD comic every day!</p>
        
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'Failed') !== false || strpos($message, 'Invalid') !== false ? 'error' : ''; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Email Form - Always visible -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <button type="submit" id="submit-email">Subscribe</button>
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
            <a href="unsubscribe.php">Unsubscribe</a>
        </div>
    </div>
</body>
</html>