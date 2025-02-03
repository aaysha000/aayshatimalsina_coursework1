<?php
session_start();
require '../php/db.php';

// Fetch filters from the query parameters
$search = isset($_GET['search']) ? $_GET['search'] : null;

// Build the base query for garden materials
$query = "SELECT * FROM materials WHERE 1=1";

// Apply search filter
if ($search) {
    // Break the search term into words
    $searchWords = explode(' ', $search);
    $searchConditions = [];
    foreach ($searchWords as $index => $word) {
        $searchConditions[] = "name LIKE :searchWord$index";
    }
    $query .= " AND (" . implode(' AND ', $searchConditions) . ")";
}

// Prepare and execute the query
$stmt = $pdo->prepare($query);

if ($search) {
    foreach ($searchWords as $index => $word) {
        $stmt->bindValue(":searchWord$index", '%' . $word . '%');
    }
}

$stmt->execute();
$materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

// User session details
$userId = $_SESSION['user_id'] ?? null;
$wishlistCount = 0;
$cartCount = 0;

if ($userId) {
    // Get the count of wishlist items
    $wishlistQuery = "SELECT COUNT(*) FROM user_wishlist WHERE user_id = :user_id";
    $wishlistStmt = $pdo->prepare($wishlistQuery);
    $wishlistStmt->execute(['user_id' => $userId]);
    $wishlistCount = $wishlistStmt->fetchColumn();

    // Get the count of cart items
    $cartQuery = "SELECT SUM(quantity) FROM user_cart WHERE user_id = :user_id";
    $cartStmt = $pdo->prepare($cartQuery);
    $cartStmt->execute(['user_id' => $userId]);
    $cartCount = $cartStmt->fetchColumn();

    // Fetch the user's wishlist for materials
    $wishlistQuery = "SELECT material_id FROM user_wishlist WHERE user_id = :user_id AND material_id IS NOT NULL";
    $wishlistStmt = $pdo->prepare($wishlistQuery);
    $wishlistStmt->execute(['user_id' => $userId]);
    $wishlist = $wishlistStmt->fetchAll(PDO::FETCH_COLUMN); // Get material IDs as an array
} else {
    $wishlist = [];
}

