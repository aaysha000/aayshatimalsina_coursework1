<?php
session_start();

// Ensure the session ID exists
if (!isset($_GET['session_id'])) {
    header('Location: index.php');
    exit;
}

// Include the database connection
require '../php/db.php';

// Check if the user is logged in
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header('Location: ../php/login.php');
    exit;
}

// Fetch items from the cart
$cartItemsQuery = $pdo->prepare("
    SELECT plant_id, material_id, quantity 
    FROM user_cart 
    WHERE user_id = :user_id
");
$cartItemsQuery->execute(['user_id' => $userId]);
$cartItems = $cartItemsQuery->fetchAll(PDO::FETCH_ASSOC);

// Save each cart item as an order
foreach ($cartItems as $item) {
    $plantId = $item['plant_id'] ?? null;
    $materialId = $item['material_id'] ?? null;
    $quantity = $item['quantity'];

    if (!empty($plantId)) {
        // It's a plant order
        $priceQuery = $pdo->prepare("SELECT price FROM plants WHERE id = :plant_id");
        $priceQuery->execute(['plant_id' => $plantId]);
        $price = $priceQuery->fetchColumn();

        if ($price !== false) { // Ensure the price is found
            $totalPrice = $price * $quantity;

            $insertOrderQuery = $pdo->prepare("
                INSERT INTO orders (user_id, plant_id, material_id, quantity, total_price) 
                VALUES (:user_id, :plant_id, NULL, :quantity, :total_price)
            ");
            $insertOrderQuery->execute([
                'user_id' => $userId,
                'plant_id' => $plantId,
                'quantity' => $quantity,
                'total_price' => $totalPrice
            ]);
        } else {
            error_log("Plant with ID $plantId not found in the database.");
        }
    } elseif (!empty($materialId)) {
        // It's a material order
        $priceQuery = $pdo->prepare("SELECT price FROM materials WHERE id = :material_id");
        $priceQuery->execute(['material_id' => $materialId]);
        $price = $priceQuery->fetchColumn();

        if ($price !== false) { // Ensure the price is found
            $totalPrice = $price * $quantity;

            $insertOrderQuery = $pdo->prepare("
                INSERT INTO orders (user_id, plant_id, material_id, quantity, total_price) 
                VALUES (:user_id, NULL, :material_id, :quantity, :total_price)
            ");
            $insertOrderQuery->execute([
                'user_id' => $userId,
                'material_id' => $materialId,
                'quantity' => $quantity,
                'total_price' => $totalPrice
            ]);
        } else {
            error_log("Material with ID $materialId not found in the database.");
        }
    } else {
        // Handle cases where both plant_id and material_id are empty
        error_log("Invalid cart item detected for user_id: $userId");
        throw new Exception("Cart item must have either a plant_id or material_id.");
    }
}


// Clear the user's cart
$clearCartQuery = $pdo->prepare("DELETE FROM user_cart WHERE user_id = :user_id");
$clearCartQuery->execute(['user_id' => $userId]);

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $review = trim($_POST['review']);
    $rating = $_POST['rating'];

    if (!empty($review) && !empty($rating)) {
        $stmt = $pdo->prepare("INSERT INTO reviews (user_id, review, rating, created_at) VALUES (:user_id, :review, :rating, NOW())");
        $stmt->execute([
            'user_id' => $userId,
            'review' => $review,
            'rating' => $rating
        ]);
        $message = "Thank you for your review!";
    } else {
        $message = "Please provide both a review and a rating.";
    }
}

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Payment Successful</title>
    
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        h1 {
            width: 100%;
            padding: 1.5rem;
            text-align: center;
            color: #4CAF50;
        }
        main {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 500px;
        }
        p {
            font-size: 1.2rem;
            margin: 1rem 0;
        }
        .btn {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            color: white;
            background-color: #4CAF50;
            text-decoration: none;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #45A049;
        }
        form {
            margin-top: 2rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }
        select {
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }
        .message {
            color: #4CAF50;
            margin-top: 1rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <main>
        <p>Your payment was successful. Your plants will be shipped soon.</p>
        <h1>Thank You for Your Purchase!</h1>
        <a href='index.php' class='btn'>Back to Home</a>

        <h2>Leave a Review</h2>
        <form method='POST'>
            <textarea name='review' placeholder='Write your review here...' rows='4' required></textarea>
            <select name='rating' required>
                <option value=''>Rate your experience</option>
                <option value='1'>1 - Poor</option>
                <option value='2'>2 - Fair</option>
                <option value='3'>3 - Good</option>
                <option value='4'>4 - Very Good</option>
                <option value='5'>5 - Excellent</option>
            </select>
            <button type='submit' class='btn'>Submit Review</button>
        </form>
        " . (!empty($message) ? "<div class='message'>$message</div>" : "") . "
    </main>
    
</body>
</html>";
?>
