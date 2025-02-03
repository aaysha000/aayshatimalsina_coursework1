<?php
require 'db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../html/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $image_url = $_POST['image_url'];
    $category = $_POST['category'];

    $stmt = $pdo->prepare("INSERT INTO plants (name, price, image_url, category) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $price, $image_url, $category]);

    header('Location: ../html/types_of_plants.php'); // Redirect to index.php after successful addition
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Plant</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-image: url('../img/soft-background.jpg'); /* Add your background image path */
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            color: #333;
        }

        header {
            background-color: rgba(44, 110, 73, 0.8);
            padding: 20px;
            text-align: center;
            color: #fff;
        }

        main.container {
            background-color: rgba(255, 255, 255, 0.9);
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="number"],
        select {
            width: 100%;
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #4CAF50;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
        }

        button:hover {
            background-color: #367c39;
        }

        .btn-secondary {
            background-color: #6c757d;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            color: #fff;
            font-size: 1rem;
            display: inline-block;
            margin-bottom: 20px;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .btn-success {
            background-color: #38a169;
        }

        .btn-success:hover {
            background-color: #2d6a4f;
        }
    </style>
</head>
<body>
    <header>
        <h1>Add New Plant</h1>
    </header>
    <main class="container">
        <a href="../html/types_of_plants.php" class="btn-secondary">Back to Dashboard</a>
        <form method="POST" action="">
            <label for="name">Plant Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="price">Price (Rs.):</label>
            <input type="number" step="0.01" id="price" name="price" required>

            <label for="image_url">Image URL:</label>
            <input type="text" id="image_url" name="image_url" required>

            <label for="category">Category:</label>
            <select id="category" name="category" required>
                <option value="Indoor">Indoor</option>
                <option value="Outdoor">Outdoor</option>
            </select>

            <button type="submit" class="btn btn-success">Add Plant</button>
        </form>
    </main>
</body>
</html>