// Helper function to mark active navigation
function isActivePage($page)
{
    return basename($_SERVER['PHP_SELF']) === $page ? 'active' : '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Garden Care</title>
    <script src="../js/script.js"></script>

    <script>
        const userWishlist = <?= json_encode($wishlist); ?>; // Pass PHP array to JavaScript
    </script>
    <link rel="stylesheet" href="../css/styles.css">

    <style>
        .admin-options {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
        }

        .btn {
            margin: 5px;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 0.9rem;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-primary {
            background-color: #007bff;
            color: #fff;
        }
        
        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-danger {
            background-color: #dc3545;
            color: #fff;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .add-new-material {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .add-new-material:hover {
            background-color: #367c39;
        }

        .filter-container {
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 20px;
            margin: 20px auto;
            background-color: #f4f4f4;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            max-width: 1200px;
            gap: 20px;
        }

        .filter-group,
        .search-group {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
        }

        .filter-group label,
        .search-group label {
            font-weight: bold;
            font-size: 14px;
            color: #333;
        }

        select,
        input[type="search"] {
            padding: 10px;
            font-size: 14px;
            border-radius: 5px;
            border: 1px solid #ccc;
            width: 200px;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: border-color 0.3s ease;
        }

        select:focus,
        input[type="search"]:focus {
            border-color: #2c6e49;
            outline: none;
        }

        input[type="search"] {
            width: 300px;
        }

        @media (max-width: 768px) {
            .filter-container {
                flex-direction: column;
                gap: 20px;
            }

            select,
            input[type="search"] {
                width: 100%;
            }
        }

        .material-bubbles-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
            padding: 40px;
            max-width: 1400px;
            margin: 0 auto;
            background-color: rgb(237, 247, 232);
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        .material-bubble {
            background-color: rgb(207, 219, 202);
            border: 2px solid #2c6e49;
            border-radius: 20px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .material-bubble:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
        }

        .material-image {
            width: 100%;
            height: 400px;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .material-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .material-name {
            font-size: 1.5rem;
            color: #ff5722;
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px; /* Space between the text and the icon */
        }

        .wishlist-icon {
            width: 24px; /* Set the width of the heart icon */
            height: 24px; /* Set the height of the heart icon */
            cursor: pointer; /* Change the cursor to a pointer */
            transition: transform 0.3s ease, filter 0.3s ease; /* Smooth transition for hover and toggle */
            filter: grayscale(1); /* Default state: grayscale (outline effect) */
            display: inline-block; /* Ensure the icon aligns properly */
            vertical-align: middle; /* Align the icon vertically with text */
        }

        .wishlist-icon.filled {
            filter: none; /* Remove grayscale for the filled heart */
            transform: scale(1.1); /* Slight scale-up for effect when toggled */
        }

        .material-price {
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 15px;
        }

        .add-to-cart {
            background-color: #4CAF50;
            color: #fff;
            border: none;
            padding: 12px 20px;
            border-radius: 10px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 1rem;
            margin: 15px auto;
            display: block;
        }

        .add-to-cart:hover {
            background-color: #367c39;
        }
        
        .no-items-found {
            text-align: center;
            font-size: 1.5rem; /* Larger font size */
            font-weight: bold; /* Bold text */
            color: #555; /* Neutral text color */
            background-color: rgb(157, 248, 121); /* Light green background */
            border: 1px solid #ddd; /* Subtle border */
            border-radius: 10px; /* Rounded corners */
            padding: 20px; /* Padding around the text */
            margin: 50px auto; /* Center horizontally */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Subtle shadow */
            max-width: 600px; /* Limit width */
            grid-column: span 3; /* Make it span across all columns */
        }
    </style>
</head>
<body>
    <header>
        <h1>Garden Materials</h1>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="../php/manage_material.php" class="add-new-material">Add New Material</a>
        <?php endif; ?>
    </header>

    
    <nav class="navbar">
        <img src="../img/logo_extracted.png" alt="Logo" style="background-color:rgb(237, 247, 232);">
        <ul>
            <li><a href="index.php" class="<?= isActivePage('index.php'); ?>">Home</a></li>
            <li><a href="types_of_plants.php" class="<?= isActivePage('types_of_plants.php'); ?>">Plants</a></li>
            <li><a href="garden_care.php" class="<?= isActivePage('garden_care.php'); ?>">Materials</a></li>
        </ul>

        <div class="auth-links">
            <?php if (isset($_SESSION['role'])): ?>
                <span class="welcome-message">Welcome, <?= htmlspecialchars($_SESSION['username']); ?>!</span>
                <div class="profile-dropdown">
                    <div class="icon-container">
                        <img src="../img/profile.png" alt="Profile" class="icon" id="profile-icon" style="background-color:rgb(237, 247, 232);">
                    </div>
                    <div class="dropdown-menu">
                        <a href="profile.php">My Account</a>
                        <a href="../php/logout.php">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <span class="welcome-message">Welcome Guest!</span>
                <div class="icon-container">
                    <a href="../php/login.php">
                        <img src="../img/profile.png" alt="Profile" class="icon" style="background-color:rgb(237, 247, 232);">
                    </a>
                </div>
            <?php endif; ?>
            <div class="icon-container">
                <a href="wishlist.php" class="wishlist-link">
                    <img src="../img/heart.png" alt="Wishlist" class="icon" style="background-color:rgb(237, 247, 232);">
                    <span class="badge wishlist-badge"><?= $wishlistCount ?? 0; ?></span>
                </a>
            </div>
            <div class="icon-container">
                <a href="cart.php" class="cart-link">
                    <img src="../img/cart.png" alt="Cart" class="icon" style="background-color:rgb(237, 247, 232);">
                    <span class="badge cart-badge"><?= $cartCount ?? 0; ?></span>
                </a>
            </div>
        </div>
    </nav>
    

    <main>
        <form method="GET" action="" class="filter-container">
            <div class="search-group">
                <label for="search">Search:</label>
                <input type="search" id="search" name="search" placeholder="What are you looking for?" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <div class="material-bubbles-container">
            <?php if (empty($materials)): ?>
                <div class="no-items-found">No materials found.</div>
            <?php else: ?>
                <?php foreach ($materials as $material): ?>
                    <div class="material-bubble" data-material-id="<?= $material['id']; ?>">
                        <div class="material-image">
                            <img src="<?= htmlspecialchars($material['image_url']); ?>" alt="<?= htmlspecialchars($material['name']); ?>">
                        </div>
                        <div class="material-name">
                            <?= htmlspecialchars($material['name']); ?>
                            <img src="../img/heart.png" alt="Wishlist" class="wishlist-icon <?= in_array($material['id'], $wishlist) ? 'filled' : ''; ?>" />
                        </div>
                        <div class="material-price">Rs.<?= number_format($material['price'], 2); ?></div>
                        <button class="add-to-cart" data-material-id="<?= $material['id']; ?>">Add to Cart</button>
                        
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <div class="admin-options">
                                <a href="../php/manage_material.php?id=<?= $material['id']; ?>" class="btn btn-primary">Edit</a>
                                <a href="../php/delete.php?id=<?= $material['id']; ?>&type=materials" class="btn btn-danger" onclick="return confirm('Are you sure?');">Delete</a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <footer>
    <div class="footer-container">
            <!-- First row: Information, My Account, and Contact -->
            <div class="footer-row">
                <div class="footer-section">
                    <h4>Information</h4>
                    <ul>
                        <li><a href="index.php">About Us</a></li>
                        <li><a href="policy.php">Terms & Conditions</a></li>
                        <li><a href="policy.php">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>My Account</h4>
                    <ul>
                        <li><a href="../php/login.php">Login</a></li>
                        <li><a href="types_of_plants.php">My Plants</a></li>
                        <li><a href="wishlist.php">My Wishlist</a></li>
                    </ul>
                </div>
                <div class="footer-section contact">
                    <h4>Contact Us</h4>
                    <p>Phone: +977 9818688098</p>
                    <p><a href="mailto:aayshatimalsina@gmail.com">Email us</a></p>
                    <p><a href="https://www.google.com/maps?q=Kathmandu,+44600,+Nepal" target="_blank">Kathmandu, 44600, Nepal</a></p>
                </div>
            </div>

            <!-- Second row: About section -->
            <div class="footer-section about">
                <p>
                    Welcome to our Plants Shop! We provide a variety of plants along with materilas for gardening. Let's grow together!
                </p>
            </div>

            <!-- Third row: Opening hours -->
            <div class="footer-opening-hours">
                <p>Office Hours: 10:00 AM - 4:00 PM, Sun-Fri</p>
            </div>

            <!-- Fourth row: Follow us -->
            <div class="footer-follow">
                <h4>Follow Us</h4>
                <div class="social-icons">
                    <a href="https://www.facebook.com" target="_blank"><img src="../img/book.jpg" alt="Facebook"></a>
                    <a href="https://www.instagram.com" target="_blank"><img src="../img/insta.jpg" alt="Instagram"></a>
                    <a href="https://www.twitter.com" target="_blank"><img src="../img/x.png" alt="Twitter"></a>
                </div>
            </div>
        </div>

        <!-- Footer bottom -->
        <div class="footer-bottom">
            <p>&copy; 2025 Plants Shop | Grow with us 🌱</p>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
    const addToCartButtons = document.querySelectorAll('.add-to-cart');

    addToCartButtons.forEach(button => {
        button.addEventListener('click', () => {
            const itemId = button.closest('.wishlist-item')?.getAttribute('data-item-id') || button.getAttribute('data-material-id');
            const itemType = button.closest('.wishlist-item')?.getAttribute('data-item-type') || 'material'; // Default to material for garden care
      

            // Assign plantId or materialId based on the item type
            if (itemType === 'plant') {
                payload.plantId = itemId;
            } else if (itemType === 'material') {
                payload.materialId = itemId;
            }

            fetch('../php/update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload),
            })
                .then(response => response.json())
                
                .catch(error => {
                    console.error('Error adding to cart:', error);
                });
        });
    });
});

</script>


</body>
</html>
