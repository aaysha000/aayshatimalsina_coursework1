<?php
require 'db.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT * FROM plants");
    $plants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($plants);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
