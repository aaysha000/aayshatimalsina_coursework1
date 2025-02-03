<?php
session_start();
require 'db.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

$userId = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

// Retrieve the IDs sent in the request
$plantId = $data['plantId'] ?? null;
$materialId = $data['materialId'] ?? null;

// Check if at least one ID is provided
if (!$plantId && !$materialId) {
    echo json_encode(['success' => false, 'message' => 'Invalid plant or material ID.']);
    exit;
}

// Determine which type of item is being updated
$idColumn = $plantId ? 'plant_id' : 'material_id';
$idValue = $plantId ?: $materialId;

try {
    // Check if the item is already in the wishlist
    $query = "SELECT * FROM user_wishlist WHERE user_id = :user_id AND $idColumn = :id_value";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['user_id' => $userId, 'id_value' => $idValue]);

    if ($stmt->rowCount() > 0) {
        // Item is in the wishlist, so remove it
        $deleteQuery = "DELETE FROM user_wishlist WHERE user_id = :user_id AND $idColumn = :id_value";
        $deleteStmt = $pdo->prepare($deleteQuery);
        $deleteStmt->execute(['user_id' => $userId, 'id_value' => $idValue]);

        echo json_encode(['success' => true, 'action' => 'removed']);
    } else {
        // Item is not in the wishlist, so add it
        $insertQuery = "INSERT INTO user_wishlist (user_id, $idColumn) VALUES (:user_id, :id_value)";
        $insertStmt = $pdo->prepare($insertQuery);
        $insertStmt->execute(['user_id' => $userId, 'id_value' => $idValue]);

        echo json_encode(['success' => true, 'action' => 'added']);
    }
} catch (Exception $e) {
    error_log("Error updating wishlist: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
