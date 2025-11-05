# Aling Nena's Kitchen - Design System Guide

## 🎨 Filipino-Themed Design System

### Color Palette

**Primary Colors (Filipino Flag)**

- Filipino Red: `#c8102e`
- Filipino Yellow: `#fcd116`
- Filipino Blue: `#0038a8`

**Warm Earth Tones**

- Warm Red: `#d32f2f`
- Warm Orange: `#ff6b35`
- Warm Yellow: `#f9ca24`
- Warm Brown: `#8d6e63`
- Bamboo Green: `#6d8b74`

**Accent Colors**

- Sunset Orange: `#ff7043`
- Mango Yellow: `#ffa726`
- Ube Purple: `#7b4b94`
- Pandan Green: `#66bb6a`

## 🚀 Quick Start

### 1. Include Files in Your HTML

```html
<!-- In <head> section -->
<link rel="stylesheet" href="css/filipino-theme.css" />
<link
  rel="stylesheet"
  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
/>

<!-- Before </body> closing tag -->
<script src="js/ux-utilities.js"></script>
```

## 📢 Toast Notifications

### Usage Examples

```javascript
// Success notification
toast.success("Item added to cart!");

// Error notification
toast.error("Failed to process your request");

// Warning notification
toast.warning("Your session is about to expire");

// Info notification
toast.info("New menu items available!");

// Custom duration (default is 4000ms)
toast.success("Order placed successfully!", 6000);
```

### Real-World Examples

```javascript
// Adding to cart
function addToCart(itemId) {
  fetch("add_to_cart.php", {
    method: "POST",
    body: JSON.stringify({ item_id: itemId }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        toast.success("Added to your cart!");
        updateCartBadge(data.cart_count);
      } else {
        toast.error(data.message);
      }
    });
}

// Removing from cart
function removeFromCart(itemId) {
  toast.info("Removing item...");
  // ... your code
}
```

## ❓ Confirmation Dialogs

### Basic Usage

```javascript
// Simple confirmation
const confirmed = await showConfirmDialog({
  title: "Delete Item?",
  message: "Are you sure you want to remove this item from your cart?",
  confirmText: "Yes, Remove",
  cancelText: "Cancel",
  type: "warning",
});

if (confirmed) {
  // User clicked confirm
  deleteItem();
}
```

### Danger Confirmation

```javascript
const confirmed = await showConfirmDialog({
  title: "Clear Cart?",
  message:
    "This will remove all items from your cart. This action cannot be undone.",
  confirmText: "Clear Cart",
  cancelText: "Keep Items",
  type: "danger",
});

if (confirmed) {
  clearCart();
}
```

### Real-World Example

```javascript
async function deleteMenuItem(id, name) {
  const confirmed = await showConfirmDialog({
    title: "Delete Menu Item?",
    message: `Are you sure you want to delete "${name}"? This cannot be undone.`,
    confirmText: "Delete",
    cancelText: "Cancel",
    type: "danger",
  });

  if (confirmed) {
    loading.show("Deleting...");
    // Perform delete
    await fetch("api_menu_items.php", {
      method: "POST",
      body: new FormData(),
    });
    loading.hide();
    toast.success("Menu item deleted!");
  }
}
```

## ⏳ Loading Indicators

### Full Screen Loading

```javascript
// Show loading
loading.show("Processing your order...");

// Hide loading
loading.hide();

// Example with async operation
async function placeOrder() {
  loading.show("Placing your order...");

  try {
    const response = await fetch("place_order.php", {
      method: "POST",
      body: orderData,
    });
    const result = await response.json();

    loading.hide();

    if (result.success) {
      toast.success("Order placed successfully!");
    } else {
      toast.error(result.message);
    }
  } catch (error) {
    loading.hide();
    toast.error("An error occurred. Please try again.");
  }
}
```

### Button Loading State

```html
<button class="btn-filipino" onclick="handleClick(this)">Submit Order</button>
```

