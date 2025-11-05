<?php
session_start();

// Simple admin check - commented out for testing
// Uncomment this in production to require login
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
    <title>Admin Dashboard - Aling Nena</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #ff6b6b;
            --secondary-color: #4ecdc4;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #3498db;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .dashboard-container {
            padding: 20px;
        }

        .dashboard-header {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .dashboard-header h1 {
            color: var(--dark-color);
            margin: 0;
            font-weight: 700;
        }

        .dashboard-header .subtitle {
            color: #7f8c8d;
            margin-top: 5px;
        }

        .metric-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }

        .metric-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .metric-card .icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 15px;
        }

        .metric-card .value {
            font-size: 32px;
            font-weight: 700;
            color: var(--dark-color);
            margin: 10px 0;
        }

        .metric-card .label {
            color: #7f8c8d;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .metric-card .sub-info {
            font-size: 12px;
            color: #95a5a6;
            margin-top: 8px;
        }

        .chart-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .chart-card h3 {
            color: var(--dark-color);
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .chart-container {
            position: relative;
            height: 350px;
        }

        .chart-container.small {
            height: 280px;
        }

        /* Color variations for metric cards */
        .metric-card.revenue .icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .metric-card.orders .icon {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .metric-card.avg-order .icon {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }

        .metric-card.customers .icon {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }

        .metric-card.popular .icon {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: white;
        }

        .metric-card.pending .icon {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            color: white;
        }

        .nav-pills .nav-link {
            border-radius: 10px;
            padding: 10px 20px;
            margin-right: 10px;
            color: var(--dark-color);
            font-weight: 500;
        }

        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .logout-btn {
            background: var(--danger-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }

        .loading-spinner {
            text-align: center;
            padding: 50px;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
            border-width: 0.3em;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- Header -->
        <div class="dashboard-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-chart-line me-2"></i>Admin Dashboard</h1>
                    <p class="subtitle mb-0">Aling Nena Restaurant Analytics</p>
                </div>
                <div>
                    <a href="manage_orders.php" class="btn btn-outline-dark me-2">
                        <i class="fas fa-shopping-bag me-2"></i>Orders
                    </a>
                    <a href="manage_menu.php" class="btn btn-outline-dark me-2">
                        <i class="fas fa-utensils me-2"></i>Menu
                    </a>
                    <a href="manage_users.php" class="btn btn-outline-dark me-2">
                        <i class="fas fa-users me-2"></i>Users
                    </a>
                    <button class="logout-btn" onclick="window.location.href='../index.html'">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loadingState" class="loading-spinner">
            <div class="spinner-border text-light" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-light mt-3">Loading dashboard data...</p>
        </div>

        <!-- Dashboard Content -->
        <div id="dashboardContent" style="display: none;">
            <!-- Metrics Cards -->
            <div class="row">
                <div class="col-md-4 col-lg-2 mb-4">
                    <div class="metric-card revenue">
                        <div class="icon"><i class="fas fa-dollar-sign"></i></div>
                        <div class="label">Today's Revenue</div>
                        <div class="value">₱<span id="revenueToday">0</span></div>
                        <div class="sub-info">
                            Week: ₱<span id="revenueWeek">0</span> | Month: ₱<span id="revenueMonth">0</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2 mb-4">
                    <div class="metric-card orders">
                        <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                        <div class="label">Total Orders</div>
                        <div class="value" id="totalOrders">0</div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2 mb-4">
                    <div class="metric-card avg-order">
                        <div class="icon"><i class="fas fa-receipt"></i></div>
                        <div class="label">Avg Order Value</div>
                        <div class="value">₱<span id="avgOrderValue">0</span></div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2 mb-4">
                    <div class="metric-card customers">
                        <div class="icon"><i class="fas fa-users"></i></div>
                        <div class="label">Customers</div>
                        <div class="value" id="totalCustomers">0</div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2 mb-4">
                    <div class="metric-card popular">
                        <div class="icon"><i class="fas fa-fire"></i></div>
                        <div class="label">Popular Item</div>
                        <div class="value" style="font-size: 16px;" id="popularItem">N/A</div>
                        <div class="sub-info"><span id="popularItemSold">0</span> sold</div>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2 mb-4">
                    <div class="metric-card pending">
                        <div class="icon"><i class="fas fa-clock"></i></div>
                        <div class="label">Pending Orders</div>
                        <div class="value" id="pendingOrders">0</div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 1 -->
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="chart-card">
                        <h3><i class="fas fa-chart-line me-2 text-primary"></i>Sales Overview (Last 30 Days)</h3>
                        <div class="chart-container">
                            <canvas id="salesOverviewChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="chart-card">
                        <h3><i class="fas fa-chart-pie me-2 text-success"></i>Order Status</h3>
                        <div class="chart-container small">
                            <canvas id="orderStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 2 -->
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="chart-card">
                        <h3><i class="fas fa-utensils me-2 text-danger"></i>Top 10 Popular Items</h3>
                        <div class="chart-container">
                            <canvas id="popularItemsChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="chart-card">
                        <h3><i class="fas fa-chart-bar me-2 text-warning"></i>Revenue by Category</h3>
                        <div class="chart-container">
                            <canvas id="revenueByCategoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 3 -->
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="chart-card">
                        <h3><i class="fas fa-clock me-2 text-info"></i>Peak Hours Analysis</h3>
                        <div class="chart-container">
                            <canvas id="peakHoursChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="chart-card">
                        <h3><i class="fas fa-credit-card me-2 text-secondary"></i>Payment Methods</h3>
                        <div class="chart-container small">
                            <canvas id="paymentMethodsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 4 -->
            <div class="row">
                <div class="col-lg-12 mb-4">
                    <div class="chart-card">
                        <h3><i class="fas fa-chart-area me-2 text-success"></i>Daily Orders (Last 30 Days)</h3>
                        <div class="chart-container">
                            <canvas id="dailyOrdersChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="dashboard.js"></script>
</body>

</html>