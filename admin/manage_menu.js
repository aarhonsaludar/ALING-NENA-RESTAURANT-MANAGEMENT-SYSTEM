// Menu Management JavaScript with AJAX
let allMenuItems = [];
let currentCategory = 'all';
let menuModal;

document.addEventListener('DOMContentLoaded', function() {
    menuModal = new bootstrap.Modal(document.getElementById('menuItemModal'));
    loadMenuItems();
    
    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function(e) {
        filterMenuItems(e.target.value);
    });
    
    // Image preview functionality
    document.getElementById('itemImage').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                document.getElementById('previewImg').src = event.target.result;
                document.getElementById('imagePreview').style.display = 'block';
                document.getElementById('currentImagePath').textContent = 'New file: ' + file.name;
            };
            reader.readAsDataURL(file);
        }
    });
});

// Load all menu items via AJAX
async function loadMenuItems() {
    try {
        const response = await fetch('api_menu_items.php?action=get_all');
        const result = await response.json();
        
        if (result.success) {
            allMenuItems = result.data;
            displayMenuItems(allMenuItems);
        } else {
            showError('Failed to load menu items: ' + result.message);
        }
    } catch (error) {
        showError('Error loading menu items: ' + error.message);
    }
}

// Display menu items
function displayMenuItems(items) {
    const container = document.getElementById('menuItemsList');
    
    if (items.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">No menu items found</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    items.forEach(item => {
        // Use category from database directly
        const category = item.category || 'Other';
        html += `
            <div class="menu-item-card" data-category="${category}">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <img src="../${item.image_url}" class="menu-item-img" alt="${item.name}" 
                             onerror="this.src='../img/placeholder.jpg'">
                    </div>
                    <div class="col">
                        <h5 class="mb-1">${item.name}</h5>
                        <p class="text-muted mb-1 small">${item.description || 'No description'}</p>
                        <span class="badge badge-category bg-info">${category}</span>
                    </div>
                    <div class="col-auto text-end">
                        <h5 class="text-primary mb-2">₱${parseFloat(item.price).toFixed(2)}</h5>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editMenuItem(${item.id})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteMenuItem(${item.id}, '${item.name}')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Filter by category
function filterByCategory(category, event) {
    if (event) {
        event.preventDefault();
        // Update active tab
        document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
        event.target.classList.add('active');
    }
    
    currentCategory = category;
    const searchTerm = document.getElementById('searchInput').value;
    filterMenuItems(searchTerm);
}

// Filter menu items by search and category
function filterMenuItems(searchTerm) {
    let filtered = allMenuItems;
    
    // Filter by category
    if (currentCategory !== 'all') {
        filtered = filtered.filter(item => {
            return item.category === currentCategory;
        });
    }
    
    // Filter by search term
    if (searchTerm) {
        const term = searchTerm.toLowerCase();
        filtered = filtered.filter(item => 
            item.name.toLowerCase().includes(term) ||
            (item.description && item.description.toLowerCase().includes(term))
        );
    }
    
    displayMenuItems(filtered);
}

// Show add modal
function showAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Menu Item';
    document.getElementById('menuItemForm').reset();
    document.getElementById('itemId').value = '';
    document.getElementById('imagePreview').style.display = 'none';
    document.getElementById('itemImage').required = true;
    menuModal.show();
}

// Edit menu item
async function editMenuItem(id) {
    const item = allMenuItems.find(i => i.id == id);
    if (!item) return;
    
    document.getElementById('modalTitle').textContent = 'Edit Menu Item';
    document.getElementById('itemId').value = item.id;
    document.getElementById('itemName').value = item.name;
    document.getElementById('itemPrice').value = item.price;
    document.getElementById('itemDescription').value = item.description || '';
    document.getElementById('itemCategory').value = item.category || '';
    
    // Show current image
    if (item.image_url) {
        document.getElementById('previewImg').src = '../' + item.image_url;
        document.getElementById('currentImagePath').textContent = 'Current: ' + item.image_url;
        document.getElementById('imagePreview').style.display = 'block';
    }
    
    // Make file input optional when editing
    document.getElementById('itemImage').required = false;
    
    menuModal.show();
}

// Save menu item (Add or Update) via AJAX with file upload
async function saveMenuItem() {
    const id = document.getElementById('itemId').value;
    const name = document.getElementById('itemName').value.trim();
    const price = document.getElementById('itemPrice').value;
    const description = document.getElementById('itemDescription').value.trim();
    const category = document.getElementById('itemCategory').value;
    const imageFile = document.getElementById('itemImage').files[0];
    
    if (!name || !price || !category) {
        alert('Please fill in all required fields');
        return;
    }
    
    // Validate image for new items
    if (!id && !imageFile) {
        alert('Please upload an image');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', id ? 'update' : 'add');
    if (id) formData.append('id', id);
    formData.append('name', name);
    formData.append('price', price);
    formData.append('description', description);
    formData.append('category', category);
    
    // Add image file if selected
    if (imageFile) {
        formData.append('image', imageFile);
    }
    
    try {
        const response = await fetch('api_menu_items.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showSuccess(id ? 'Menu item updated successfully!' : 'Menu item added successfully!');
            menuModal.hide();
            loadMenuItems(); // Reload list
        } else {
            showError('Failed to save menu item: ' + result.message);
        }
    } catch (error) {
        showError('Error saving menu item: ' + error.message);
    }
}

// Delete menu item via AJAX
async function deleteMenuItem(id, name) {
    if (!confirm(`Are you sure you want to delete "${name}"?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);
    
    try {
        const response = await fetch('api_menu_items.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showSuccess('Menu item deleted successfully!');
            loadMenuItems(); // Reload list
        } else {
            showError('Failed to delete menu item: ' + result.message);
        }
    } catch (error) {
        showError('Error deleting menu item: ' + error.message);
    }
}

// Utility functions
function showSuccess(message) {
    // You can replace this with a better notification system
    alert(message);
}

function showError(message) {
    alert('Error: ' + message);
    console.error(message);
}
