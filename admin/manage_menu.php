<?php
session_start();

// Simple admin check - commented out for testing
// Uncomment in production
// if (!isset($_SESSION['user_id'])) {
//     header('Location: ../index.html');
//     exit;
// }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Management - Admin</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .admin-container {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .page-header {
            border-bottom: 3px solid #667eea;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }

        .btn-add {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }

        .btn-add:hover {
            background: linear-gradient(135deg, #5568d3 0%, #6a3f8f 100%);
            color: white;
        }

        .menu-item-card {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .menu-item-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .menu-item-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }

        .badge-category {
            font-size: 11px;
            padding: 5px 10px;
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .nav-pills .nav-link {
            border-radius: 10px;
            color: #667eea;
        }

        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="fas fa-utensils me-2"></i>Menu Management</h2>
                <p class="text-muted mb-0">Add, edit, or delete menu items</p>
            </div>
            <div>
                <button class="btn btn-add me-2" onclick="showAddModal()">
                    <i class="fas fa-plus me-2"></i>Add Menu Item
                </button>
                <a href="dashboard.php" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-chart-line me-2"></i>Dashboard
                </a>
                <a href="manage_orders.php" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-shopping-bag me-2"></i>Orders
                </a>
                <a href="manage_users.php" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-users me-2"></i>Users
                </a>
                <a href="../index.html" class="btn btn-outline-danger">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>

        <!-- Filter Tabs -->
        <ul class="nav nav-pills mb-4" id="categoryTabs">
            <li class="nav-item">
                <a class="nav-link active" data-category="all" href="#" onclick="filterByCategory('all', event)">All Items</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-category="Main Dishes" href="#" onclick="filterByCategory('Main Dishes', event)">Main Dishes</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-category="Appetizers" href="#" onclick="filterByCategory('Appetizers', event)">Appetizers</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-category="Salads" href="#" onclick="filterByCategory('Salads', event)">Salads</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-category="Desserts" href="#" onclick="filterByCategory('Desserts', event)">Desserts</a>
            </li>
        </ul>

        <!-- Search Bar -->
        <div class="mb-4">
            <input type="text" id="searchInput" class="form-control" placeholder="Search menu items...">
        </div>

        <!-- Menu Items List -->
        <div id="menuItemsList">
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 text-muted">Loading menu items...</p>
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal fade" id="menuItemModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Menu Item</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="menuItemForm">
                        <input type="hidden" id="itemId">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Item Name *</label>
                                <input type="text" class="form-control" id="itemName" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price (₱) *</label>
                                <input type="number" class="form-control" id="itemPrice" step="0.01" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="itemDescription" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category *</label>
                            <select class="form-control" id="itemCategory" required>
                                <option value="">Select Category</option>
                                <option value="Main Dishes">Main Dishes</option>
                                <option value="Appetizers">Appetizers</option>
                                <option value="Salads">Salads</option>
                                <option value="Desserts">Desserts</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image Upload *</label>
                            <input type="file" class="form-control" id="itemImage" accept="image/*">
                            <small class="text-muted">Upload an image file (JPG, PNG, GIF). Max size: 5MB</small>
                            <div id="imagePreview" class="mt-2" style="display: none;">
                                <img id="previewImg" src="" alt="Preview" style="max-width: 200px; border-radius: 8px;">
                                <p class="text-muted small mt-1" id="currentImagePath"></p>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveMenuItem()">Save Item</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="manage_menu.js"></script>
</body>

</html>