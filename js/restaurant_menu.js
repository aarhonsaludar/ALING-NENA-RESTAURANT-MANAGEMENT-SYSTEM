// Menu items will be loaded dynamically from database
let menuItems = [];

// Update the createMenuItem function to include rating
const createMenuItem = (id, name, desc, price, image, category) => ({
    id, 
    name, 
    desc, 
    price, 
    image: `img/${category}/${image}`, 
    category,
    rating: (Math.random() * 2 + 3).toFixed(1), // Random rating between 3.0 and 5.0
    ratingCount: Math.floor(Math.random() * 500 + 50) // Random number of ratings
});

// Function to load menu items from database
async function loadMenuFromDatabase() {
    try {
        const response = await fetch('get_menu.php');
        const data = await response.json();
        
        if (data.success && data.items) {
            menuItems = data.items;
            
            // Initialize category data after loading menu items
            categoryData = computeCategoryData();
            
            // Display everything
            displayCategories();
            displayMenuItems();
            updateWishlistCount();
            displayWishlistDropdown();
        } else {
            console.error('Failed to load menu items');
            // Fallback to hardcoded items if needed
            loadHardcodedMenu();
        }
    } catch (error) {
        console.error('Error loading menu:', error);
        // Fallback to hardcoded items
        loadHardcodedMenu();
    }
}

