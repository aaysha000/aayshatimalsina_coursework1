<?php
session_start();
require '../php/db.php';


$stmt = $pdo->query("SELECT * FROM plants");
$plants = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

$reviewStmt = $pdo->prepare("SELECT r.id, r.review, r.rating, u.username, r.created_at 
                             FROM reviews r 
                             JOIN users u ON r.user_id = u.id 
                             ORDER BY r.created_at DESC 
                             LIMIT 5");
$reviewStmt->execute();
$reviews = $reviewStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch notifications for the logged-in user
$userId = $_SESSION['user_id'] ?? null;
$notifications = [];

if ($userId) {
    $notificationsQuery = "SELECT id, message, created_at FROM notifications WHERE user_id = :user_id AND viewed = 0";
    $notificationsStmt = $pdo->prepare($notificationsQuery);
    $notificationsStmt->execute(['user_id' => $userId]);
    $notifications = $notificationsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Mark notifications as viewed
    if (!empty($notifications)) {
        $updateQuery = "UPDATE notifications SET viewed = 1 WHERE user_id = :user_id AND viewed = 0";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute(['user_id' => $userId]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plants Web Application</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        /* Notifications Container */
        .notifications {
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
        }

        /* Notifications Title */
        .notifications h3 {
            margin-bottom: 15px;
            font-size: 1.5rem;
            color: #2c6e49;
            text-align: center;
            font-weight: bold;
        }

        /* Notifications List */
        .notifications ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        /* Individual Notification */
        .notifications li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            border-bottom: 1px solid #ddd;
            background-color: #ffffff;
            border-radius: 8px;
            margin-bottom: 10px;
            transition: background-color 0.3s ease;
        }

        /* Last Notification: Remove Border */
        .notifications li:last-child {
            border-bottom: none;
        }

        /* Notification Text */
        .notifications li p {
            margin: 0;
            font-size: 1rem;
            color: #333;
            flex-grow: 1;
        }

        /* Notification Date */
        .notifications li small {
            font-size: 0.9rem;
            color: #777;
            margin-left: 15px;
        }

        /* Hover Effect for Notifications */
        .notifications li:hover {
            background-color: #f0f7f0;
        }

        .about-container {
            margin: 40px auto;
            max-width: 1200px;
        }

        .about-text {
            text-align: center;
            margin-bottom: 30px;
        }

        .about-text h2 {
            color: #2c6e49;
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .about-text p {
            line-height: 1.6;
            margin-bottom: 15px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .about-quote {
            font-style: italic;
            font-size: 1.2rem;
            color: #555;
            margin-top: 20px;
        }

        .about-images-container {
            display: flex;
            justify-content: space-between;
            gap: 15px;
        }

        .about-images-container .image-frame {
            flex: 1;
            height: 400px;
            max-width: 250px;
            border: 2px solid #2c6e49;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .about-images-container .image-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .about-additional-info {
            margin-top: 50px;
            text-align: center;
        }

        .about-additional-info h3 {
            color: #2c6e49;
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .about-additional-info ul {
            list-style: none;
            padding: 0;
            margin: 0 auto;
            max-width: 800px;
        }

        .about-additional-info ul li {
            margin: 10px 0;
            line-height: 1.6;
        }

        .facts-section {
            text-align: center;
            margin: 50px auto;
            max-width: 1200px;
        }

        .facts-section h2 {
            color: #2c6e49;
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        .facts-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            justify-items: center;
            align-items: center;
        }

        .fact-bubble {
            position: relative;
            width: 270px;
            background-color: rgb(237, 247, 232);
            border: 2px solid #2c6e49;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .fact-bubble:hover {
            transform: scale(1.05);
        }

        .fact-bubble img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            loading: lazy;
        }

        .fact-bubble p {
            margin: 0;
            padding: 15px;
            width: 100%;
            text-align: center;
            background-color: #2c6e49;
            color: #fff;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .fact-info {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 100%;
            background-color: rgb(237, 247, 232);
            transform: translateY(100%);
            transition: transform 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 10px;
            box-sizing: border-box;
        }

        .fact-bubble:hover .fact-info {
            transform: translateY(0);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            max-width: 500px;
            text-align: center;
        }

        .modal-content img {
            width: 100%;
            height: auto;
            margin-bottom: 20px;
        }

        .close-modal {
            background: #2c6e49;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .close-modal:hover {
            background: #1e4c34;
        }

        /* Slider Container */
        .slider-container {
            position: relative;
            width: 100vw; /* Full viewport width */
            height: 400px; /* Adjustable height */
            overflow: hidden;
            margin: 0 auto; /* Center aligns container */
            margin-top: -18px;
            left: 50%;
            transform: translateX(-50%); /* Ensures full alignment across viewport */
            background-color: #f9f9f9; /* Matches the body background */
        }

        /* Slider Wrapper */
        .slider-wrapper {
            display: flex;
            transition: transform 0.5s ease-in-out;
            width: 100%; /* Full width for all slides */
            height: 100%;
        }

        /* Individual Slides */
        .slide {
            flex: 0 0 100%; /* Each slide spans full width */
            height: 100%;
            position: relative;
        }

        /* Images in Slides */
        .slide img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Ensures images scale to fit the container */
            display: block;
        }

        /* Content Overlay on Slides */
        .slide-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.19); /* Semi-transparent background */
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            max-width: 80%; /* Restrict the width */
        }

        .slide-content h2 {
            font-size: 2rem;
            margin: 0 0 10px;
        }

        .slide-content button {
            padding: 10px 20px;
            font-size: 1rem;
            background-color: #4CAF50; /* Green button */
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .slide-content button:hover {
            background-color: #367c39; /* Darker green on hover */
        }

        /* Navigation Arrows */
        .arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(0, 0, 0, 0.6);
            color: white;
            border: none;
            font-size: 2rem;
            padding: 10px;
            cursor: pointer;
            z-index: 100;
            border-radius: 50%;
            transition: background-color 0.3s ease;
        }

        .arrow:hover {
            background-color: rgba(0, 0, 0, 0.8);
        }

        .arrow-left {
            left: 10px;
        }

        .arrow-right {
            right: 10px;
        }

        .delete-button {
            background-color: #ff4d4f; /* Red color for delete button */
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .delete-button:hover {
            background-color: #d9363e; /* Darker red on hover */
        }

        .delete-form {
            margin-top: 10px; /* Space above the button */
            text-align: right; /* Align button to the right */
        }


        /* Container for the entire Customer Reviews section */
        #customer-reviews {
            background-color: #f3f7ed; /* Soft greenish background for a calm and natural feel */
            padding: 60px 20px; /* Adds space around the section */
            margin-top: 50px; /* Creates space between this section and the previous content */
            border-top: 2px solid #2c6e49; /* Adds a border to separate it visually */
        }

        /* Wrapper for the reviews content */
        .reviews-container {
            max-width: 1200px; /* Limits the section width for better readability */
            margin: 0 auto; /* Centers the container horizontally */
            text-align: center; /* Aligns text to the center */
        }

        /* Heading for the Customer Reviews section */
        .reviews-container h2 {
            font-size: 2.5rem; /* Large, bold heading size */
            color: #2c6e49; /* Green color matching the theme */
            margin-bottom: 30px; /* Space below the heading */
            text-transform: uppercase; /* Makes the heading text uppercase */
            font-family: 'Poppins', sans-serif; /* Modern and professional font */
            font-weight: bold; /* Ensures the heading stands out */
            letter-spacing: 1.5px; /* Adds spacing between letters */
        }

        /* Wrapper for individual review cards */
        .reviews-list {
            display: flex; /* Aligns reviews in a row */
            flex-wrap: wrap; /* Allows wrapping on smaller screens */
            gap: 20px; /* Space between each review card */
            justify-content: center; /* Centers the review cards */
            margin-top: 20px; /* Adds space between the heading and the reviews */
        }

        /* Styling for individual review cards */
        .review-card {
            background: #ffffff; /* White background for a clean look */
            border: 1px solid #d9e8d4; /* Subtle green border for definition */
            border-radius: 12px; /* Rounded corners for a modern feel */
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1); /* Soft shadow for depth */
            padding: 20px; /* Inner padding for spacing */
            max-width: 350px; /* Increased width for larger cards */
            min-width: 300px; /* Ensures cards have a consistent minimum width */
            min-height: 150px; /* Increased height for better content spacing */
            text-align: left; /* Aligns text to the left inside cards */
            transition: transform 0.3s ease, box-shadow 0.3s ease; /* Smooth hover animations */
        }

        /* Hover effect for review cards */
        .review-card:hover {
            transform: translateY(-5px); /* Moves the card slightly upward */
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15); /* Enhances shadow on hover */
        }

        /* Common styling for text inside review cards */
        .review-card p {
            margin: 10px 0; /* Adds space between paragraphs */
            font-family: 'Roboto', sans-serif; /* Clean and modern font */
            color: #555555; /* Subtle dark gray for readability */
        }

        /* Styling for the review text */
        .review-text {
            font-size: 1.1rem; /* Slightly larger text size */
            font-style: italic; /* Italic style for emphasis */
            color: #333333; /* Darker color for focus */
            margin-bottom: 15px; /* Space below the review text */
        }

        /* Styling for the rating stars container */
        .review-rating {
            font-weight: bold; /* Makes the "Rating" label stand out */
            font-size: 1rem; /* Consistent text size */
            color: #4CAF50; /* Matches the theme's green color */
            margin-bottom: 10px; /* Space below the stars */
            display: flex; /* Aligns stars and text horizontally */
            align-items: center; /* Vertically aligns stars and text */
            justify-content: flex-start; /* Aligns stars to the left */
            gap: 8px; /* Adds space between "Rating" and the stars */
        }

        /* Default style for stars */
        .star {
            font-size: 1.4rem; /* Increased size for better visibility */
            color: #ccc; /* Default color for unfilled stars */
            margin-right: 2px; /* Space between stars */
            transition: color 0.3s ease; /* Smooth color transition */
        }

        /* Styling for filled stars */
        .star.filled {
            color: #FFD700; /* Gold color for filled stars */
        }

        /* Styling for the review author */
        .review-author {
            font-size: 0.9rem; /* Slightly smaller text */
            color: #777777; /* Light gray for subtle emphasis */
            text-align: right; /* Aligns the author name to the right */
            margin-top: 10px; /* Space above the author name */
            font-weight: bold; /* Makes the author's name stand out */
        }

        /* Styling for the review date */
        .review-date {
            font-size: 0.8rem; /* Small text for the date */
            color: #999999; /* Lighter gray for less focus */
            text-align: right; /* Aligns the date to the right */
            margin-top: 5px; /* Space above the date */
        }

    </style>
