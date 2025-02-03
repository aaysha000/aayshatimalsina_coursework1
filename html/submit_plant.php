<?php
session_start();
require '../php/db.php';

$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_plant'])) {
    $name = htmlspecialchars($_POST['name'] ?? '');
    $category = htmlspecialchars($_POST['category'] ?? '');
    $price = htmlspecialchars($_POST['price'] ?? '');
    $image_url = htmlspecialchars($_POST['image_url'] ?? '');
    $submittedBy = $_SESSION['user_id'] ?? null;

    // Check for empty fields
    if (empty($name) || empty($category) || empty($price) || empty($image_url)) {
        $message = "Please fill in all fields before submitting.";
    } elseif (!$submittedBy) {
        $message = "You must be logged in to submit a plant.";
    } else {
        // Insert into pending_plants table
        $insertQuery = "INSERT INTO pending_plants (name, category, price, image_url, submitted_by) 
                        VALUES (:name, :category, :price, :image_url, :submitted_by)";
        $stmt = $pdo->prepare($insertQuery);

        try {
            $stmt->execute([
                'name' => $name,
                'category' => $category,
                'price' => $price,
                'image_url' => $image_url,
                'submitted_by' => $submittedBy,
            ]);
            $message = "Your submission has been received and is pending admin approval.";
        } catch (PDOException $e) {
            $message = "Error submitting the plant: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit New Plant</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        /* Page Layout */
        body {
            background-color: #f4f9f4;
            font-family: 'Arial', sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            font-size: 2rem;
            color: #2c6e49;
            text-align: center;
            margin-bottom: 20px;
        }

        .message {
            text-align: center;
            color: red;
            font-weight: bold;
            margin-bottom: 20px;
            opacity: 1;
            transition: opacity 0.5s ease-in-out;
        }

        .form-styled {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .form-group label {
            font-weight: bold;
            color: #333;
        }

        .form-group input,
        .form-group select {
            padding: 12px;
            font-size: 16px;
            border-radius: 8px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #2c6e49;
            box-shadow: 0 0 8px rgba(44, 110, 73, 0.2);
            outline: none;
        }

        /* Buttons */
        .btn-submit {
            padding: 14px;
            font-size: 16px;
            color: white;
            background-color: #4CAF50;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .btn-submit:hover {
            background-color: #367c39;
            transform: scale(1.05);
        }

        .btn-submit.hidden {
            display: none;
        }

        .btn-back {
            display: inline-block;
            margin-bottom: 20px;
            padding: 12px;
            font-size: 16px;
            color: white;
            background-color: #007bff;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .btn-back:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                margin: 20px;
                padding: 20px;
            }

            h2 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="types_of_plants.php" class="btn-back">Back to Plants</a>
        <h2>Submit a New Plant</h2>
        
        <?php if (!empty($message)): ?>
            <p class="message" id="message"><?= $message; ?></p>
        <?php endif; ?>

        <form method="POST" action="" class="form-styled" id="plantForm">
            <div class="form-group">
                <label for="name">Plant Name:</label>
                <input type="text" id="name" name="name" placeholder="Enter plant name" required>
            </div>
            <div class="form-group">
                <label for="category">Category:</label>
                <select id="category" name="category" required>
                    <option value="">-- Select Category --</option>
                    <option value="Indoor">Indoor</option>
                    <option value="Outdoor">Outdoor</option>
                </select>
            </div>
            <div class="form-group">
                <label for="price">Price (NPR):</label>
                <input type="number" id="price" name="price" placeholder="Enter price" min="1" required>
            </div>
            <div class="form-group">
                <label for="image_url">Image URL:</label>
                <input type="url" id="image_url" name="image_url" placeholder="Enter image URL" required>
            </div>
            <button type="submit" name="submit_plant" class="btn-submit" id="submitButton">Submit</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const message = document.getElementById('message');
            const submitButton = document.getElementById('submitButton');
            const form = document.getElementById('plantForm');

            if (message && message.textContent.trim() !== '') {
                // Hide the message after 5 seconds
                setTimeout(() => {
                    message.style.opacity = '0';
                    setTimeout(() => {
                        message.remove();
                    }, 500); // Allow transition to complete before removal
                }, 2000);
            }
        });
    </script>
</body>
</html>