// Fallback function with hardcoded menu items
function loadHardcodedMenu() {
    const mainDishes = [
        createMenuItem(1, "Classic Burger", "Juicy beef patty with fresh vegetables", 75.00, "burger.jpg", "Main Dishes"),
        createMenuItem(2, "Pepperoni Pizza", "Traditional pizza with pepperoni and cheese", 130.00, "pizza.jpg", "Main Dishes"),
        createMenuItem(3, "Spaghetti Meatballs", "Pasta with homemade meatballs", 95.00, "pasta.jpg", "Main Dishes"),
        createMenuItem(4, "Grilled Chicken", "Herb-marinated grilled chicken breast", 120.00, "grilled_chicken.jpg", "Main Dishes"),
        createMenuItem(5, "Beef Steak", "Premium cut beef steak with vegetables", 350.00, "steak.jpg", "Main Dishes"),
        createMenuItem(6, "Fish & Chips", "Crispy battered fish with fries", 180.00, "fish_chips.jpg", "Main Dishes"),
        createMenuItem(7, "Chicken Curry", "Spicy chicken curry with rice", 140.00, "chicken_curry.jpg", "Main Dishes"),
        createMenuItem(8, "Beef Tacos", "Three soft tacos with seasoned beef", 110.00, "tacos.jpg", "Main Dishes"),
        createMenuItem(9, "Pork Chop", "Grilled pork chop with apple sauce", 160.00, "pork_chop.jpg", "Main Dishes"),
        createMenuItem(10, "Salmon Fillet", "Grilled salmon with lemon butter", 220.00, "salmon_fillet.jpg", "Main Dishes")
    ];

    const appetizers = [
        createMenuItem(11, "Buffalo Wings", "Spicy chicken wings with blue cheese dip", 140.00, "buffalo_wings.jpg", "Appetizers"),
        createMenuItem(12, "Mozzarella Sticks", "Breaded cheese sticks with marinara", 90.00, "mozzarella_sticks.jpg", "Appetizers"),
        createMenuItem(13, "Nachos Grande", "Loaded nachos with cheese and toppings", 120.00, "nachos_grande.jpg", "Appetizers"),
        createMenuItem(14, "Spinach Dip", "Creamy spinach and artichoke dip", 95.00, "spinach_dip.jpg", "Appetizers"),
        createMenuItem(15, "Calamari", "Fried squid rings with aioli", 110.00, "calamari.jpg", "Appetizers"),
        createMenuItem(16, "Bruschetta", "Toasted bread with tomatoes and herbs", 85.00, "bruschetta.jpg", "Appetizers"),
        createMenuItem(17, "Spring Rolls", "Vegetable spring rolls with sweet chili sauce", 75.00, "spring_rolls.jpg", "Appetizers"),
        createMenuItem(18, "Garlic Bread", "Toasted bread with garlic butter", 60.00, "garlic_bread.jpg", "Appetizers"),
        createMenuItem(19, "Potato Skins", "Loaded potato skins with bacon and cheese", 95.00, "potato_skins.jpg", "Appetizers"),
        createMenuItem(20, "Shrimp Cocktail", "Chilled shrimp with cocktail sauce", 130.00, "shrimp_cocktail.jpg", "Appetizers")
    ];

    const salads = [
        createMenuItem(21, "Caesar Salad", "Fresh romaine with Caesar dressing", 80.00, "caesar_salad.jpg", "Salads"),
        createMenuItem(22, "Greek Salad", "Mediterranean salad with feta cheese", 85.00, "greek_salad.jpg", "Salads"),
        createMenuItem(23, "Garden Salad", "Mixed greens with vegetables", 70.00, "garden_salad.jpg", "Salads"),
        createMenuItem(24, "Cobb Salad", "Salad with chicken, bacon, and eggs", 95.00, "cobb_salad.jpg", "Salads"),
        createMenuItem(25, "Caprese Salad", "Fresh mozzarella, tomatoes, and basil", 90.00, "caprese_salad.jpg", "Salads"),
        createMenuItem(26, "Asian Chicken Salad", "Oriental salad with grilled chicken", 100.00, "asian_chicken_salad.jpg", "Salads"),
        createMenuItem(27, "Taco Salad", "Mexican-style salad in tortilla bowl", 95.00, "taco_salad.jpg", "Salads"),
        createMenuItem(28, "Quinoa Salad", "Healthy quinoa with vegetables", 85.00, "quinoa_salad.jpg", "Salads"),
        createMenuItem(29, "Spinach Salad", "Fresh spinach with warm bacon dressing", 80.00, "spinach_salad.jpg", "Salads"),
        createMenuItem(30, "Waldorf Salad", "Apple, walnut, and celery salad", 75.00, "waldorf_salad.jpg", "Salads")
    ];

    const desserts = [
        createMenuItem(31, "Chocolate Cake", "Rich chocolate layer cake", 85.00, "chocolate_cake.jpg", "Desserts"),
        createMenuItem(32, "Cheesecake", "New York style cheesecake", 95.00, "cheese_cake.jpg", "Desserts"),
        createMenuItem(33, "Apple Pie", "Homemade apple pie with ice cream", 80.00, "apple_pie.jpg", "Desserts"),
        createMenuItem(34, "Tiramisu", "Classic Italian coffee dessert", 90.00, "tiramisu.jpg", "Desserts"),
        createMenuItem(35, "Crème Brûlée", "French vanilla custard dessert", 100.00, "creme_brulee.jpg", "Desserts"),
        createMenuItem(36, "Ice Cream Sundae", "Three scoops with toppings", 75.00, "ice_cream_sundae.jpg", "Desserts"),
        createMenuItem(37, "Lemon Tart", "Tangy lemon custard tart", 85.00, "lemon_tart.jpg", "Desserts"),
        createMenuItem(38, "Chocolate Mousse", "Light and airy chocolate dessert", 80.00, "chocolate_mousee.jpg", "Desserts"),
        createMenuItem(39, "Fruit Parfait", "Fresh fruits with yogurt and granola", 70.00, "fruit_parfait.jpg", "Desserts"),
        createMenuItem(40, "Bread Pudding", "Warm bread pudding with caramel", 75.00, "bread_pudding.jpg", "Desserts")
    ];

    menuItems = [
        ...mainDishes,
        ...appetizers,
        ...salads,
        ...desserts
    ];
    
    categoryData = computeCategoryData();
    displayCategories();
    displayMenuItems();
}

let wishlist = JSON.parse(localStorage.getItem('wishlist')) || [];