</head>
<body>
    <header>
        <h1>Welcome to the Plant Shop</h1>
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

    <!-- Notifications Section -->
    <?php if (!empty($notifications)): ?>
        <div id="notificationBox" class="notifications">
            <h3>Notifications</h3>
            <ul>
                <?php foreach ($notifications as $notification): ?>
                    <li>
                        <p><?= htmlspecialchars($notification['message']); ?></p>
                        <small><?= date('F j, Y, g:i A', strtotime($notification['created_at'])); ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <main class="container">

        <!-- Slider Section -->
        <div class="slider-container">
            <button class="arrow arrow-left" onclick="moveSlide(-1)">&#8592;</button>
            <div class="slider-wrapper">
                <div class="slide">
                    <img src="../img/collection.jpg.webp" alt="Slide 1">
                    <div class="slide-content">
                        <h2>Fresh Plants Collection</h2>
                        <a href="types_of_plants.php">
                            <button>Shop Now</button>
                        </a>
                    </div>
                </div>
                <div class="slide">
                    <img src="../img/rare.jpg" alt="Slide 2">
                    <div class="slide-content">
                        <h2>Rare Species Available</h2>
                        <a href="types_of_plants.php">
                            <button>Shop Now</button>
                        </a>
                    </div>
                </div>
                <div class="slide">
                    <img src="../img/deals.webp" alt="Slide 3">
                    <div class="slide-content">
                        <h2>Exclusive Deals on Plants</h2>
                        <a href="types_of_plants.php">
                            <button>Shop Now</button>
                        </a>
                    </div>
                </div>
            </div>
            <button class="arrow arrow-right" onclick="moveSlide(1)">&#8594;</button>
        </div>

        <script>
            let currentSlide = 0;

            function moveSlide(direction) {
                const slides = document.querySelectorAll('.slide');
                const totalSlides = slides.length;
                const sliderWrapper = document.querySelector('.slider-wrapper');

                currentSlide += direction;

                if (currentSlide < 0) {
                    currentSlide = totalSlides - 1;
                } else if (currentSlide >= totalSlides) {
                    currentSlide = 0;
                }

                sliderWrapper.style.transform = translateX(-${currentSlide * 100}%);
            }

            // Automatically move slides every 3 seconds
            setInterval(() => {
                moveSlide(1); // Move to the next slide
            }, 3000); // 3000 milliseconds = 3 seconds
        </script>


        <section id="about">
            <div class="about-container">
                <div class="about-text">
                    <h2>About the Plant Shop</h2>
                    
                    <p>Welcome to the ultimate destination for all plant enthusiasts! Discover a wide range of plants, from common houseplants to rare species, all available at competitive prices.</p>
                    
                    <p>Whether you're a seasoned gardener or just starting your plant journey, our shop is here to cater to your needs.</p>
                
                    <p class="about-quote">
                        "Plants give us oxygen for the lungs and for the soul." — Linda Solegato
                    </p>
                </div>
                <div class="about-images-container">
                    <div class="image-frame"><img src="../img/summer.jpg" alt="Gaillardia"></div>
                    <div class="image-frame"><img src="../img/autumn.jpeg" alt="Maple"></div>
                    <div class="image-frame"><img src="../img/winter.jpg" alt="Camellia"></div>
                    <div class="image-frame"><img src="../img/spring.jpg.avif" alt="Flowering Quince"></div>
                </div>
            </div>
        </section>

        <section class="about-additional-info">
            <h3>Why Learn About Plants?</h3>
            <ul>
                <li>Plants improve air quality by producing oxygen and reducing carbon dioxide levels.</li>
                <li>Understanding plants helps in maintaining biodiversity and ecological balance.</li>
                <li>Gardening and plant care can reduce stress and enhance mental well-being.</li>
                <li>Plants provide us with food, medicine, and raw materials for various industries.</li>
            </ul>
        </section>

        <section id="facts" class="facts-section">
            <h2>Interesting Facts About Plants</h2>
            <div class="facts-container">
                <div class="fact-bubble">
                    <img src="../img/pando.jpeg" alt="Pando Tree">
                    <p>Immortal Tree Colony</p>
                    <div class="fact-info">Pando, located in Utah's Fishlake National Forest, is the world's largest and heaviest organism, spanning 106 acres, weighing nearly 13 million pounds, and originating from a single seed over 2.6 million years ago, reproducing exclusively through cloning due to its triploid nature.</div>
                </div>
                <div class="fact-bubble">
                    <img src="../img/nickel.jpeg" alt="Nickel Plant">
                    <p>Plants That Bleed Metal</p>
                    <div class="fact-info">Pycnandra acuminata, a tree native to New Caledonia, produces a green-blue sap rich in nickel, classifying it as a hyperaccumulator plant. </div>
                </div>
                <div class="fact-bubble">
                    <img src="../img/venus.jpg" alt="Venus Flytrap">
                    <p>Plants Can Count</p>
                    <div class="fact-info">The Venus flytrap (Dionaea muscipula) does not snap shut at random. It “counts” the number of times its hairs are touched. The trap closes only after two touches within about 20 seconds, ensuring that it doesn't waste energy.</div>
                </div>
                <div class="fact-bubble">
                    <img src="../img/dead.jpg" alt="Gympie-Gympie Tree">
                    <p>The Deadliest Plant</p>
                    <div class="fact-info">The Gympie-Gympie tree, found in Australia, has leaves covered with tiny, needle-like hairs that inject a venom causing pain so intense that it can last for months. Some animals have been reported to die from the shock of touching it.</div>
                </div>
                <div class="fact-bubble">
                    <img src="../img/walk.jpg" alt="Walking Palm">
                    <p>Plants That "Walk"</p>
                    <div class="fact-info">The Walking Palm Tree (Socratea exorrhiza) in Central and South America appears to "move" over time. It grows new roots and shifts toward sunlight, though this is debated among scientists.</div>
                </div>
                <div class="fact-bubble">
                    <img src="../img/explode.jpg" alt="Dynamite Tree">
                    <p>Exploding Fruit</p>
                    <div class="fact-info">The Sandbox Tree (Hura crepitans), also known as the “Dynamite Tree,” produces fruits that explode with a loud bang, sending seeds flying at speeds of up to 160 miles per hour.</div>
                </div>
                <div class="fact-bubble">
                    <img src="../img/glow.jpg" alt="Bioluminescent Plant">
                    <p>Plants That Glow</p>
                    <div class="fact-info">Foxfire fungus, also known as bioluminescent fungi, emits a greenish glow in the dark due to a chemical reaction involving the enzyme luciferase, which helps it attract insects to spread its spores.</div>
                </div>
                <div class="fact-bubble">
                    <img src="../img/mem.jpg" alt="Mimosa Pudica">
                    <p>Memory In Plants</p>
                    <div class="fact-info">The Mimosa pudica, or “Touch-Me-Not,” doesn’t just react to touch—it can "remember." If repeatedly exposed to harmless touches, it will stop folding its leaves, showing a form of plant learning. This memory can last for weeks.</div>
                </div>
            </div>
        </section>
    </main>

    <section id="customer-reviews">
        <div class="reviews-container">
            <h2>Customer Reviews</h2>
            <?php if (count($reviews) > 0): ?>
                <div class="reviews-list">
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-card">
                            <p class="review-text">"<?= htmlspecialchars($review['review']); ?>"</p>
                            <div class="review-rating">
                                Rating:
                                <?php
                                $rating = htmlspecialchars($review['rating']);
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $rating) {
                                        echo '<span class="star filled">★</span>';
                                    } else {
                                        echo '<span class="star">☆</span>';
                                    }
                                }
                                ?>
                            </div>
                            <p class="review-author">- <?= htmlspecialchars($review['username']); ?></p>
                            <p class="review-date"><?= date("F j, Y", strtotime($review['created_at'])); ?></p>

                            <!-- Show delete button only if the user is an admin -->
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <form method="POST" action="../php/delete_review.php" class="delete-form">
                                    <input type="hidden" name="review_id" value="<?= htmlspecialchars($review['id'] ?? ''); ?>">
                                    <button type="submit" class="delete-button">Delete</button>
                                </form>
                            <?php endif; ?>
                        </div>

                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No reviews available. Be the first to leave a review!</p>
            <?php endif; ?>
        </div>
    </section>

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

