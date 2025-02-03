<?php
session_start();
require '../php/db.php';

function isActivePage($page) {
    return basename($_SERVER['PHP_SELF']) === $page ? 'active' : '';
}
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions & Privacy Policy</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #f9f9f9;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .container {
            width: 90%;
            max-width: 800px;
            margin: 2rem auto;
            background: white;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h2 {
            color: #4CAF50;
        }
        p {
            margin: 1rem 0;
        }
        ul {
            list-style-type: disc;
            margin-left: 2rem;
        }
    </style>
</head>
<body>

    <header>
        <h1>Terms and Conditions & Privacy Policy</h1>
    </header>

    <nav class="navbar">
        <img src="../img/logo_extracted.png" alt="Logo" style="background-color:rgb(237, 247, 232);">
        <ul>
            <li><a href="index.php" class="<?= isActivePage('index.php'); ?>">Home</a></li>
            <li><a href="types_of_plants.php" class="<?= isActivePage('types_of_plants.php'); ?>">Plants</a></li>
            <li><a href="#garden_care.php" class="<?= isActivePage('#garden_care.php'); ?>">Materials</a></li>
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
                        <a href="profile.php">My Account</a>
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
        <section>
            <h2>Terms and Conditions</h2>
            <p>Welcome to our application! By accessing or using our services, you agree to comply with the following terms and conditions:</p>
            <ul>
                <li>You must use our platform responsibly and for lawful purposes only.</li>
                <li>Any unauthorized attempts to access restricted areas or tamper with functionality may result in account termination.</li>
                <li>We are not liable for any loss or damages arising from the use of our platform.</li>
                <li>All information provided must be accurate, and you are responsible for maintaining the confidentiality of your login credentials.</li>
            </ul>
            <p>These terms may be updated at any time. Continued use of the platform constitutes acceptance of these updates.</p>
        </section>

        <section>
            <h2>Privacy Policy</h2>
            <p>Your privacy is important to us. This policy outlines how we collect, use, and protect your personal information:</p>
            <ul>
                <li>We collect only necessary information for account creation and platform functionality.</li>
                <li>Your data is stored securely and will not be shared with third parties without your consent, except as required by law.</li>
                <li>Cookies may be used to improve user experience, and you may disable them in your browser settings.</li>
            </ul>
            <p><strong>No Refund Policy:</strong> Please note that all transactions on this platform are final. Refunds are not available under any circumstances.</p>
            <p>For further details or concerns, feel free to contact our support team.</p>
        </section>
    </div>
    
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
            <p>&copy; 2025 Plants Shop| Grow with us 🌱</p>
        </div>


    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            function updateBadges() {
                fetch('../php/get_counts.php') // Create a backend endpoint for fetching counts
                    .then(response => response.json())
                    .then(data => {
                        document.querySelector('.wishlist-badge').textContent = wishlistCount || 0;
                    document.querySelector('.cart-badge').textContent = cartCount || 0;

                });
            }

            // Call updateBadges periodically or after actions like adding/removing items
            updateBadges();
        });
    </script>

    
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
