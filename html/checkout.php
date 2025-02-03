<?php
require '../vendor/autoload.php'; // Autoload Stripe library
require '../php/db.php';
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Stripe API key
\Stripe\Stripe::setApiKey('sk_test_51QiyEdP8V5evD96cEjC04vkEM9ww7sRCSBceLHadQAN21Q4NtTG85uxyvy9Mxb07kaRvvV5HZrhbEGAQFGJee4ZM00J20bWeKi');

// Fetch cart items for plants
$userId = $_SESSION['user_id'];
$queryPlants = "SELECT plants.*, user_cart.quantity FROM plants
                INNER JOIN user_cart ON plants.id = user_cart.plant_id
                WHERE user_cart.user_id = :user_id";
$stmtPlants = $pdo->prepare($queryPlants);
$stmtPlants->execute(['user_id' => $userId]);
$cartPlants = $stmtPlants->fetchAll(PDO::FETCH_ASSOC);

// Fetch cart items for materials
$queryMaterials = "SELECT materials.*, user_cart.quantity FROM materials
                   INNER JOIN user_cart ON materials.id = user_cart.material_id
                   WHERE user_cart.user_id = :user_id";
$stmtMaterials = $pdo->prepare($queryMaterials);
$stmtMaterials->execute(['user_id' => $userId]);
$cartMaterials = $stmtMaterials->fetchAll(PDO::FETCH_ASSOC);

// Combine plants and materials into one cart array
$cartItems = array_merge($cartPlants, $cartMaterials);

// Check if cart is empty
if (empty($cartItems)) {
    echo 'Your cart is empty. Please add items to proceed.';
    exit;
}

// Initialize Stripe line items array
$lineItems = [];

// Process cart items
foreach ($cartItems as $item) {
    // Ensure price is numeric and calculate in paisa
    $priceInPaisa = $item['price'] * 100;

    $lineItems[] = [
        'price_data' => [
            'currency' => 'npr', // Set currency to NPR
            'product_data' => [
                'name' => htmlspecialchars($item['name']),
                'images' => [$item['image_url']],
            ],
            'unit_amount' => (int)$priceInPaisa, // Amount in paisa
        ],
        'quantity' => $item['quantity'],
    ];
}

// Create Stripe session
try {
    $checkout_session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => $lineItems,
        'mode' => 'payment',
        'success_url' => 'http://localhost/plants_app/html/success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'http://localhost/plants_app/html/cancel.php',
    ]);
} catch (\Exception $e) {
    error_log('Stripe Checkout Error: ' . $e->getMessage());
    echo 'An error occurred while initiating payment. Please try again later.';
    exit;
}

// Redirect to Stripe Checkout
header('Location: ' . $checkout_session->url);
exit;
