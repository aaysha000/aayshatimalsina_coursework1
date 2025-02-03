<?php
session_start();
require '../php/db.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../php/login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch wishlist items for plants
$plantQuery = "SELECT plants.*, 'plant' AS type FROM plants 
               INNER JOIN user_wishlist ON plants.id = user_wishlist.plant_id 
               WHERE user_wishlist.user_id = :user_id";
$plantStmt = $pdo->prepare($plantQuery);
$plantStmt->execute(['user_id' => $userId]);
$plantWishlistItems = $plantStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch wishlist items for materials
$materialQuery = "SELECT materials.*, 'material' AS type FROM materials 
                  INNER JOIN user_wishlist ON materials.id = user_wishlist.material_id 
                  WHERE user_wishlist.user_id = :user_id";
$materialStmt = $pdo->prepare($materialQuery);
$materialStmt->execute(['user_id' => $userId]);
$materialWishlistItems = $materialStmt->fetchAll(PDO::FETCH_ASSOC);

// Combine both plants and materials for display
$wishlistItems = array_merge($plantWishlistItems, $materialWishlistItems);

function isActivePage($page) {
    return basename($_SERVER['PHP_SELF']) === $page ? 'active' : '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist</title>
    <link rel="stylesheet" href="../css/styles.css">
    <script src="../js/script.js"></script>
    <style>
        .wishlist-section {
            margin: 20px 0;
        }
        .wishlist-section h2 {
            font-size: 1.8rem;
            text-align: center;
            margin-bottom: 20px;
            color:#4CAF50;
        }
        .wishlist-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
            background-color: rgb(237, 247, 232);
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .wishlist-item {
            background-color: rgb(207, 219, 202);
            border: 2px solid #2c6e49;
            border-radius: 15px;
            overflow: hidden;
            padding: 15px;
            text-align: center;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .wishlist-item:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
        }
        .wishlist-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
        }
        .wishlist-item .name {
            font-size: 1.2rem;
            color: #ff5722;
            font-weight: bold;
            margin: 10px 0;
        }
        .wishlist-item .type {
            font-size: 1rem;
            color: #333;
            margin-bottom: 10px;
        }
        .wishlist-item .price {
            font-size: 1rem;
            color: #333;
            margin-bottom: 15px;
        }
        .wishlist-item button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }
        .wishlist-item button:hover {
            background-color: #367c39;
        }
        .empty-message {
            text-align: center;
            font-size: 1.5rem;
            color: #555;
            padding: 20px;
            background-color: rgb(157, 248, 121);
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <header>
        <h1>My Wishlist</h1>
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
        <?php if (empty($plantWishlistItems) && empty($materialWishlistItems)): ?>
            <div class="empty-message">Your wishlist is empty!</div>
        <?php else: ?>
            <?php if (!empty($plantWishlistItems)): ?>
                <section class="wishlist-section">
                    <h2>Plants</h2>
                    <div class="wishlist-container">
                        <?php foreach ($plantWishlistItems as $item): ?>
                            <div class="wishlist-item" data-item-id="<?= $item['id']; ?>" data-item-type="plant">
                                <img src="<?= htmlspecialchars($item['image_url']); ?>" alt="<?= htmlspecialchars($item['name']); ?>">
                                <div class="name"><?= htmlspecialchars($item['name']); ?></div>
                                <div class="type">Plant</div>
                                <div class="price">Rs.<?= number_format($item['price'], 2); ?></div>
                                <button class="remove-from-wishlist">Remove</button>
                                <button class="add-to-cart">Add to Cart</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
            <?php if (!empty($materialWishlistItems)): ?>
                <section class="wishlist-section">
                    <h2>Materials</h2>
                    <div class="wishlist-container">
                        <?php foreach ($materialWishlistItems as $item): ?>
                            <div class="wishlist-item" data-item-id="<?= $item['id']; ?>" data-item-type="material">
                                <img src="<?= htmlspecialchars($item['image_url']); ?>" alt="<?= htmlspecialchars($item['name']); ?>">
                                <div class="name"><?= htmlspecialchars($item['name']); ?></div>
                                <div class="type">Material</div>
                                <div class="price">Rs.<?= number_format($item['price'], 2); ?></div>
                                <button class="remove-from-wishlist">Remove</button>
                                <button class="add-to-cart">Add to Cart</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <script>
            
        document.addEventListener('DOMContentLoaded', () => {
        const addToCartButtons = document.querySelectorAll('.add-to-cart');
        const removeFromWishlistButtons = document.querySelectorAll('.remove-from-wishlist');
        const wishlistBadge = document.querySelector('.wishlist-badge');
        const cartBadge = document.querySelector('.cart-badge');
        const wishlistContainer = document.querySelector('main');

            // Add to cart functionality
            addToCartButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const itemId = button.closest('.wishlist-item').getAttribute('data-item-id');
                    const itemType = button.closest('.wishlist-item').getAttribute('data-item-type');

                    const payload = {
                        action: 'add',
                    };

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
                        .then(data => {
                            if (data.success) {
                                alert('Item added to cart successfully!');
                                updateBadges(); // Refresh badge counts after adding to cart
                            } else {
                                alert('Failed to add to cart: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error adding to cart:', error);
                        });
                });
            });

            // Remove from wishlist functionality
            removeFromWishlistButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const itemElement = button.closest('.wishlist-item');
                    const itemId = itemElement.getAttribute('data-item-id');
                    const itemType = itemElement.getAttribute('data-item-type');

                    const payload = {
                        action: 'remove',
                    };

                    if (itemType === 'plant') {
                        payload.plantId = itemId;
                    } else if (itemType === 'material') {
                        payload.materialId = itemId;
                    }

                    fetch('../php/update_wishlist.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Item removed from wishlist successfully!');
                                itemElement.remove(); // Remove item from UI
                                updateBadges(); // Refresh badge counts
                                checkWishlistEmpty(); // Check if wishlist is empty
                            } else {
                                alert('Failed to remove from wishlist: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error removing from wishlist:', error);
                        });
                });
            });

            // Function to update badges dynamically
            function updateBadges() {
                return fetch('../php/get_counts.php', {
                    method: 'GET',
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update wishlist and cart badge counts
                            if (wishlistBadge) wishlistBadge.textContent = data.wishlistCount || 0;
                            if (cartBadge) cartBadge.textContent = data.cartCount || 0;
                        } else {
                            console.error('Failed to fetch badge counts: ', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error updating badges:', error);
                    });
            }

            // Function to check if wishlist is empty
            function checkWishlistEmpty() {
                const remainingItems = document.querySelectorAll('.wishlist-item');
                if (remainingItems.length === 0) {
                    wishlistContainer.innerHTML = `
                        <div class="empty-message">Your wishlist is empty!</div>
                    `;
                }
            }

            // Initial badge update on page load
            updateBadges();
        });

        
    </script>

</body>
</html>
