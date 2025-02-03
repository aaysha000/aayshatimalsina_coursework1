<?php
require 'db.php';
session_start();

// Ensure the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../html/index.php');
    exit;
}

// Fetch and validate parameters
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING);

if (!$id || !in_array($type, ['plants', 'materials'])) {
    error_log('Invalid request: ' . print_r($_GET, true)); // Log debug information
    die('Invalid request. Please provide both a valid ID and type.');
}

// Determine the table and redirection page
$table = ($type === 'plants') ? 'plants' : 'materials';
$redirectPage = ($type === 'plants') ? 'types_of_plants.php' : 'garden_care.php';

try {
    // Perform the deletion
    $stmt = $pdo->prepare("DELETE FROM `$table` WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $message = ucfirst($type) . ' deleted successfully!';
    header('Location: ../html/' . $redirectPage . '?message=' . urlencode($message));
    exit;
} catch (PDOException $e) {
    error_log('Deletion Error: ' . $e->getMessage()); // Log the error
    die('Error occurred while deleting the record.');
}
?>
