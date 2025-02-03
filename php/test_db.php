<?php
require 'db.php';

try {
    $stmt = $pdo->query('SELECT 1');
    echo "Database connection successful!";
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
}
?>
