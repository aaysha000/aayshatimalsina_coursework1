<?php
session_start();
require '../php/db.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../php/login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch cart items for plants
$query = "SELECT plants.*, user_cart.quantity FROM plants
          INNER JOIN user_cart ON plants.id = user_cart.plant_id
          WHERE user_cart.user_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->execute(['user_id' => $userId]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch cart items for materials
$materialQuery = "SELECT materials.*, user_cart.quantity FROM materials
                  INNER JOIN user_cart ON materials.id = user_cart.material_id
                  WHERE user_cart.user_id = :user_id";
$materialStmt = $pdo->prepare($materialQuery);
$materialStmt->execute(['user_id' => $userId]);
$materialCartItems = $materialStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .cart-container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 10px;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
        }

        .cart-item-details {
            flex: 1;
            margin-left: 20px;
        }

        .cart-item-name {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
        }

        .cart-item-price {
            font-size: 1rem;
            color: #555;
            margin: 10px 0;
        }

        .cart-item-quantity {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cart-item-quantity input {
            width: 50px;
            text-align: center;
        }

        .cart-remove-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
        }

        .cart-remove-btn:hover {
            background-color: #c82333;
        }

        .cart-total {
            text-align: right;
            margin-top: 20px;
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }

        .checkout-btn {
            background-color: #4CAF50;
            color: white;
            padding: 15px 20px;
            border: none;
            border-radius: 5px;
            font-size: 1.2rem;
            cursor: pointer;
            display: block;
            text-align: center;
            margin: 20px auto;
            max-width: 300px;
        }

        .checkout-btn:hover {
            background-color: #367c39;
        }

        .empty-cart-message {
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            color: #555;
            padding: 50px 20px;
        }

        .add-more-container {
            text-align: left; /* Center the button */
            margin-top: 20px; /* Add spacing above the button */
        }

        .add-more-btn {
            background-color: #4CAF50; /* Green background */
            color: white; /* White text */
            padding: 12px 20px; /* Padding around the button */
            text-decoration: none; /* Remove underline */
            font-size: 1rem; /* Font size */
            border-radius: 10px; /* Rounded corners */
            transition: background-color 0.3s ease; /* Smooth hover effect */
            display: inline-block; /* Make it behave like a button */
        }

        .add-more-btn:hover {
            background-color: #367c39; /* Darker green on hover */
        }

        .back-home-container {
            margin-top: -40px ; /* Add space above the button */
            margin-left: 200px;
        }

        .back-home-btn {
            background-color: #4CAF50; /* Green background */
            color: white; /* White text */
            padding: 12px 20px; /* Padding around the button */
            text-decoration: none; /* Remove underline */
            font-size: 1rem; /* Font size */
            border-radius: 10px; /* Rounded corners */
            transition: background-color 0.3s ease; /* Smooth hover effect */
            display: inline-block; /* Make it behave like a button */
        }

        .back-home-btn:hover {
            background-color: #367c39; /* Darker green on hover */
        }


    </style>
</head>
<body>
    <header>
        <h1>My Cart</h1>
    </header>
    
    <main>
        <div class="cart-container">
            <?php if (empty($cartItems) && empty($materialCartItems)): ?>
                <div class="empty-cart-message">
                    Your cart is empty!
                </div>
            <?php else: ?>
                <?php
                $total = 0;

                // Display plants in the cart
                foreach ($cartItems as $item):
                    $subtotal = $item['quantity'] * $item['price'];
                    $total += $subtotal;
                ?>
                <div class="cart-item" data-plant-id="<?= $item['id']; ?>">
                    <img src="<?= htmlspecialchars($item['image_url']); ?>" alt="<?= htmlspecialchars($item['name']); ?>">
                    <div class="cart-item-details">
                        <div class="cart-item-name"><?= htmlspecialchars($item['name']); ?></div>
                        <div class="cart-item-price">Rs.<?= number_format($item['price'], 2); ?> x <?= $item['quantity']; ?></div>
                        <div class="cart-item-quantity">
                            <input type="number" min="1" value="<?= $item['quantity']; ?>" class="quantity-input">
                            <button class="cart-remove-btn">Remove</button>
                        </div>
                    </div>
                </div>

                <?php endforeach; ?>
                <!-- Display materials in the cart -->
                <?php foreach ($materialCartItems as $item): 
                    $subtotal = $item['quantity'] * $item['price'];
                    $total += $subtotal;
                ?>
                <div class="cart-item" data-material-id="<?= $item['id']; ?>">
                    <img src="<?= htmlspecialchars($item['image_url']); ?>" alt="<?= htmlspecialchars($item['name']); ?>">
                    <div class="cart-item-details">
                        <div class="cart-item-name"><?= htmlspecialchars($item['name']); ?> (Material)</div>
                        <div class="cart-item-price">Rs.<?= number_format($item['price'], 2); ?> x <?= $item['quantity']; ?></div>
                        <div class="cart-item-quantity">
                            <input type="number" min="1" value="<?= $item['quantity']; ?>" class="quantity-input">
                            <button class="cart-remove-btn">Remove</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <div class="add-more-container">
                    <a href="types_of_plants.php" class="add-more-btn">Add More Plants</a>
                    <a href="garden_care.php" class="add-more-btn">Add More Materials</a>
                </div>

                <div class="cart-total">Total: Rs.<?= number_format($total, 2); ?></div>
                <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
            <?php endif; ?>
        </div>

        <!-- Back Home Button -->
        <div class="back-home-container">
            <a href="index.php" class="back-home-btn">Back Home</a>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const removeButtons = document.querySelectorAll('.cart-remove-btn');
            const quantityInputs = document.querySelectorAll('.quantity-input');
            const cartContainer = document.querySelector('.cart-container');

            removeButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    const cartItem = e.target.closest('.cart-item');
                    const plantId = cartItem.getAttribute('data-plant-id');
                    const materialId = cartItem.getAttribute('data-material-id');

                    fetch('../php/update_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ 
                            plantId: plantId || null, 
                            materialId: materialId || null, 
                            action: 'remove' 
                        }),
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            cartItem.remove();
                            if (cartContainer.querySelectorAll('.cart-item').length === 0) {
                                cartContainer.innerHTML = '<div class="empty-cart-message">Your cart is empty!</div>';
                            }
                        }
                    });
                });
            });

            quantityInputs.forEach(input => {
                input.addEventListener('change', (e) => {
                    const cartItem = e.target.closest('.cart-item');
                    const plantId = cartItem.getAttribute('data-plant-id');
                    const materialId = cartItem.getAttribute('data-material-id');
                    const quantity = e.target.value;

                    fetch('../php/update_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ 
                            plantId: plantId || null, 
                            materialId: materialId || null, 
                            action: 'update', 
                            quantity 
                        }),
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>





