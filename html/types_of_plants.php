<?php
session_start();
require '../php/db.php';

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
}

// Fetch filters from the query parameters
$category = isset($_GET['category']) && $_GET['category'] !== 'all' ? $_GET['category'] : null;
$sortBy = isset($_GET['sort-by']) ? $_GET['sort-by'] : null;
$search = isset($_GET['search']) ? $_GET['search'] : null;

// Build the base query
$query = "SELECT * FROM plants WHERE 1=1";

// Apply category filter
if ($category) {
    $query .= " AND category = :category";
}

// Apply search filter
if ($search) {
    // Split the search term into words
    $searchWords = explode(' ', $search);
    $searchConditions = [];
    foreach ($searchWords as $index => $word) {
        $searchConditions[] = "name LIKE :searchWord$index";
    }
    $query .= " AND (" . implode(' AND ', $searchConditions) . ")";
}

// Apply sorting
switch ($sortBy) {
    case 'a-z':
        $query .= " ORDER BY name ASC";
        break;
    case 'z-a':
        $query .= " ORDER BY name DESC";
        break;
    case 'low-high':
        $query .= " ORDER BY price ASC";
        break;
    case 'high-low':
        $query .= " ORDER BY price DESC";
        break;
    default:
        $query .= " ORDER BY id ASC";
}

// Prepare and execute the query
$stmt = $pdo->prepare($query);

if ($category) {
    $stmt->bindValue(':category', $category);
}

if ($search) {
    foreach ($searchWords as $index => $word) {
        $stmt->bindValue(":searchWord$index", '%' . $word . '%');
    }
}

$stmt->execute();
$plants = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch the user's wishlist if logged in
$wishlist = [];
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $wishlistQuery = "SELECT plant_id FROM user_wishlist WHERE user_id = :user_id";
    $wishlistStmt = $pdo->prepare($wishlistQuery);
    $wishlistStmt->execute(['user_id' => $userId]);
    $wishlist = $wishlistStmt->fetchAll(PDO::FETCH_COLUMN); // Get plant IDs as an array
}

function isActivePage($page) {
    return basename($_SERVER['PHP_SELF']) === $page ? 'active' : '';
}
?>