```javascript
async function handleClick(button) {
  button.classList.add("btn-loading");
  button.disabled = true;

  // Perform async operation
  await submitOrder();

  button.classList.remove("btn-loading");
  button.disabled = false;
}
```

## 📝 Form Validation

### HTML Setup

```html
<form id="orderForm">
  <div class="form-group">
    <label>Full Name</label>
    <input type="text" name="full_name" class="form-control" required />
    <div class="error-message-field"></div>
  </div>

  <div class="form-group">
    <label>Email</label>
    <input type="email" name="email" class="form-control" required />
    <div class="error-message-field"></div>
  </div>

  <div class="form-group">
    <label>Phone</label>
    <input type="tel" name="phone" class="form-control" required />
    <div class="error-message-field"></div>
  </div>

  <button type="submit" class="btn-filipino">Submit</button>
</form>
```

### JavaScript Validation

```javascript
const form = document.getElementById("orderForm");
const validator = new FormValidator(form);

form.addEventListener("submit", function (e) {
  e.preventDefault();

  const isValid = validator.validate({
    full_name: [
      { type: "required", message: "Name is required" },
      { type: "min", value: 3, message: "Name must be at least 3 characters" },
    ],
    email: [
      { type: "required", message: "Email is required" },
      { type: "email", message: "Please enter a valid email" },
    ],
    phone: [
      { type: "required", message: "Phone is required" },
      { type: "pattern", value: /^[0-9-]+$/, message: "Invalid phone format" },
    ],
  });

  if (isValid) {
    // Submit form
    submitForm();
  } else {
    toast.error("Please fix the errors in the form");
  }
});
```

## 🛒 Cart & Wishlist Badges

### HTML Structure

```html
<a href="view_cart.php" class="cart-badge">
  <i class="fas fa-shopping-cart"></i>
  <span class="badge-count">0</span>
</a>

<a href="wishlist.php" class="wishlist-badge">
  <i class="fas fa-heart"></i>
  <span class="badge-count">0</span>
</a>
```

### Update Badge Count

```javascript
// Update cart badge
updateCartBadge(5); // Shows "5"

// Update wishlist badge
updateWishlistBadge(3); // Shows "3"

// Example in add to cart
function addToCart(itemId) {
  fetch("add_to_cart.php", {
    method: "POST",
    body: JSON.stringify({ item_id: itemId }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        toast.success("Added to cart!");
        updateCartBadge(data.cart_count); // Update badge
      }
    });
}
```

## 🗑️ Empty States

### HTML Structure

```html
<div class="empty-state">
  <div class="empty-state-icon">
    <i class="fas fa-shopping-cart"></i>
  </div>
  <h3 class="empty-state-title">Your cart is empty</h3>
  <p class="empty-state-message">
    Looks like you haven't added any items to your cart yet. Start shopping to
    fill it up!
  </p>
  <a href="badges_lab.html" class="empty-state-button">
    <i class="fas fa-utensils"></i>
    Browse Menu
  </a>
</div>
```

### Different Empty States

**Empty Cart**

```html
<div class="empty-state">
  <div class="empty-state-icon"><i class="fas fa-shopping-cart"></i></div>
  <h3 class="empty-state-title">Your cart is empty</h3>
  <p class="empty-state-message">Start adding delicious food to your cart!</p>
  <a href="badges_lab.html" class="empty-state-button">Browse Menu</a>
</div>
```

**No Orders**

```html
<div class="empty-state">
  <div class="empty-state-icon"><i class="fas fa-receipt"></i></div>
  <h3 class="empty-state-title">No orders yet</h3>
  <p class="empty-state-message">You haven't placed any orders yet.</p>
  <a href="badges_lab.html" class="empty-state-button">Order Now</a>
</div>
```

**Empty Wishlist**

```html
<div class="empty-state">
  <div class="empty-state-icon"><i class="fas fa-heart"></i></div>
  <h3 class="empty-state-title">Your wishlist is empty</h3>
  <p class="empty-state-message">Save your favorite dishes for later!</p>
  <a href="badges_lab.html" class="empty-state-button">Browse Menu</a>
</div>
```