function toggleWishlist(foodId) {
    const index = wishlist.indexOf(parseInt(foodId));
    if (index === -1) {
        wishlist.push(parseInt(foodId));
    } else {
        wishlist.splice(index, 1);
    }
    localStorage.setItem('wishlist', JSON.stringify(wishlist));
    displayMenuItems();
    updateWishlistCount(); // Add this line
    displayWishlistDropdown(); // Add this line

    // After updating wishlist, recompute category data
    categoryData = computeCategoryData();
    displayCategories();
}

function displayWishlistDropdown() {
    const wishlistItems = document.getElementById('wishlistItems');
    wishlistItems.innerHTML = '';

    if (wishlist.length === 0) {
        wishlistItems.innerHTML = '<div class="wishlist-item">No items in wishlist</div>';
        return;
    }

    wishlist.forEach(itemId => {
        const item = menuItems.find(menuItem => menuItem.id === itemId);
        if (item) {
            const itemElement = document.createElement('div');
            itemElement.className = 'wishlist-item';
            itemElement.innerHTML = `
                <img src="${item.image}" alt="${item.name}">
                <div class="wishlist-item-details">
                    <div class="wishlist-item-name">${item.name}</div>
                    <div class="wishlist-item-price">₱${item.price.toFixed(2)}</div>
                </div>
                <button class="remove-from-wishlist" data-id="${item.id}">×</button>
            `;
            wishlistItems.appendChild(itemElement);
        }
    });

    // Add event listeners to remove buttons
    document.querySelectorAll('.remove-from-wishlist').forEach(button => {
        button.addEventListener('click', (e) => {
            e.stopPropagation();
            const foodId = parseInt(button.getAttribute('data-id'));
            toggleWishlist(foodId);
        });
    });
}

function updateWishlistCount() {
    const count = wishlist.length;
    document.getElementById('wishlistCount').textContent = count;
}

// Compute category data based on menu items
function computeCategoryData() {
    const categories = {};
    
    // Count items per category
    menuItems.forEach(item => {
        if (!categories[item.category]) {
            categories[item.category] = {
                name: item.category,
                productCount: 0,
                pending: Math.floor(Math.random() * 3), // Random initial pending items
                wishlistCount: 0  // Initialize wishlist count
            };
        }
        categories[item.category].productCount++;
        // Count items in wishlist for this category
        if (wishlist.includes(item.id)) {
            categories[item.category].wishlistCount++;
        }
    });
    
    // Convert to array
    return Object.values(categories);
}

// Initialize category data
let categoryData = [];
let currentCategory = 'All'; // Track current selected category
let searchQuery = ''; // Track search query
let sortOption = ''; // Track sort option
let priceRange = ''; // Track price filter

