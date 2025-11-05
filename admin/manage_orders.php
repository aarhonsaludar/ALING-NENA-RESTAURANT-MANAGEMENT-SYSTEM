<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Admin</title>
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

        .order-card {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .order-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .status-badge {
            font-size: 12px;
            padding: 5px 12px;
            border-radius: 20px;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background: #cfe2ff;
            color: #084298;
        }

        .status-preparing {
            background: #e7d4ff;
            color: #6f42c1;
        }

        .status-out_for_delivery {
            background: #cff4fc;
            color: #055160;
        }

        .status-delivered {
            background: #d1e7dd;
            color: #0a3622;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #58151c;
        }

        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="fas fa-shopping-bag me-2"></i>Order Management</h2>
                <p class="text-muted mb-0">View and manage all orders</p>
            </div>
            <div>
                <a href="dashboard.php" class="btn btn-outline-primary me-2">
                    <i class="fas fa-chart-line me-2"></i>Dashboard
                </a>
                <a href="manage_menu.php" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-utensils me-2"></i>Menu
                </a>
                <a href="manage_users.php" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-users me-2"></i>Users
                </a>
                <a href="../index.html" class="btn btn-outline-danger">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-section">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small">Filter by Status</label>
                    <select id="statusFilter" class="form-select" onchange="loadOrders()">
                        <option value="all">All Orders</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="preparing">Preparing</option>
                        <option value="out_for_delivery">Out for Delivery</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Payment Status</label>
                    <select id="paymentFilter" class="form-select" onchange="loadOrders()">
                        <option value="all">All</option>
                        <option value="paid">Paid</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Search</label>
                    <input type="text" id="searchInput" class="form-control" placeholder="Order number, customer name...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">&nbsp;</label>
                    <button class="btn btn-secondary w-100" onclick="loadOrders()">
                        <i class="fas fa-sync me-2"></i>Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- Orders List -->
        <div id="ordersList">
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 text-muted">Loading orders...</p>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Order Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    Loading...
                </div>
            </div>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="manage_orders.js"></script>
</body>

</html>