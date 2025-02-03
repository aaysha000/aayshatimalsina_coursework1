// Fetch plant data from the backend
async function fetchPlants() {
    try {
        const response = await fetch('../php/fetch_plants.php'); // Ensure the path is correct
        if (!response.ok) {
            throw new Error('Failed to fetch plant data.');
        }
        const plants = await response.json();
        return plants;
    } catch (error) {
        console.error('Error fetching plant data:', error);
        return [];
    }
}

// Check if the logged-in user is an admin
async function isAdminUser() {
    try {
        const response = await fetch('../php/session_status.php'); // Ensure the path is correct
        if (!response.ok) {
            throw new Error('Failed to fetch session status.');
        }
        const data = await response.json();
        return data.logged_in && data.role === 'admin'; // Check if the user is logged in and has an admin role
    } catch (error) {
        console.error('Error fetching session status:', error);
        return false; // Default to false if there's an error
    }
}

// Render plant cards dynamically with search and filter options
async function renderPlants(filterType = 'All', searchQuery = '') {
    const plantCardsContainer = document.getElementById('plant-cards');
    plantCardsContainer.innerHTML = ''; // Clear existing plants

    const plants = await fetchPlants();
    const isAdmin = await isAdminUser(); // Check if the user is an admin

    // Filter plants by type and search query
    const filteredPlants = plants.filter(plant => {
        const matchesType = filterType === 'All' || plant.type === filterType;
        const matchesSearch = plant.name.toLowerCase().includes(searchQuery.toLowerCase());
        return matchesType && matchesSearch;
    });

    if (filteredPlants.length === 0) {
        plantCardsContainer.innerHTML = '<p>No plants found.</p>';
        return;
    }

    filteredPlants.forEach(plant => {
        const card = document.createElement('div');
        card.className = 'card';
        card.innerHTML = `
            <h3>${plant.name}</h3>
            <p><strong>Type:</strong> ${plant.type}</p>
            <p><strong>Classification:</strong> ${plant.classification}</p>
            <p><strong>Care Instructions:</strong> ${plant.care_instructions}</p>
            <p><strong>History:</strong> ${plant.history}</p>
            ${
                isAdmin
                    ? `
                        <div>
                            <a href="../php/edit_plant.php?id=${plant.id}" class="btn btn-primary">Edit</a>
                            <a href="../php/delete_plant.php?id=${plant.id}" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this plant?')">Delete</a>
                        </div>
                      `
                    : ''
            }
        `;
        plantCardsContainer.appendChild(card);
    });
}

// Attach event listeners for search and filter functionality
function setupSearchAndFilter() {
    const searchBar = document.getElementById('search-bar');
    const filterDropdown = document.getElementById('filter-type');
    const searchButton = document.getElementById('search-btn');

    searchButton.addEventListener('click', () => {
        const searchQuery = searchBar.value.trim();
        const filterType = filterDropdown.value;
        renderPlants(filterType, searchQuery);
    });

    // Optional: Add event listener for Enter key on the search bar
    searchBar.addEventListener('keypress', (event) => {
        if (event.key === 'Enter') {
            const searchQuery = searchBar.value.trim();
            const filterType = filterDropdown.value;
            renderPlants(filterType, searchQuery);
        }
    });
}

// Fetch session status and update login/logout buttons dynamically
async function updateAuthLinks() {
    const authLinks = document.getElementById('auth-links');
    try {
        const response = await fetch('../php/session_status.php'); // Ensure the path is correct
        const data = await response.json();

        if (data.logged_in) {
            if (data.role === 'admin') {
                authLinks.innerHTML = `
                    <a href="../php/logout.php" class="btn btn-outline">Logout</a>
                    <span>Hello, ${data.username}</span>
                `;
            } else {
                authLinks.innerHTML = `
                    <a href="../php/logout.php" class="btn btn-outline">Logout</a>
                    <span>Hello, ${data.username}</span>
                `;
            }
        } else {
            authLinks.innerHTML = `
                <a href="../php/login.php" class="btn btn-success">Login</a>
            `;
        }
    } catch (error) {
        console.error('Error fetching session status:', error);
    }
}

// Initialize the application
document.addEventListener('DOMContentLoaded', () => {
    renderPlants(); // Render all plants initially
    setupSearchAndFilter(); // Set up search and filter functionality
    updateAuthLinks(); // Update login/logout buttons dynamically
});


document.addEventListener('DOMContentLoaded', () => {
    // Update wishlist and cart badges dynamically
    function updateBadges() {
        fetch('../php/get_counts.php')
            .then(response => response.json())
            .then(data => {
                const wishlistBadge = document.querySelector('.wishlist-badge');
                const cartBadge = document.querySelector('.cart-badge');

                if (wishlistBadge) wishlistBadge.textContent = data.wishlistCount || 0;
                if (cartBadge) cartBadge.textContent = data.cartCount || 0;
            })
            .catch(error => console.error('Error updating badges:', error));
    }

    // Wishlist functionality for both plants and materials
    const wishlistIcons = document.querySelectorAll('.wishlist-icon');
    wishlistIcons.forEach(icon => {
        icon.addEventListener('click', () => {
            const parentElement = icon.closest('.plant-bubble') || icon.closest('.material-bubble');
            const plantId = parentElement.getAttribute('data-plant-id') || null;
            const materialId = parentElement.getAttribute('data-material-id') || null;

            fetch('../php/update_wishlist.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ plantId, materialId }),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const action = data.action === 'added' ? 'added to' : 'removed from';
                        alert(`Item ${action} wishlist!`);

                        // Toggle the filled class based on action
                        if (data.action === 'added') {
                            icon.classList.add('filled');
                        } else {
                            icon.classList.remove('filled');
                        }

                        updateBadges();
                    } else {
                        alert('Failed to update wishlist: ' + data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    });

    // Add to cart functionality for both plants and materials
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', () => {
            const parentElement = button.closest('.plant-bubble') || button.closest('.material-bubble');
            const plantId = parentElement.getAttribute('data-plant-id') || null;
            const materialId = parentElement.getAttribute('data-material-id') || null;

            fetch('../php/update_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ plantId, materialId, action: 'add' }),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Item added to cart successfully!');
                        updateBadges();
                    } else {
                        alert('Failed to add to cart: ' + data.message);
                    }
                })
                .catch(error => console.error('Error adding to cart:', error));
        });
    });
});




document.addEventListener("DOMContentLoaded", () => {
    const profileIcon = document.getElementById("profile-icon");
    const profileDropdown = document.querySelector(".profile-dropdown");

    if (profileIcon && profileDropdown) {
        // Toggle dropdown visibility on profile icon click
        profileIcon.addEventListener("click", (event) => {
            event.stopPropagation(); // Prevent click event from bubbling up
            profileDropdown.classList.toggle("open"); // Toggle dropdown visibility
        });

        // Close dropdown if clicked outside
        document.addEventListener("click", (event) => {
            if (!profileDropdown.contains(event.target) && event.target !== profileIcon) {
                profileDropdown.classList.remove("open");
            }
        });
    }
});