// Function to display categories with badges
function displayCategories() {
    const categoriesList = document.getElementById('categoriesList');
    categoriesList.innerHTML = '';
    
    // Add "All" category first
    const allCategoryItem = document.createElement('li');
    allCategoryItem.className = 'category-item' + (currentCategory === 'All' ? ' active' : '');
    allCategoryItem.style.cursor = 'pointer';
    allCategoryItem.style.backgroundColor = currentCategory === 'All' ? '#f0f0f0' : '';
    
    const allNameSpan = document.createElement('span');
    allNameSpan.className = 'category-name';
    allNameSpan.textContent = 'All Categories';
    
    const allBadgesDiv = document.createElement('div');
    allBadgesDiv.className = 'category-badges';
    
    const allProductBadge = document.createElement('span');
    allProductBadge.className = 'badge bg-primary';
    allProductBadge.textContent = `Products: ${menuItems.length}`;
    
    allBadgesDiv.appendChild(allProductBadge);
    allCategoryItem.appendChild(allNameSpan);
    allCategoryItem.appendChild(allBadgesDiv);
    
    allCategoryItem.addEventListener('click', () => {
        currentCategory = 'All';
        displayMenuItems();
        displayCategories();
    });
    
    categoriesList.appendChild(allCategoryItem);
    
    categoryData.forEach(category => {
        const listItem = document.createElement('li');
        listItem.className = 'category-item' + (currentCategory === category.name ? ' active' : '');
        listItem.style.cursor = 'pointer';
        listItem.style.backgroundColor = currentCategory === category.name ? '#f0f0f0' : '';
        
        const nameSpan = document.createElement('span');
        nameSpan.className = 'category-name';
        nameSpan.textContent = category.name;
        
        const badgesDiv = document.createElement('div');
        badgesDiv.className = 'category-badges';
        
        const productBadge = document.createElement('span');
        productBadge.className = 'badge bg-primary';
        productBadge.textContent = `Products: ${category.productCount}`;
        
        const pendingBadge = document.createElement('span');
        pendingBadge.className = 'badge bg-warning text-dark';
        pendingBadge.textContent = `Pending: ${category.pending}`;
        
        const warningBadge = document.createElement('span');
        warningBadge.className = 'badge bg-danger';
        warningBadge.textContent = `Wishlist: ${category.wishlistCount}`;
        
        badgesDiv.appendChild(productBadge);
        badgesDiv.appendChild(pendingBadge);
        badgesDiv.appendChild(warningBadge);
        
        listItem.appendChild(nameSpan);
        listItem.appendChild(badgesDiv);
        
        listItem.addEventListener('click', () => {
            currentCategory = category.name;
            displayMenuItems();
            displayCategories();
        });
        
        categoriesList.appendChild(listItem);
    });
}

