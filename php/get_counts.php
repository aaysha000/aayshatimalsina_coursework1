<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    // Count wishlist items
    $wishlistQuery = "SELECT COUNT(*) FROM user_wishlist WHERE user_id = :user_id";
    $wishlistStmt = $pdo->prepare($wishlistQuery);
    $wishlistStmt->execute(['user_id' => $userId]);
    $wishlistCount = $wishlistStmt->fetchColumn();

    // Count cart items
    $cartQuery = "SELECT SUM(quantity) FROM user_cart WHERE user_id = :user_id";
    $cartStmt = $pdo->prepare($cartQuery);
    $cartStmt->execute(['user_id' => $userId]);
    $cartCount = $cartStmt->fetchColumn() ?: 0;

    echo json_encode([
        'success' => true,
        'wishlistCount' => $wishlistCount,
        'cartCount' => $cartCount,
    ]);
} catch (Exception $e) {
    error_log("Error fetching counts: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to fetch counts']);
}
?>
