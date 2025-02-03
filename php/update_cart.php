<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$plantId = $data['plantId'] ?? null;
$materialId = $data['materialId'] ?? null;
$action = $data['action'];

if ($action === 'add') {
    if ($plantId) {
        // Check if the plant is already in the cart
        $stmt = $pdo->prepare("SELECT quantity FROM user_cart WHERE user_id = :user_id AND plant_id = :plant_id");
        $stmt->execute(['user_id' => $userId, 'plant_id' => $plantId]);
        $existingItem = $stmt->fetchColumn();

        if ($existingItem) {
            // Increment the quantity if the plant is already in the cart
            $stmt = $pdo->prepare("UPDATE user_cart SET quantity = quantity + 1 WHERE user_id = :user_id AND plant_id = :plant_id");
            $stmt->execute(['user_id' => $userId, 'plant_id' => $plantId]);
        } else {
            // Add the plant to the cart if it does not exist
            $stmt = $pdo->prepare("INSERT INTO user_cart (user_id, plant_id, quantity) VALUES (:user_id, :plant_id, 1)");
            $stmt->execute(['user_id' => $userId, 'plant_id' => $plantId]);
        }
    }

    if ($materialId) {
        // Check if the material is already in the cart
        $stmt = $pdo->prepare("SELECT quantity FROM user_cart WHERE user_id = :user_id AND material_id = :material_id");
        $stmt->execute(['user_id' => $userId, 'material_id' => $materialId]);
        $existingItem = $stmt->fetchColumn();

        if ($existingItem) {
            // Increment the quantity if the material is already in the cart
            $stmt = $pdo->prepare("UPDATE user_cart SET quantity = quantity + 1 WHERE user_id = :user_id AND material_id = :material_id");
            $stmt->execute(['user_id' => $userId, 'material_id' => $materialId]);
        } else {
            // Add the material to the cart if it does not exist
            $stmt = $pdo->prepare("INSERT INTO user_cart (user_id, material_id, quantity) VALUES (:user_id, :material_id, 1)");
            $stmt->execute(['user_id' => $userId, 'material_id' => $materialId]);
        }
    }

    echo json_encode(['success' => true]);
} elseif ($action === 'remove') {
    if ($plantId) {
        $stmt = $pdo->prepare("DELETE FROM user_cart WHERE user_id = :user_id AND plant_id = :plant_id");
        $stmt->execute(['user_id' => $userId, 'plant_id' => $plantId]);
    }

    if ($materialId) {
        $stmt = $pdo->prepare("DELETE FROM user_cart WHERE user_id = :user_id AND material_id = :material_id");
        $stmt->execute(['user_id' => $userId, 'material_id' => $materialId]);
    }

    echo json_encode(['success' => true]);
} elseif ($action === 'update') {
    if ($plantId) {
        $quantity = $data['quantity'];
        $stmt = $pdo->prepare("UPDATE user_cart SET quantity = :quantity WHERE user_id = :user_id AND plant_id = :plant_id");
        $stmt->execute(['user_id' => $userId, 'plant_id' => $plantId, 'quantity' => $quantity]);
    }

    if ($materialId) {
        $quantity = $data['quantity'];
        $stmt = $pdo->prepare("UPDATE user_cart SET quantity = :quantity WHERE user_id = :user_id AND material_id = :material_id");
        $stmt->execute(['user_id' => $userId, 'material_id' => $materialId, 'quantity' => $quantity]);
    }

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