// Update the menu item display HTML template in displayMenuItems function
function displayMenuItems() {
    const menuContainer = document.getElementById('menuItems');
    menuContainer.innerHTML = '';
    
    // Get items based on current category
    let itemsToDisplay = currentCategory === 'All' 
        ? [...menuItems]
        : menuItems.filter(item => item.category === currentCategory);
    
    // Apply search filter
    if (searchQuery) {
        itemsToDisplay = itemsToDisplay.filter(item => 
            item.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
            item.desc.toLowerCase().includes(searchQuery.toLowerCase()) ||
            item.category.toLowerCase().includes(searchQuery.toLowerCase())
        );
    }
    
    // Apply price filter
    if (priceRange) {
        const [min, max] = priceRange.split('-').map(Number);
        itemsToDisplay = itemsToDisplay.filter(item => 
            item.price >= min && item.price <= max
        );
    }
    
    // Apply sorting
    if (sortOption) {
        switch(sortOption) {
            case 'name-asc':
                itemsToDisplay.sort((a, b) => a.name.localeCompare(b.name));
                break;
            case 'name-desc':
                itemsToDisplay.sort((a, b) => b.name.localeCompare(a.name));
                break;
            case 'price-asc':
                itemsToDisplay.sort((a, b) => a.price - b.price);
                break;
            case 'price-desc':
                itemsToDisplay.sort((a, b) => b.price - a.price);
                break;
            case 'rating-desc':
                itemsToDisplay.sort((a, b) => parseFloat(b.rating) - parseFloat(a.rating));
                break;
        }
    }
    
    // Update results info
    updateResultsInfo(itemsToDisplay.length);
    
    // If no items found, show message
    if (itemsToDisplay.length === 0) {
        menuContainer.innerHTML = '<div class="no-results">No items found matching your criteria. Try adjusting your filters.</div>';
        return;
    }
    
    // Group by category for display
    let categoriesToDisplay = currentCategory === 'All' 
        ? [...new Set(itemsToDisplay.map(item => item.category))]
        : [currentCategory];
    
    // Sort categories alphabetically
    categoriesToDisplay.sort();
    
    // Use DocumentFragment for better performance
    const fragment = document.createDocumentFragment();
    
    // Create a section for each category
    categoriesToDisplay.forEach(categoryName => {
        // Filter items for this category
        const categoryItems = itemsToDisplay.filter(item => item.category === categoryName);
        
        if (categoryItems.length === 0) return; // Skip empty categories
        
        // Create category section
        const categorySection = document.createElement('div');
        categorySection.className = 'category-section';
        
        // Add category heading
        const categoryHeading = document.createElement('h3');
        categoryHeading.className = 'category-heading';
        categoryHeading.textContent = categoryName;
        categorySection.appendChild(categoryHeading);
        
        // Create menu items grid for this category
        const categoryGrid = document.createElement('div');
        categoryGrid.className = 'category-items';
        
        // Build HTML string instead of createElement for better performance
        let itemsHTML = '';
        categoryItems.forEach(item => {
            const stars = '⭐'.repeat(Math.floor(item.rating)) + 
                         (item.rating % 1 >= 0.5 ? '½⭐' : '');
            const inWishlist = wishlist.includes(item.id);
                         
            itemsHTML += `
                <div class="menu-item">
                    <img src="${item.image}" alt="${item.name}" loading="lazy">
                    <div class="menu-item-details">
                        <div class="menu-item-name">${item.name}</div>
                        <div class="menu-item-desc">${item.desc}</div>
                        <div class="rating">
                            <span class="rating-stars">${stars}</span>
                            <span class="rating-count">(${item.rating} / 5 - ${item.ratingCount} reviews)</span>
                        </div>
                        <div class="menu-item-price">₱${item.price.toFixed(2)}</div>
                        <div class="item-actions">
                            <button class="add-to-cart" data-id="${item.id}">Add to Cart</button>
                            <button class="toggle-wishlist ${inWishlist ? 'in-wishlist' : ''}" data-id="${item.id}">
                                ${inWishlist ? '❤️' : '🤍'}
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        
        // Set innerHTML once instead of appending multiple times
        categoryGrid.innerHTML = itemsHTML;
        
        // Add the grid to the category section
        categorySection.appendChild(categoryGrid);
        
        // Add to fragment instead of directly to DOM
        fragment.appendChild(categorySection);
    });
    
    // Single DOM update - much faster!
    menuContainer.appendChild(fragment);
    
    // Event listeners are added using event delegation in DOMContentLoaded
    // No need to re-add them here - improves performance
}

// Update results info display
function updateResultsInfo(count) {
    const resultsInfo = document.getElementById('resultsInfo');
    if (!resultsInfo) return;
    
    if (searchQuery || priceRange || sortOption || currentCategory !== 'All') {
        resultsInfo.textContent = `Showing ${count} item${count !== 1 ? 's' : ''}`;
        resultsInfo.className = count === 0 ? 'results-info no-results' : 'results-info';
        resultsInfo.style.display = 'block';
    } else {
        resultsInfo.style.display = 'none';
    }
}

// Update category counts periodically to simulate dynamic data
function updateCategoryCounts() {
    categoryData.forEach(category => {
        // Randomly update counts to simulate database changes
        if (Math.random() > 0.7) {
            category.pending = Math.max(0, category.pending + Math.floor(Math.random() * 3) - 1);
        }
        
        // Update wishlist counts based on actual wishlist data
        category.wishlistCount = menuItems.filter(item => 
            item.category === category.name && wishlist.includes(item.id)
        ).length;
    });
    
    // Update the display
    displayCategories();
}

// Check if user is logged in
document.addEventListener('DOMContentLoaded', function() {
    const userString = localStorage.getItem('user');
    if (!userString) {
        window.location.href = 'index.html';
        return;
    }
    
    const user = JSON.parse(userString);
    document.getElementById('username').textContent = user.username;
    
    // Set the cart link with user ID
    document.getElementById('cartLink').href = `view_cart.php?user_id=${user.id}`;
    
    // Logout functionality
    document.getElementById('logout').addEventListener('click', function() {
        localStorage.removeItem('user');
        window.location.href = 'index.html';
    });
    
    // Load menu from database first, then display
    loadMenuFromDatabase();
    
    // Update category counts every 5 seconds
    setInterval(updateCategoryCounts, 5000);
    
    // Update cart count
    updateCartCount();

    // Initialize wishlist count
    updateWishlistCount();

    // Add wishlist dropdown toggle functionality
    const wishlistLink = document.getElementById('wishlistLink');
    const wishlistContent = document.getElementById('wishlistContent');

    wishlistLink.addEventListener('click', function(e) {
        e.preventDefault();
        wishlistContent.classList.toggle('show');
        displayWishlistDropdown();
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!wishlistLink.contains(e.target) && !wishlistContent.contains(e.target)) {
            wishlistContent.classList.remove('show');
        }
    });
    
    // Search functionality with debouncing
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            const value = e.target.value;
            searchTimeout = setTimeout(() => {
                searchQuery = value;
                // Use requestIdleCallback for better performance
                if ('requestIdleCallback' in window) {
                    requestIdleCallback(() => displayMenuItems());
                } else {
                    requestAnimationFrame(() => displayMenuItems());
                }
            }, 300); // Increased to 300ms for better performance
        });
    }
    
    // Sort filter
    const sortFilter = document.getElementById('sortFilter');
    if (sortFilter) {
        sortFilter.addEventListener('change', function(e) {
            sortOption = e.target.value;
            requestAnimationFrame(() => displayMenuItems());
        });
    }
    
    // Price filter
    const priceFilter = document.getElementById('priceFilter');
    if (priceFilter) {
        priceFilter.addEventListener('change', function(e) {
            priceRange = e.target.value;
            requestAnimationFrame(() => displayMenuItems());
        });
    }
    
    // Clear filters button
    const clearFiltersBtn = document.getElementById('clearFilters');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            // Reset all filters
            searchQuery = '';
            sortOption = '';
            priceRange = '';
            currentCategory = 'All';
            
            // Reset form controls
            if (searchInput) searchInput.value = '';
            if (sortFilter) sortFilter.value = '';
            if (priceFilter) priceFilter.value = '';
            
            // Refresh display
            displayCategories();
            displayMenuItems();
        });
    }
    
    // Event delegation for Add to Cart and Wishlist buttons (PERFORMANCE BOOST)
    // This handles all button clicks with a single event listener instead of hundreds
    document.getElementById('menuItems').addEventListener('click', function(e) {
        // Handle Add to Cart button clicks
        if (e.target.classList.contains('add-to-cart') || e.target.closest('.add-to-cart')) {
            e.preventDefault();
            const button = e.target.classList.contains('add-to-cart') ? e.target : e.target.closest('.add-to-cart');
            const foodId = button.getAttribute('data-id');
            if (foodId) {
                addToCart(foodId);
            }
        }
        
        // Handle Wishlist button clicks
        if (e.target.classList.contains('toggle-wishlist') || e.target.closest('.toggle-wishlist')) {
            e.preventDefault();
            const button = e.target.classList.contains('toggle-wishlist') ? e.target : e.target.closest('.toggle-wishlist');
            const foodId = button.getAttribute('data-id');
            if (foodId) {
                toggleWishlist(foodId);
            }
        }
    });
});

function addToCart(foodId) {
    const user = JSON.parse(localStorage.getItem('user'));
    
    fetch('add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `food_id=${foodId}&user_id=${user.id}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart count
            updateCartCount();
            // Show the modal instead of alert
            const myModal = new bootstrap.Modal(document.getElementById('myModal'));
            myModal.show();
        } else {
            alert('Error adding item to cart: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error adding to cart:', error);
        alert('Error adding item to cart. Please try again.');
    });
}

function updateCartCount() {
    const user = JSON.parse(localStorage.getItem('user'));
    
    // In a real app, this would be an AJAX call to a PHP script
    fetch(`get_cart_count.php?user_id=${user.id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('cartCount').textContent = data.count;
        })
        .catch(error => {
            console.error('Error getting cart count:', error);
        });
}

