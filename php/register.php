<?php
require 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    try {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        $existingUser = $stmt->fetch();

        if ($existingUser) {
            // Redirect back with an error if username or email exists
            header('Location: ../php/register.php?error=exists');
            exit;
        }

        // Check if the users table is empty to determine the role (first user becomes admin)
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $userCount = $stmt->fetchColumn();
        $role = ($userCount == 0) ? 'admin' : 'user';

        // Insert the new user
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $email, $passwordHash, $role]);

        // Automatically log the user in after registration
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;

        // Redirect to the home page
        header('Location: ../html/index.php?success=registered');
        exit;
    } catch (PDOException $e) {
        // Redirect back with a general error
        header('Location: ../php/register.php?error=general');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
            background-color:#4CAF50;
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
            color:#4CAF50;
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
    </style>
</head>
<body>
    <main class="container">
        <!-- Display error messages -->
        <?php if (isset($_GET['error'])): ?>
            <div class="error-message">
                <?php if ($_GET['error'] === 'exists'): ?>
                    <p style="color: red;">The username or email already exists. Please try again.</p>
                <?php elseif ($_GET['error'] === 'general'): ?>
                    <p style="color: red;">An error occurred during registration. Please try again later.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Enter your username" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>

            <button type="submit" class="btn btn-success">Register</button>
        </form>
        
        <div class="links">
            <p>Already have an account? <a href="../php/login.php">Login here</a>.</p>
        </div>
    </main>
</body>
</html>
