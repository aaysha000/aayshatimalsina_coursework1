<?php
session_start();
require '../php/db.php';

// Verify if the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "Unauthorized access. Only admins can delete reviews.";
    exit;
}

// Ensure the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if review_id is passed
    if (isset($_POST['review_id']) && !empty($_POST['review_id'])) {
        $reviewId = $_POST['review_id'];

        // Debugging message
        echo "Review ID to delete: " . htmlspecialchars($reviewId) . "<br>";

        // Attempt to delete the review
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = :id");
        $stmt->bindParam(':id', $reviewId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo "Review deleted successfully.";
            // Redirect back to the reviews page
            header('Location: ../html/index.php');
            exit;
        } else {
            echo "Failed to delete the review. Check your database connection or query.";
        }
    } else {
        echo "No review ID provided.";
    }
} else {
    echo "Invalid request method.";
}
?>
