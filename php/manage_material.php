<?php
require 'db.php';
session_start();


// Check user role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die('Access Denied: Admins Only.');
}

$id = $_GET['id'] ?? null; // Check if editing
$isEdit = $id !== null;

if ($id && !is_numeric($id)) {
    die('Invalid ID provided.');
}

// If editing, fetch the material data
if ($isEdit) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM materials WHERE id = ?");
        $stmt->execute([$id]);
        $material = $stmt->fetch();

        if (!$material) {
            die('Material not found.');
        }

        echo '<pre>';
        print_r($material);
        echo '</pre>';
    } catch (PDOException $e) {
        die('Error fetching material: ' . $e->getMessage());
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $image_url = $_POST['image_url'];

    try {
        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE materials SET name = ?, price = ?, image_url = ? WHERE id = ?");
            $stmt->execute([$name, $price, $image_url, $id]);
            $message = 'Material updated successfully!';
        } else {
            $stmt = $pdo->prepare("INSERT INTO materials (name, price, image_url) VALUES (?, ?, ?)");
            $stmt->execute([$name, $price, $image_url]);
            $message = 'Material added successfully!';
        }

        header('Location: ../html/garden_care.php?message=' . urlencode($message));
        exit;
    } catch (PDOException $e) {
        die('Error saving material: ' . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Edit' : 'Add'; ?> Garden Material</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px 40px;
            border-radius: 10px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color:rgb(255, 255, 255);
        }

        form label {
            display: block;
            margin: 15px 0 5px;
            font-weight: bold;
            font-size: 16px;
        }

        form input[type="text"],
        form input[type="number"] {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        form input[type="number"] {
            -moz-appearance: textfield;
        }

        form button {
            background-color: #4CAF50;
            color: #fff;
            font-size: 16px;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
            width: 100%;
        }

        form button:hover {
            background-color: #367c39;
        }

        a.btn-secondary {
            display: inline-block;
            text-decoration: none;
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            text-align: center;
            margin-bottom: 20px;
        }

        a.btn-secondary:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <header>
        <h1>Add New Material</h1>
    </header>


    <main class="container">
        
        <a href="../html/garden_care.php" class="btn-secondary">Back to Garden Care</a>
        <form method="POST" action="">
            <label for="name">Material Name:</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($material['name'] ?? ''); ?>" required>

            <label for="price">Price (NPR):</label>
            <input type="number" step="0.01" id="price" name="price" value="<?= htmlspecialchars($material['price'] ?? ''); ?>" required>

            <label for="image_url">Image URL:</label>
            <input type="text" id="image_url" name="image_url" value="<?= htmlspecialchars($material['image_url'] ?? ''); ?>" required>

            <button type="submit"><?= $isEdit ? 'Update' : 'Add'; ?> Material</button>
        </form>
    </main>
</body>
</html>