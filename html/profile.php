<?php
session_start();
require '../php/db.php';

// Helper function to mark the active page
function isActivePage($page) {
    return basename($_SERVER['PHP_SELF']) === $page ? 'active' : '';
}

// Session-based data
$userId = $_SESSION['user_id'] ?? null;
$userRole = $_SESSION['role'] ?? 'guest';
$wishlistCount = 0;
$cartCount = 0;
$userData = null;

if ($userId) {
    // Get user details
    $userQuery = "SELECT username, email FROM users WHERE id = :user_id";
    $userStmt = $pdo->prepare($userQuery);
    $userStmt->execute(['user_id' => $userId]);
    $userData = $userStmt->fetch(PDO::FETCH_ASSOC);

    // Get wishlist count
    $wishlistQuery = "SELECT COUNT(*) FROM user_wishlist WHERE user_id = :user_id";
    $wishlistStmt = $pdo->prepare($wishlistQuery);
    $wishlistStmt->execute(['user_id' => $userId]);
    $wishlistCount = $wishlistStmt->fetchColumn();

    // Get cart count
    $cartQuery = "SELECT SUM(quantity) FROM user_cart WHERE user_id = :user_id";
    $cartStmt = $pdo->prepare($cartQuery);
    $cartStmt->execute(['user_id' => $userId]);
    $cartCount = $cartStmt->fetchColumn();
}
// Default fallback for guest users
if (!$userData) {
    $userData = [
        'username' => 'Guest',
        'email' => 'Not Available',
    ];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account</title>
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
            display: flex;
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .sidebar {
            width: 25%;
            background-color: #e9f7e9;
            padding: 20px;
            border-right: 1px solid #ddd;
            border-radius: 10px 0 0 10px;
        }

        .sidebar h2 {
            font-size: 1.2rem;
            margin-bottom: 15px;
            color: #2c6e49;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin-bottom: 10px;
        }

        .sidebar ul li a {
            text-decoration: none;
            color: #333;
            font-size: 1rem;
            padding: 10px 15px;
            display: block;
            border-radius: 5px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .sidebar ul li a:hover {
            background-color: #2c6e49;
            color: white;
        }

        .content {
            flex: 1;
            padding: 20px;
        }

        .content h2 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: #2c6e49;
        }

        .content section {
            margin-bottom: 30px;
        }

        .content section h3 {
            font-size: 1.2rem;
            margin-bottom: 10px;
        }

        .content section p {
            font-size: 1rem;
            line-height: 1.5;
        }

        .content section a {
            color: #007bff;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .content section a:hover {
            color: #0056b3;
        }
    </style>
</head>

<body>
    <header>
        <h1>My Account</h1>
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
                
                <!-- Display welcome message with username -->
                <span class="welcome-message">Welcome, <?= htmlspecialchars($_SESSION['username']); ?>!</span>

                <!-- Profile dropdown -->
                <div class="profile-dropdown">
                    <div class="icon-container">
                        <img src="../img/profile.png" alt="Profile" class="icon" id="profile-icon" style="background-color:rgb(237, 247, 232);">
                    </div>
                    <div class="dropdown-menu">
                        <a href="../html/profile.php">My Account</a>
                        <a href="../php/logout.php">Logout</a>
                    </div>
                </div>
            <?php else: ?>

                <span class="welcome-message">Welcome Guest!</span>
                <!-- If user is not logged in, show profile icon linking to login -->
                <div class="icon-container">
                    <a href="../php/login.php">
                        <img src="../img/profile.png" alt="Profile" class="icon" style="background-color:rgb(237, 247, 232);">
                    </a>
                </div>
            <?php endif; ?>
            <!-- Wishlist and Cart icons -->
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
    
    <div class="container">
        <div class="sidebar">
            <h2>My Account</h2>
            <ul>
                <li><a href="../html/my_orders.php">My Orders</a></li>
                
                <li><a href="../html/wishlist.php">My Wish List</a></li>
                <li><a href="../html/profile.php#contact-us">Address Book</a></li>
                
                <?php if ($userRole === 'admin'): ?>
                    <li><a href="../html/index.php">Admin Panel</a></li>
                <?php endif; ?>
          
                <li><a href="../php/logout.php">Logout</a></li>
            </ul>
        </div>
        <div class="content">
            <h2>Account Information</h2>
            <section>
                <h3>Contact Information</h3>
                <p>
                    <?= htmlspecialchars($userData['username']); ?><br>
                    <?= htmlspecialchars($userData['email']); ?><br>
                    <a href="#">Edit</a> | <a href="../php/forgot_password.php">Change Password</a>
                </p>
            </section>
            <section>
                <h3>Address Book</h3>
                <p>
                    Default Billing Address<br>
                    You have not set a default billing address.<br>
                    <a href="#">Edit Address</a>
                </p>
                <p>
                    Default Shipping Address<br>
                    You have not set a default shipping address.<br>
                    <a href="#">Edit Address</a>
                </p>
            </section>
        </div>
    </div>

    <footer>
    <div class="footer-container">
        <!-- First row: Information, My Account, and Contact -->
        <div class="footer-row">
            <div class="footer-section" id="information">
                <h4>Information</h4>
                <ul>
                    <li><a href="index.php">About Us</a></li>
                    <li><a href="policy.php">Terms & Conditions</a></li>
                    <li><a href="policy.php">Privacy Policy</a></li>
                </ul>
            </div>
            <div class="footer-section" id="my-account">
                <h4>My Account</h4>
                <ul>
                    <li><a href="../php/login.php">Login</a></li>
                    <li><a href="types_of_plants.php">My Plants</a></li>
                    <li><a href="wishlist.php">My Wishlist</a></li>
                </ul>
            </div>
            <div class="footer-section contact" id="contact-us">
                <h4>Contact Us</h4>
                <p>Phone: +977 9818688098</p>
                <p><a href="mailto:aayshatimalsina@gmail.com">Email us</a></p>
                <p><a href="https://www.google.com/maps?q=Kathmandu,+44600,+Nepal" target="_blank">Kathmandu, 44600, Nepal</a></p>
            </div>
        </div>

        <!-- Second row: About section -->
        <div class="footer-section about" id="about">
            <p>
                Welcome to our Plants Shop! We provide a variety of plants along with materials for gardening. Let's grow together!
            </p>
        </div>

        <!-- Third row: Opening hours -->
        <div class="footer-opening-hours" id="opening-hours">
            <p>Office Hours: 10:00 AM - 4:00 PM, Sun-Fri</p>
        </div>

        <!-- Fourth row: Follow us -->
        <div class="footer-follow" id="follow-us">
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
    document.addEventListener("DOMContentLoaded", () => {
        const profileIcon = document.getElementById("profile-icon");
        const profileDropdown = document.querySelector(".profile-dropdown");

        profileIcon.addEventListener("click", (event) => {
            event.stopPropagation(); // Prevent click event from bubbling
            profileDropdown.classList.toggle("open"); // Toggle dropdown visibility
        });

        // Close the dropdown if clicked outside
        document.addEventListener("click", (event) => {
            if (!profileDropdown.contains(event.target)) {
                profileDropdown.classList.remove("open"); // Close dropdown
            }
        });
    });
</script>

</body>
</html>
