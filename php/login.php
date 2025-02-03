<?php
require 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Redirect to intended page or homepage
        $redirectTo = $_SESSION['redirect_to'] ?? '../html/index.php';
        unset($_SESSION['redirect_to']);
        header("Location: $redirectTo");
        exit;
    } else {
        $_SESSION['error'] = 'Invalid username or password.';
        header('Location: ../php/login.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <style> 
        /* Reset styling */
        body, h1, p, a, label, input, button {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        /* Body styles */
        body {
            background: url('../img/back4.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        /* Center the container */
        .container {
            background-color: rgb(237, 247, 232);
            border-radius: 12px;
            padding: 20px 30px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            text-align: center;
        }

        /* Form styling */
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        /* Input fields */
        input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
            width: 100%;
        }

        /* Button styling */
        button.btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        button.btn:hover {
            background-color: #285e61;
        }

        /* Links */
        a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }

        /* Error message */
        .error-message {
            background: rgba(255, 0, 0, 0.1);
            border: 1px solid red;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
            text-align: left;
            color: red;
            font-size: 0.9rem;
        }

        /* Footer links */
        .links {
            margin-top: 15px;
        }

        .links p {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <main class="container">
        <!-- Display error message -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message">
                <p style="color: red;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Enter your username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>

            <button type="submit" class="btn">Login</button>
        </form>
        
        <div class="links">
            <p><a href="../php/forgot_password.php">Forgot your password?</a></p>
            <p>Don't have an account? <a href="../php/register.php">Register here</a>.</p>
        </div>
    </main>
</body>
</html>