## 🖨️ Print Receipt

### HTML Structure

```html
<div class="receipt-container receipt-print">
  <!-- Receipt content -->
  <h2>Order Receipt</h2>
  <!-- ... -->
</div>

<button onclick="printReceipt()" class="btn-filipino no-print">
  <i class="fas fa-print"></i>
  Print Receipt
</button>
```

### JavaScript

```javascript
// Simple print
printReceipt();

// With confirmation
async function handlePrint() {
  const confirmed = await showConfirmDialog({
    title: "Print Receipt?",
    message: "Make sure your printer is ready.",
    confirmText: "Print",
    cancelText: "Cancel",
    type: "info",
  });

  if (confirmed) {
    printReceipt();
  }
}
```

## 🎨 Filipino Buttons

### HTML Examples

```html
<!-- Primary Button -->
<button class="btn-filipino">
  <i class="fas fa-shopping-cart"></i>
  Add to Cart
</button>

<!-- Outline Button -->
<button class="btn-filipino-outline">
  <i class="fas fa-heart"></i>
  Add to Wishlist
</button>

<!-- Link as Button -->
<a href="badges_lab.html" class="btn-filipino">
  <i class="fas fa-utensils"></i>
  View Menu
</a>
```

## 🎭 Filipino Patterns

### Add Background Pattern

```html
<div class="filipino-pattern-bg">
  <!-- Your content -->
</div>

<!-- Or Banig Pattern -->
<div class="banig-pattern">
  <!-- Your content -->
</div>
```

## 🛠️ Utility Functions

### Format Currency

```javascript
formatCurrency(150.5); // Returns: "₱150.50"
formatCurrency(1234.56); // Returns: "₱1,234.56"
```

### Copy to Clipboard

```javascript
copyToClipboard("Order #12345");
// Shows success toast
```

### Smooth Scroll

```javascript
smoothScrollTo("menu-section");
```

### Debounce

```javascript
const searchMenu = debounce((searchTerm) => {
  // Perform search
  console.log("Searching for:", searchTerm);
}, 500);

// Use in input event
document.getElementById("search").addEventListener("input", (e) => {
  searchMenu(e.target.value);
});
```

## 📱 Responsive Design

All components are fully responsive and mobile-friendly:

- Toast notifications adapt to screen size
- Confirmation dialogs stack buttons on mobile
- Empty states scale appropriately
- Loading overlays work on all devices

## 🎨 CSS Variables

Customize colors easily:

```css
:root {
  --warm-orange: #ff6b35;
  --warm-red: #d32f2f;
  /* ... customize as needed */
}
```

## 💡 Best Practices

1. **Always show feedback**: Use toast notifications for user actions
2. **Confirm destructive actions**: Use confirmation dialogs for delete/clear operations
3. **Show loading states**: Use loading indicators for async operations
4. **Validate forms**: Use FormValidator for all user input
5. **Handle empty states**: Show helpful empty states with call-to-action
6. **Make receipts printable**: Use print-friendly CSS classes

## 🚀 Complete Example

```javascript
// Complete flow for adding item to cart
async function addToCart(itemId, itemName) {
  // Show loading
  const button = event.target;
  button.classList.add("btn-loading");

  try {
    const response = await fetch("add_to_cart.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ item_id: itemId }),
    });

    const data = await response.json();

    if (data.success) {
      // Show success toast
      toast.success(`${itemName} added to cart!`);

      // Update cart badge
      updateCartBadge(data.cart_count);
    } else {
      // Show error toast
      toast.error(data.message || "Failed to add item");
    }
  } catch (error) {
    toast.error("An error occurred. Please try again.");
  } finally {
    // Remove loading state
    button.classList.remove("btn-loading");
  }
}
```

---

**Enjoy your beautiful Filipino-themed restaurant management system! 🇵🇭🍽️**