<script>
    document.addEventListener("DOMContentLoaded", () => {
        let currentSlide = 0;

        function moveSlide(direction) {
            const slides = document.querySelectorAll(".slide");
            const totalSlides = slides.length;
            const sliderWrapper = document.querySelector(".slider-wrapper");

            // Update current slide index
            currentSlide += direction;

            // Wrap around if out of bounds
            if (currentSlide < 0) {
                currentSlide = totalSlides - 1;
            } else if (currentSlide >= totalSlides) {
                currentSlide = 0;
            }

            // Move the slider wrapper
            sliderWrapper.style.transform = `translateX(-${currentSlide * 100}%)`;
        }

        // Automatically move slides every 3 seconds
        const autoSlideInterval = setInterval(() => {
            moveSlide(1);
        }, 3000);

        // Add event listeners to arrows
        document.querySelector(".arrow-left").addEventListener("click", () => {
            moveSlide(-1);
            clearInterval(autoSlideInterval); // Stop auto-slide on manual navigation
        });

        document.querySelector(".arrow-right").addEventListener("click", () => {
            moveSlide(1);
            clearInterval(autoSlideInterval); // Stop auto-slide on manual navigation
        });
    });
</script>

<script>
     // Automatically hide the notification after 30 seconds
    const notificationBox = document.getElementById('notificationBox');
    if (notificationBox) {
        setTimeout(() => {
            notificationBox.style.transition = "opacity 0.5s ease";
            notificationBox.style.opacity = "0";
            setTimeout(() => {
                notificationBox.style.display = "none";
            }, 500); // Allow the transition to complete
        }, 30000); // 30 seconds in milliseconds
    }
</script>

</body>
</html>