<?php
session_start();
require '../php/db.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../php/login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch user's orders
$ordersQuery = $pdo->prepare("
    SELECT 
        o.id, 
        COALESCE(p.name, m.name) AS item_name, 
        o.quantity, 
        o.total_price, 
        o.order_date,
        CASE 
            WHEN o.plant_id IS NOT NULL THEN 'Plant'
            WHEN o.material_id IS NOT NULL THEN 'Material'
            ELSE 'Unknown'
        END AS item_type
    FROM orders o
    LEFT JOIN plants p ON o.plant_id = p.id
    LEFT JOIN materials m ON o.material_id = m.id
    WHERE o.user_id = :user_id
    ORDER BY o.order_date DESC
");
$ordersQuery->execute(['user_id' => $userId]);
$orders = $ordersQuery->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f9f4;
            color: #333;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #2c6e49;
            padding: 15px;
            color: white;
            text-align: center;
        }

        h1 {
            margin: 0;
            font-size: 2rem;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        #my-orders {
            margin-top: 20px;
        }

        #my-orders table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        #my-orders th, #my-orders td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        #my-orders th {
            background-color: #2c6e49;
            color: white;
        }

        #my-orders tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        #my-orders tr:hover {
            background-color: #f1f1f1;
        }

        #my-orders p {
            font-size: 1.2rem;
            text-align: center;
        }

        .btn-back {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            font-size: 1rem;
            color: white;
            background-color: #2c6e49;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .btn-back:hover {
            background-color: #1e4c34;
        }
    </style>
</head>
<body>
    <header>
        <h1>My Orders</h1>
    </header>

    <div class="container">
        <a href="profile.php" class="btn-back">Back to My Account</a>
        <section id="my-orders">
            <h2>My Orders</h2>
            <?php if (count($orders) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Item Name</th>
                            <th>Item Type</th>
                            <th>Quantity</th>
                            <th>Total Price (NPR)</th>
                            <th>Order Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['id']); ?></td>
                                <td><?= htmlspecialchars($order['item_name']); ?></td>
                                <td><?= htmlspecialchars($order['item_type']); ?></td>
                                <td><?= htmlspecialchars($order['quantity']); ?></td>
                                <td><?= number_format($order['total_price'], 2); ?></td>
                                <td><?= htmlspecialchars($order['order_date']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>You have not placed any orders yet.</p>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>
