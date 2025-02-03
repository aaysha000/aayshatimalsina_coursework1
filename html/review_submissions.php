<?php
session_start();
require '../php/db.php';

// Ensure only admins can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../html/index.php');
    exit;
}

$message = ''; // Message to show after an action

// Fetch pending submissions
$query = "SELECT * FROM pending_plants WHERE approved = 0";
$stmt = $pdo->query($query);
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $plantId = $_POST['plant_id'];

    if ($action === 'approve') {
        // Move to plants table
        $approveQuery = "INSERT INTO plants (name, category, price, image_url)
                         SELECT name, category, price, image_url
                         FROM pending_plants WHERE id = :id";
        $approveStmt = $pdo->prepare($approveQuery);
        $approveStmt->execute(['id' => $plantId]);
    
        // Get the user who submitted the plant
        $userQuery = "SELECT submitted_by FROM pending_plants WHERE id = :id";
        $userStmt = $pdo->prepare($userQuery);
        $userStmt->execute(['id' => $plantId]);
        $submittedBy = $userStmt->fetchColumn();
    
        // Mark as approved
        $updateQuery = "UPDATE pending_plants SET approved = 1, approved_by = :admin_id, approved_at = NOW() WHERE id = :id";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute(['id' => $plantId, 'admin_id' => $_SESSION['user_id']]);
    
        // Notify the user if submitted_by exists
        if ($submittedBy) {
            $notificationQuery = "INSERT INTO notifications (user_id, message, viewed) VALUES (:user_id, :message, 0)";
            $notificationStmt = $pdo->prepare($notificationQuery);
            $notificationStmt->execute([
                'user_id' => $submittedBy,
                'message' => "Your plant has been approved and added to the shop!"
            ]);
        }
    
        $message = "Plant has been added to the shop.";
    }

    if ($action === 'reject') {
        // Remove the plant from pending submissions
        $rejectQuery = "DELETE FROM pending_plants WHERE id = :id";
        $rejectStmt = $pdo->prepare($rejectQuery);
        $rejectStmt->execute(['id' => $plantId]);
    
        // Notify the user if submitted_by exists
        $userQuery = "SELECT submitted_by FROM pending_plants WHERE id = :id";
        $userStmt = $pdo->prepare($userQuery);
        $userStmt->execute(['id' => $plantId]);
        $submittedBy = $userStmt->fetchColumn();
    
        if ($submittedBy) {
            $notificationQuery = "INSERT INTO notifications (user_id, message, viewed) VALUES (:user_id, :message, 0)";
            $notificationStmt = $pdo->prepare($notificationQuery);
            $notificationStmt->execute([
                'user_id' => $submittedBy,
                'message' => "Your plant submission was rejected."
            ]);
        }
    
        $message = "Plant submission has been rejected.";

    }

    if ($action === 'edit') {
        // Fetch existing plant data
        $name = $_POST['name'];
        $category = $_POST['category'];
        $price = $_POST['price'];
        $image_url = $_POST['image_url'];
    
        // Update the plant details in the `pending_plants` table
        $editQuery = "UPDATE pending_plants 
                      SET name = :name, category = :category, price = :price, image_url = :image_url 
                      WHERE id = :id";
        $editStmt = $pdo->prepare($editQuery);
        $editStmt->execute([
            'id' => $plantId,
            'name' => $name,
            'category' => $category,
            'price' => $price,
            'image_url' => $image_url
        ]);
    
        $message = "Plant has been updated.";
    
    }
    
    
    // Refresh page to show updated list
    header("Location: review_submissions.php?message=" . urlencode($message));
    exit;
}

