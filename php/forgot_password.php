<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Include PHPMailer via Composer
require 'db.php'; // Include your database connection

// Set timezone to Australia/Sydney
date_default_timezone_set('Asia/Kathmandu');

$error = ''; // Initialize error message
$success = ''; // Initialize success message

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Check if the email exists in the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Generate a unique reset token
        $token = bin2hex(random_bytes(16)); // Generate a token
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour

        // Save token and expiration to database
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
        $stmt->execute([$token, $expires, $email]);

        // Generate the reset link
        $resetLink = "http://localhost/plants_app/php/reset_password.php?token=$token";

        // Send the email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            // SMTP server configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'aayshatimalsina@gmail.com';
            $mail->Password = 'mhhj txup oizn gdqy';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Email details
            $mail->setFrom('aayshatimalsina@gmail.com', 'PHP Mailer');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = "Click the following link to reset your password: <a href=\"$resetLink\">Reset Password</a>";

            $mail->send();

            // Display success message
            $success = "A password reset link has been sent to your email.";
        } catch (Exception $e) {
            $error = "An error occurred while sending the reset link. Please try again later.";
        }
    } else {
        // If the email doesn't exist
        $error = "User doesn't exist.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
        /* Body styles */
        body {
            background: url('../img/back4.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        /* Back to Login button */
        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }

        .back-button:hover {
            background-color: #3a8c40;
        }

        /* Container styles */
        .container {
            background-color: rgba(255, 255, 255, 0.9); /* Semi-transparent white */
            border-radius: 12px;
            padding: 40px 50px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);
            width: 400px; /* Fixed width */
            text-align: center;
            height: 450px; /* Fixed height */
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Header styles */
        header h1 {
            margin-bottom: 10px; /* Reduced spacing below header */
            font-size: 2rem;
            color: #4caf50;
            font-weight: bold;
        }

        /* Form styles */
        form {
            margin-top: -30px; /* Adjusted for better spacing */
            display: flex;
            flex-direction: column;
            gap: 20px;
            font-size: 1.2rem;
        }

        /* Input fields */
        input {
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
            width: 100%;
        }

        /* Button styling */
        button {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1rem;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #3a8c40;
        }

        /* Error and Success messages */
        .message {
            min-height: 40px; /* Reserve space for messages */
            margin-top: -30px; /* Reduced spacing above the message */
            margin-bottom: 40px; /* Adjusted spacing below the message */
            font-size: 0.9rem;
            text-align: left;
            display: flex;
            align-items: center;
            justify-content: center;
            visibility: hidden; /* Hidden by default */
        }

        .error-message {
            background: rgba(255, 0, 0, 0.1);
            border: 1px solid red;
            border-radius: 5px;
            color: red;
            visibility: hidden; /* Hidden by default */
        }

        .success-message {
            background: rgba(0, 255, 0, 0.1);
            border: 1px solid green;
            border-radius: 5px;
            color: green;
            visibility: hidden; /* Hidden by default */
        }

        .message.show {
            visibility: visible; /* Make message visible when added */
        }
    </style>
</head>
<body>
    <!-- Back to Login Button -->
    <a href="login.php" class="back-button">Back to Login</a>

    <main class="container">
        <header>
            <h1>Forgot Password</h1>
        </header>

        <!-- Reserved space for messages -->
        <div class="message error-message <?php echo !empty($error) ? 'show' : ''; ?>">
            <p><?php echo !empty($error) ? $error : ''; ?></p>
        </div>
        <div class="message success-message <?php echo !empty($success) ? 'show' : ''; ?>">
            <p><?php echo !empty($success) ? $success : ''; ?></p>
        </div>

        <!-- Forgot password form -->
        <form method="POST" action="">
            <label for="email">Enter your email address:</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
            <button type="submit">Send Reset Link</button>
        </form>
    </main>
</body>
</html>
