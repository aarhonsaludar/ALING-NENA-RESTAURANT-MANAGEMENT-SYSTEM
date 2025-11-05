// Check if user is logged in
document.addEventListener('DOMContentLoaded', function() {
    const userString = localStorage.getItem('user');
    if (!userString) {
        window.location.href = 'index.html';
        return;
    }
    
    const user = JSON.parse(userString);
    loadUserProfile(user);
    loadOrderHistory(user.id);
    loadAddresses(user.id);
    loadWishlist();
    
    // Tab switching
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');
            switchTab(tabName);
        });
    });
    
    // Logout
    document.getElementById('logoutBtn').addEventListener('click', function(e) {
        e.preventDefault();
        logout();
    });
});

function loadUserProfile(user) {
    document.getElementById('profileName').textContent = user.full_name || user.username;
    document.getElementById('profileUsername').textContent = user.username;
    document.getElementById('profileEmail').textContent = user.email || 'Not provided';
    document.getElementById('profilePhone').textContent = user.phone || 'Not provided';
    
    // Set avatar initials
    const initials = user.full_name 
        ? user.full_name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()
        : user.username.substring(0, 2).toUpperCase();
    document.getElementById('profileAvatar').textContent = initials;
    
    // Set member since (would come from database in real app)
    const memberSince = new Date().toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long' 
    });
    document.getElementById('profileMemberSince').textContent = memberSince;
}

function loadOrderHistory(userId) {
    fetch(`get_order_history.php?user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            const ordersContent = document.getElementById('ordersContent');
            
            if (data.success && data.orders && data.orders.length > 0) {
                ordersContent.innerHTML = '';
                data.orders.forEach(order => {
                    ordersContent.innerHTML += createOrderCard(order);
                });
            } else {
                ordersContent.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">📦</div>
                        <h3>No Orders Yet</h3>
                        <p>Start ordering to see your order history here!</p>
                        <a href="badges_lab.html" class="btn-add-address" style="display: inline-block; text-decoration: none; margin-top: 20px;">Browse Menu</a>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading order history:', error);
        });
}

function createOrderCard(order) {
    const statusClass = order.order_status.toLowerCase();
    const orderDate = new Date(order.created_at).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    
    let itemsHtml = '';
    if (order.items && order.items.length > 0) {
        order.items.forEach(item => {
            itemsHtml += `
                <div class="order-item">
                    <span>${item.quantity}x ${item.food_name}</span>
                    <span>₱${parseFloat(item.subtotal).toFixed(2)}</span>
                </div>
            `;
        });
    }
    
    return `
        <div class="order-card">
            <div class="order-header">
                <div>
                    <div class="order-number">Order #${order.order_number}</div>
                    <small style="color: #999;">${orderDate}</small>
                </div>
                <span class="order-status ${statusClass}">${order.order_status.toUpperCase()}</span>
            </div>
            <div class="order-items">
                ${itemsHtml}
            </div>
            <div class="order-total">
                <span>Total:</span>
                <span>₱${parseFloat(order.total_amount).toFixed(2)}</span>
            </div>
        </div>
    `;
}

function loadAddresses(userId) {
    fetch(`get_user_addresses.php?user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            const addressesContent = document.getElementById('addressesContent');
            
            if (data.success && data.addresses && data.addresses.length > 0) {
                addressesContent.innerHTML = '';
                data.addresses.forEach(address => {
                    addressesContent.innerHTML += createAddressCard(address);
                });
            } else {
                addressesContent.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">📍</div>
                        <h3>No Saved Addresses</h3>
                        <p>Add your delivery addresses for faster checkout!</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading addresses:', error);
        });
}

function createAddressCard(address) {
    const defaultBadge = address.is_default == 1 ? '<span class="address-default">Default</span>' : '';
    
    return `
        <div class="address-card">
            ${defaultBadge}
            <div class="address-label">${address.address_label}</div>
            <p style="margin: 10px 0 5px 0; color: #333;">${address.street_address}</p>
            <p style="margin: 0; color: #666;">${address.city}, ${address.postal_code}</p>
        </div>
    `;
}

function loadWishlist() {
    const wishlist = JSON.parse(localStorage.getItem('wishlist')) || [];
    const wishlistContent = document.getElementById('wishlistContent');
    
    if (wishlist.length === 0) {
        wishlistContent.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">❤️</div>
                <h3>Your Wishlist is Empty</h3>
                <p>Browse our menu and add items to your wishlist!</p>
                <a href="badges_lab.html" class="btn-add-address" style="display: inline-block; text-decoration: none; margin-top: 20px;">Browse Menu</a>
            </div>
        `;
        return;
    }
    
    // Fetch menu items to display wishlist
    fetch('get_menu.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.items) {
                const wishlistItems = data.items.filter(item => wishlist.includes(item.id));
                
                if (wishlistItems.length > 0) {
                    wishlistContent.innerHTML = '<div class="wishlist-grid">';
                    wishlistItems.forEach(item => {
                        wishlistContent.innerHTML += `
                            <div class="wishlist-item">
                                <img src="${item.image_url}" alt="${item.name}">
                                <div class="wishlist-item-details">
                                    <div class="wishlist-item-name">${item.name}</div>
                                    <p style="color: #666; font-size: 13px; margin: 5px 0;">${item.description}</p>
                                    <div class="wishlist-item-price">₱${parseFloat(item.price).toFixed(2)}</div>
                                    <button onclick="removeFromWishlist(${item.id})" style="background: #dc3545; color: white; border: none; padding: 8px 16px; border-radius: 5px; margin-top: 10px; cursor: pointer; width: 100%;">Remove</button>
                                </div>
                            </div>
                        `;
                    });
                    wishlistContent.innerHTML += '</div>';
                }
            }
        })
        .catch(error => {
            console.error('Error loading wishlist items:', error);
        });
}

function removeFromWishlist(itemId) {
    let wishlist = JSON.parse(localStorage.getItem('wishlist')) || [];
    wishlist = wishlist.filter(id => id !== itemId);
    localStorage.setItem('wishlist', JSON.stringify(wishlist));
    loadWishlist();
}

function switchTab(tabName) {
    // Update tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
    
    // Update tab content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    document.getElementById(tabName).classList.add('active');
}

function addNewAddress() {
    alert('Add address functionality would open a modal form here. This is a demo version.');
}

function logout() {
    fetch('logout.php')
        .then(response => response.json())
        .then(data => {
            localStorage.removeItem('user');
            localStorage.removeItem('wishlist');
            window.location.href = 'index.html';
        })
        .catch(error => {
            console.error('Error during logout:', error);
            localStorage.removeItem('user');
            localStorage.removeItem('wishlist');
            window.location.href = 'index.html';
        });
}
