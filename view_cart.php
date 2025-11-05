<?php
require_once('connect.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - Aling Nena Restaurant</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #FFE5D4 0%, #FFD5C2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .cart-header {
            background: linear-gradient(135deg, #00b894 0%, #00cec9 100%);
            color: white;
            padding: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
        }

        .cart-count {
            background: rgba(255, 255, 255, 0.3);
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
        }

        .cart-content {
            padding: 30px;
        }

        .cart-item {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px;
            border: 2px solid #f0f0f0;
            border-radius: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            background: white;
        }

        .cart-item:hover {
            border-color: #00b894;
            box-shadow: 0 5px 15px rgba(0, 184, 148, 0.1);
            transform: translateY(-2px);
        }

        .item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
            flex-shrink: 0;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .item-price {
            color: #00b894;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .qty-btn {
            width: 35px;
            height: 35px;
            border: 2px solid #00b894;
            background: white;
            color: #00b894;
            border-radius: 8px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qty-btn:hover {
            background: #00b894;
            color: white;
            transform: scale(1.1);
        }

        .qty-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .qty-input {
            width: 60px;
            text-align: center;
            font-size: 1rem;
            font-weight: 600;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 8px;
        }

        .item-total {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c3e50;
            min-width: 100px;
            text-align: right;
        }

        .remove-btn {
            background: linear-gradient(135deg, #ff6b6b 0%, #ff8787 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .remove-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
        }

        .cart-summary {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 15px;
            margin-top: 30px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 1rem;
        }

        .summary-row.total {
            border-top: 2px solid #dee2e6;
            margin-top: 10px;
            padding-top: 15px;
            font-size: 1.5rem;
            font-weight: 700;
            color: #00b894;
        }

        .cart-actions {
            display: flex;
            gap: 15px;
            margin-top: 25px;
            flex-wrap: wrap;
        }

        .btn-action {
            flex: 1;
            min-width: 200px;
            padding: 15px 30px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-continue {
            background: white;
            color: #00b894;
            border: 2px solid #00b894;
        }

        .btn-continue:hover {
            background: #00b894;
            color: white;
            transform: translateY(-2px);
        }

        .btn-clear {
            background: linear-gradient(135deg, #ff6b6b 0%, #ff8787 100%);
            color: white;
        }

        .btn-clear:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
        }

        .btn-checkout {
            background: linear-gradient(135deg, #00b894 0%, #00cec9 100%);
            color: white;
        }

        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 184, 148, 0.3);
        }

        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-cart-icon {
            font-size: 5rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-cart h2 {
            color: #7f8c8d;
            margin-bottom: 20px;
        }

        .loading {
            text-align: center;
            padding: 60px;
            font-size: 1.2rem;
            color: #7f8c8d;
        }

        @media (max-width: 768px) {
            .cart-item {
                flex-direction: column;
                text-align: center;
            }

            .item-image {
                width: 100%;
                max-width: 200px;
                height: 200px;
            }

            .cart-actions {
                flex-direction: column;
            }

            .btn-action {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="cart-container">
        <div class="cart-header">
            <h1>🛒 Shopping Cart</h1>
            <div class="cart-count" id="cartCount">0 items</div>
        </div>

        <div class="cart-content">
            <div id="loadingMessage" class="loading">
                Loading your cart...
            </div>

            <div id="cartItems" style="display: none;">
                <!-- Cart items will be inserted here -->
            </div>

            <div id="cartSummary" style="display: none;">
                <div class="cart-summary">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span id="subtotal">₱0.00</span>
                    </div>
                    <div class="summary-row">
                        <span>Items:</span>
                        <span id="itemCount">0</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total:</span>
                        <span id="grandTotal">₱0.00</span>
                    </div>
                </div>

                <div class="cart-actions">
                    <a href="badges_lab.html" class="btn-action btn-continue">Continue Shopping</a>
                    <button onclick="clearCart()" class="btn-action btn-clear">Clear Cart</button>
                    <button onclick="proceedToCheckout()" class="btn-action btn-checkout">Proceed to Checkout</button>
                </div>
            </div>

            <div id="emptyCart" style="display: none;">
                <div class="empty-cart">
                    <div class="empty-cart-icon">🛒</div>
                    <h2>Your cart is empty</h2>
                    <p style="color: #7f8c8d; margin-bottom: 30px;">Add some delicious items to get started!</p>
                    <a href="badges_lab.html" class="btn-action btn-continue" style="max-width: 300px; margin: 0 auto;">Browse Menu</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentUserId = null;
        let cartData = [];

        // Check if user is logged in
        document.addEventListener('DOMContentLoaded', function() {
            const userString = localStorage.getItem('user');
            if (!userString) {
                window.location.href = 'index.html';
                return;
            }

            const user = JSON.parse(userString);
            currentUserId = user.id;

            loadCart();
        });

        function loadCart() {
            fetch(`get_cart_data.php?user_id=${currentUserId}`)
                .then(response => response.json())
                .then(data => {
                    cartData = data.items || [];
                    displayCart();
                })
                .catch(error => {
                    console.error('Error loading cart:', error);
                    document.getElementById('loadingMessage').innerHTML =
                        '<div class="empty-cart"><h2>Error loading cart</h2><p>Please try again.</p></div>';
                });
        }

        function displayCart() {
            document.getElementById('loadingMessage').style.display = 'none';

            if (cartData.length === 0) {
                document.getElementById('emptyCart').style.display = 'block';
                document.getElementById('cartItems').style.display = 'none';
                document.getElementById('cartSummary').style.display = 'none';
                document.getElementById('cartCount').textContent = '0 items';
                return;
            }

            // Show cart items and summary
            document.getElementById('cartItems').style.display = 'block';
            document.getElementById('cartSummary').style.display = 'block';
            document.getElementById('emptyCart').style.display = 'none';

            // Build cart items HTML
            let cartHTML = '';
            let totalItems = 0;
            let grandTotal = 0;

            cartData.forEach(item => {
                totalItems += parseInt(item.quantity);
                grandTotal += parseFloat(item.total);

                cartHTML += `
                    <div class="cart-item" data-cart-id="${item.cart_id}">
                        <img src="${item.image_url}" alt="${item.name}" class="item-image" onerror="this.src='img/placeholder.jpg'">
                        <div class="item-details">
                            <div class="item-name">${item.name}</div>
                            <div class="item-price">₱${parseFloat(item.price).toFixed(2)} each</div>
                        </div>
                        <div class="quantity-controls">
                            <button class="qty-btn" onclick="updateQuantity(${item.cart_id}, ${item.quantity - 1})" ${item.quantity <= 1 ? 'disabled' : ''}>−</button>
                            <input type="number" class="qty-input" value="${item.quantity}" min="1" max="99" 
                                   onchange="updateQuantity(${item.cart_id}, this.value)" readonly>
                            <button class="qty-btn" onclick="updateQuantity(${item.cart_id}, ${item.quantity + 1})" ${item.quantity >= 99 ? 'disabled' : ''}>+</button>
                        </div>
                        <div class="item-total">₱${parseFloat(item.total).toFixed(2)}</div>
                        <button class="remove-btn" onclick="removeItem(${item.cart_id})">Remove</button>
                    </div>
                `;
            });

            document.getElementById('cartItems').innerHTML = cartHTML;
            document.getElementById('cartCount').textContent = `${totalItems} item${totalItems !== 1 ? 's' : ''}`;
            document.getElementById('subtotal').textContent = `₱${grandTotal.toFixed(2)}`;
            document.getElementById('itemCount').textContent = `${totalItems} item${totalItems !== 1 ? 's' : ''}`;
            document.getElementById('grandTotal').textContent = `₱${grandTotal.toFixed(2)}`;
        }

        function updateQuantity(cartId, newQuantity) {
            newQuantity = parseInt(newQuantity);

            if (isNaN(newQuantity) || newQuantity < 1) {
                newQuantity = 1;
            }

            if (newQuantity > 99) {
                newQuantity = 99;
            }

            const itemElement = document.querySelector(`[data-cart-id="${cartId}"]`);
            if (itemElement) {
                itemElement.style.opacity = '0.6';
                itemElement.style.pointerEvents = 'none';
            }

            fetch('update_cart_quantity.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `cart_id=${cartId}&quantity=${newQuantity}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadCart();
                        updateHeaderCartCount();
                    } else {
                        alert('Error updating quantity: ' + data.message);
                        if (itemElement) {
                            itemElement.style.opacity = '1';
                            itemElement.style.pointerEvents = 'auto';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error updating quantity:', error);
                    alert('Error updating quantity. Please try again.');
                    if (itemElement) {
                        itemElement.style.opacity = '1';
                        itemElement.style.pointerEvents = 'auto';
                    }
                });
        }

        function removeItem(cartId) {
            if (!confirm('Remove this item from cart?')) {
                return;
            }

            const itemElement = document.querySelector(`[data-cart-id="${cartId}"]`);
            if (itemElement) {
                itemElement.style.opacity = '0.6';
                itemElement.style.pointerEvents = 'none';
            }

            fetch('remove_from_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `cart_id=${cartId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadCart();
                        updateHeaderCartCount();
                    } else {
                        alert('Error removing item: ' + data.message);
                        if (itemElement) {
                            itemElement.style.opacity = '1';
                            itemElement.style.pointerEvents = 'auto';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error removing item:', error);
                    alert('Error removing item. Please try again.');
                    if (itemElement) {
                        itemElement.style.opacity = '1';
                        itemElement.style.pointerEvents = 'auto';
                    }
                });
        }

        function clearCart() {
            if (!confirm('Are you sure you want to clear your entire cart?')) {
                return;
            }

            fetch('clear_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `user_id=${currentUserId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadCart();
                        updateHeaderCartCount();
                    } else {
                        alert('Error clearing cart: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error clearing cart:', error);
                    alert('Error clearing cart. Please try again.');
                });
        }

        function proceedToCheckout() {
            if (cartData.length === 0) {
                alert('Your cart is empty!');
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'receipt.php';

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'user_id';
            input.value = currentUserId;

            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }

        function updateHeaderCartCount() {
            fetch(`get_cart_count.php?user_id=${currentUserId}`)
                .then(response => response.json())
                .then(data => {
                    localStorage.setItem('cartCount', data.count);
                })
                .catch(error => {
                    console.error('Error updating cart count:', error);
                });
        }
    </script>
</body>

</html>