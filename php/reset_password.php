<?php
require 'db.php'; // Include your database connection

// Set timezone to Australia/Sydney
date_default_timezone_set('Australia/Sydney');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if the token is valid and not expired
    $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        // Update the password and clear the token
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?");
        $stmt->execute([$newPassword, $token]);

        // Redirect to the login page
        header("Location: ../php/login.php?message=password_reset_success");
        exit;
    } else {
        echo "Invalid or expired token.";
    }
} elseif (isset($_GET['token'])) {
    $token = $_GET['token'];
} else {
    die("Invalid request.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
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
            margin-bottom: 10px;
            font-size: 2rem;
            color: #4caf50;
            font-weight: bold;
        }

        /* Form styles */
        form {
            margin-top: 10px;
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

        /* Error message */
        .error-message {
            background: rgba(255, 0, 0, 0.1);
            border: 1px solid red;
            border-radius: 5px;
            color: red;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <main class="container">
        <header>
            <h1>Reset Password</h1>
        </header>

        <!-- Display error message -->
        <?php if (!empty($error)): ?>
            <div class="error-message">
                <p><?php echo $error; ?></p>
            </div>
        <?php endif; ?>

        <!-- Reset password form -->
        <?php if (isset($token)): ?>
            <form method="POST" action="">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <label for="password">Enter your new password:</label>
                <input type="password" id="password" name="password" placeholder="Enter your new password" required>
                <button type="submit">Reset Password</button>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>