<!DOCTYPE html>
<html lang="en">
<script src="../js/script.js"></script>
<head>

    <script>
        const userWishlist = <?= json_encode($wishlist); ?>; // Pass PHP array to JavaScript
    </script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plant Shop</title>
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

        .add-new-plant {
            position: relative;
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .add-new-plant:hover {
            background-color: #367c39;
            transform: scale(1.05);
        }

        /* Additional styling for the Review Submissions button */
        .add-new-plant[href="review_submissions.php"] {
            background-color: #007bff;
        }

        .add-new-plant[href="review_submissions.php"]:hover {
            background-color: #0056b3;
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

        .plant-bubbles-container {
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

        .plant-bubble {
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

        .plant-bubble:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
        }

        .plant-image {
            width: 100%;
            height: 400px;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .plant-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .plant-name {
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

        .plant-price {
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
        
     

        /* Button for submitting a new plant */
        .btn-submit-plant {
             /* Make the button inline while still respecting padding and block-level properties */
            padding: 10px 20px; /* Add padding: 10px vertically and 20px horizontally */
            font-size: 16px; /* Set the font size to 16px for readability */
            color: white; /* Set the text color to white */
            background-color: #007bff; /* Set the background color to blue */
            text-decoration: none; /* Remove underline from the button text */
            border-radius: 8px; /* Add rounded corners with an 8px radius */
            margin-left: 76em; /* Push the button to the far right of the container */
            
            transition: background-color 0.3s ease, transform 0.3s ease; /* Add smooth transitions for hover effects */
        }

        /* Hover effect for the submit button */
        .btn-submit-plant:hover {
            background-color: #0056b3; /* Change the background color to a darker blue when hovered */
            transform: scale(1.05); /* Slightly enlarge the button on hover for a subtle interaction effect */
        }
        header {
            position: relative; /* Makes the header the parent context for the button */
            padding: 20px;
            background-color: #f4f9f4; /* Light background color */
            border-bottom: 2px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Button for submitting a new plant */
        .btn-submit-plant {
            position: absolute; /* Position it absolutely within its container */
            top: 20px; /* Adjust the distance from the top */
            right: 20px; /* Align it to the right */
            padding: 12px 20px; /* Add padding */
            font-size: 16px; /* Set font size */
            color: white; /* Button text color */
            background-color: #4CAF50; /* Button background color */
            text-decoration: none; /* Remove underline from the text */
            border-radius: 8px; /* Rounded corners */
            font-weight: bold; /* Bold text for visibility */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Add subtle shadow */
            transition: background-color 0.3s ease, transform 0.3s ease; /* Smooth hover effects */
        }

        /* Hover effect for the submit button */
        .btn-submit-plant:hover {
            background-color:rgb(57, 130, 59); /* Darker blue when hovered */
            transform: scale(1.05); /* Slightly enlarge the button on hover */
        }

    </style>
</head>
<body>
<header>
        <h1>Plants</h1>
        

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <div style="position: absolute; top: 20px; right: 20px; display: flex; gap: 10px;">
                <a href="../php/add_plant.php" class="add-new-plant">Add New Plant</a>
                <a href="../html/review_submissions.php" class="add-new-plant"> Review Submissions</a>
            </div>
        <?php elseif (isset($_SESSION['role'])): ?>
            <!-- User: Opens the submission modal -->
            <a href="submit_plant.php" class="btn-submit-plant">Submit a New Plant</a>
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

        <!-- Modal for submitting a new plant -->
        <div id="addPlantModal" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close" onclick="toggleModal()">&times;</span>
                <h2>Submit a New Plant</h2>
                <form method="POST" action="" class="form-styled">
                    <div class="form-group">
                        <label for="name">Plant Name:</label>
                        <input type="text" id="name" name="name" placeholder="Enter plant name" required>
                    </div>
                    <div class="form-group">
                        <label for="category">Category:</label>
                        <select id="category" name="category" required>
                            <option value="Indoor">Indoor</option>
                            <option value="Outdoor">Outdoor</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="price">Price (NPR):</label>
                        <input type="number" id="price" name="price" placeholder="Enter price" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="image_url">Image URL:</label>
                        <input type="url" id="image_url" name="image_url" placeholder="Enter image URL" required>
                    </div>
                    <button type="submit" name="submit_plant" class="btn-submit">Submit</button>
                </form>
            </div>
        </div>


        <!-- Filter Container -->
        <form method="GET" action="" class="filter-container">
            <div class="filter-group">
                <label for="category">Category:</label>
                <select id="category" name="category">
                    <option value="all" <?= !isset($_GET['category']) || $_GET['category'] === 'all' ? 'selected' : '' ?>>All</option>
                    <option value="Indoor" <?= isset($_GET['category']) && $_GET['category'] === 'Indoor' ? 'selected' : '' ?>>Indoor</option>
                    <option value="Outdoor" <?= isset($_GET['category']) && $_GET['category'] === 'Outdoor' ? 'selected' : '' ?>>Outdoor</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="sort-by">Sort By:</label>
                <select id="sort-by" name="sort-by">
                    <option value="default" <?= !isset($_GET['sort-by']) || $_GET['sort-by'] === 'default' ? 'selected' : '' ?>>Default</option>
                    <option value="a-z" <?= isset($_GET['sort-by']) && $_GET['sort-by'] === 'a-z' ? 'selected' : '' ?>>A to Z</option>
                    <option value="z-a" <?= isset($_GET['sort-by']) && $_GET['sort-by'] === 'z-a' ? 'selected' : '' ?>>Z to A</option>
                    <option value="low-high" <?= isset($_GET['sort-by']) && $_GET['sort-by'] === 'low-high' ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="high-low" <?= isset($_GET['sort-by']) && $_GET['sort-by'] === 'high-low' ? 'selected' : '' ?>>Price: High to Low</option>
                </select>
            </div>

            <div class="search-group">
                <label for="search">Search:</label>
                <input type="search" id="search" name="search" placeholder="What are you looking for?" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            </div>

            <button type="submit" class="btn btn-primary">Filter</button>
        </form>

        <!-- Plants Display -->
        <div class="plant-bubbles-container">
            <?php if (empty($plants)): ?>
                <div class="no-items-found">No plants found.</div>
            <?php else: ?>
                <?php foreach ($plants as $plant): ?>
                    <div class="plant-bubble" data-plant-id="<?= $plant['id']; ?>">

                        <div class="plant-image">
                            <img src="<?= htmlspecialchars($plant['image_url']); ?>" alt="<?= htmlspecialchars($plant['name']); ?>">
                        </div>
                        <div class="plant-name">
                            <?= htmlspecialchars($plant['name']); ?>
                            <img src="../img/heart.png" alt="Wishlist" class="wishlist-icon <?= in_array($plant['id'],$wishlist)? 'filled':'';?>" />
                        </div>
                        <div class="plant-price">Rs.<?= number_format($plant['price'], 2); ?></div>

                        <button class="add-to-cart" data-plant-id="<?= $plant['id']; ?>">Add to Cart</button>
                        
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <div class="admin-options">
                                <a href="../php/edit_plant.php?id=<?= $plant['id']; ?>" class="btn btn-primary">Edit</a>
                                <a href="../php/delete.php?id=<?= $plant['id']; ?>&type=plants" class="btn btn-danger" onclick="return confirm('Are you sure?');">Delete</a>
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
        function toggleModal() {
            const modal = document.getElementById('addPlantModal');
            modal.style.display = modal.style.display === 'none' ? 'block' : 'none';
        }
    </script>


</body>
</html>