// Get message from the URL if set
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Submissions</title>
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

        .btn-back {
            display: inline-block;
            margin-bottom: 20px;
            padding: 12px;
            font-size: 16px;
            color: white;
            background-color: #007bff;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .btn-back:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        table th {
            background-color: #2c6e49;
            color: white;
            font-size: 1rem;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        img {
            max-width: 100px;
            border-radius: 8px;
        }

        .btn {
            padding: 10px 15px;
            font-size: 14px;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-approve {
            background-color: #4CAF50;
        }

        .btn-approve:hover {
            background-color: #367c39;
        }

        .btn-reject {
            background-color: #dc3545;
        }

        .btn-reject:hover {
            background-color: #b52d38;
        }

        .btn-edit {
            background-color: #ffc107;
        }

        .btn-edit:hover {
            background-color: #e0a800;
        }

        .message {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #4caf50;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 1rem;
            display: none;
            z-index: 1000;
        }

        .message.error {
            background-color: #dc3545;
        }

        #editModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        #editModalContent {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            width: 400px;
            max-width: 90%;
            font-family: 'Arial', sans-serif;
        }

    </style>
</head>
<body>
    <header>
        <h1>Review Submissions</h1>
    </header>
    <div class="container">
        <a href="types_of_plants.php" class="btn-back">Back to Plants</a>
        <?php if (empty($submissions)): ?>
            <div class="no-submissions" style="text-align: center; font-size: 1.2rem;">No submissions to review.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price (NPR)</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $submission): ?>
                        <tr>
                            <td><?= htmlspecialchars($submission['name']); ?></td>
                            <td><?= htmlspecialchars($submission['category']); ?></td>
                            <td><?= number_format($submission['price'], 2); ?></td>
                            <td>
                                <img src="<?= htmlspecialchars($submission['image_url']); ?>" alt="<?= htmlspecialchars($submission['name']); ?>">
                            </td>
                            <td>
                                <form method="POST" style="display: inline-block;">
                                    <input type="hidden" name="plant_id" value="<?= $submission['id']; ?>">
                                    <button type="submit" name="action" value="approve" class="btn btn-approve">Approve</button>
                                </form>
                                <form method="POST" style="display: inline-block;">
                                    <input type="hidden" name="plant_id" value="<?= $submission['id']; ?>">
                                    <button type="submit" name="action" value="reject" class="btn btn-reject">Reject</button>
                                </form>
                                <button type="button" class="btn btn-edit" onclick="openEditModal(<?= $submission['id']; ?>, '<?= htmlspecialchars($submission['name']); ?>', '<?= htmlspecialchars($submission['category']); ?>', <?= $submission['price']; ?>, '<?= htmlspecialchars($submission['image_url']); ?>')">Edit</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div id="actionMessage" class="message <?= isset($_GET['message']) && strpos($_GET['message'], 'rejected') !== false ? 'error' : '' ?>">
        <?= $message ?>
    </div>

    <div id="editModal">
        <div id="editModalContent">
            <h2 style="text-align: center; margin-bottom: 20px; font-size: 1.5rem; color: #2c6e49;">Edit Plant</h2>
            <form method="POST" id="editForm" style="display: flex; flex-direction: column; gap: 15px;">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="plant_id" id="editPlantId">

                <label style="font-weight: bold; font-size: 1rem;">Name:</label>
                <input type="text" name="name" id="editName" required style="padding: 10px; font-size: 1rem; border: 1px solid #ddd; border-radius: 5px;">

                <label style="font-weight: bold; font-size: 1rem;">Category:</label>
                <input type="text" name="category" id="editCategory" required style="padding: 10px; font-size: 1rem; border: 1px solid #ddd; border-radius: 5px;">

                <label style="font-weight: bold; font-size: 1rem;">Price (NPR):</label>
                <input type="number" step="0.01" name="price" id="editPrice" required style="padding: 10px; font-size: 1rem; border: 1px solid #ddd; border-radius: 5px;">

                <label style="font-weight: bold; font-size: 1rem;">Image URL:</label>
                <input type="text" name="image_url" id="editImageUrl" required style="padding: 10px; font-size: 1rem; border: 1px solid #ddd; border-radius: 5px;">

                <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                    <button type="submit" class="btn btn-approve" style="padding: 10px 20px; font-size: 1rem; border-radius: 5px; background-color: #4CAF50; color: white; border: none; cursor: pointer; transition: background-color 0.3s ease;">Save Changes</button>
                    <button type="button" class="btn btn-reject" onclick="closeEditModal()" style="padding: 10px 20px; font-size: 1rem; border-radius: 5px; background-color: #dc3545; color: white; border: none; cursor: pointer; transition: background-color 0.3s ease;">Cancel</button>
                </div>
            </form>
        </div>
    </div>


    <script>
        function openEditModal(id, name, category, price, imageUrl) {
            document.getElementById('editPlantId').value = id;
            document.getElementById('editName').value = name;
            document.getElementById('editCategory').value = category;
            document.getElementById('editPrice').value = price;
            document.getElementById('editImageUrl').value = imageUrl;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        const messageElement = document.getElementById('actionMessage');
        if (messageElement.textContent.trim() !== '') {
            messageElement.style.display = 'block';
            setTimeout(() => {
                messageElement.style.display = 'none';
                if (<?= json_encode(empty($submissions)) ?>) {
                    document.querySelector('.no-submissions').style.display = 'block';
                }
            }, 3000);
        }
    </script>
</body>
</html>
