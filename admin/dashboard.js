// Chart.js configuration and data loading
let charts = {};

// Chart color schemes
const chartColors = {
    primary: 'rgba(102, 126, 234, 1)',
    primaryLight: 'rgba(102, 126, 234, 0.2)',
    secondary: 'rgba(245, 87, 108, 1)',
    secondaryLight: 'rgba(245, 87, 108, 0.2)',
    success: 'rgba(67, 233, 123, 1)',
    successLight: 'rgba(67, 233, 123, 0.2)',
    warning: 'rgba(254, 225, 64, 1)',
    warningLight: 'rgba(254, 225, 64, 0.2)',
    info: 'rgba(52, 152, 219, 1)',
    infoLight: 'rgba(52, 152, 219, 0.2)',
    danger: 'rgba(231, 76, 60, 1)',
    dangerLight: 'rgba(231, 76, 60, 0.2)',
    purple: 'rgba(155, 89, 182, 1)',
    purpleLight: 'rgba(155, 89, 182, 0.2)',
    teal: 'rgba(78, 205, 196, 1)',
    tealLight: 'rgba(78, 205, 196, 0.2)'
};

const colorPalette = [
    'rgba(102, 126, 234, 0.8)',
    'rgba(245, 87, 108, 0.8)',
    'rgba(67, 233, 123, 0.8)',
    'rgba(254, 225, 64, 0.8)',
    'rgba(52, 152, 219, 0.8)',
    'rgba(231, 76, 60, 0.8)',
    'rgba(155, 89, 182, 0.8)',
    'rgba(78, 205, 196, 0.8)',
    'rgba(241, 196, 15, 0.8)',
    'rgba(46, 204, 113, 0.8)'
];

// Load dashboard data
async function loadDashboardData() {
    try {
        const response = await fetch('get_dashboard_data.php');
        const result = await response.json();
        
        if (result.success) {
            updateMetricCards(result.data);
            createCharts(result.data);
            
            // Hide loading, show content
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('dashboardContent').style.display = 'block';
        } else {
            console.error('Failed to load dashboard data:', result.message);
            alert('Failed to load dashboard data. Please try again.');
        }
    } catch (error) {
        console.error('Error loading dashboard data:', error);
        alert('Error loading dashboard data. Please check your connection.');
    }
}

// Update metric cards
function updateMetricCards(data) {
    document.getElementById('revenueToday').textContent = formatCurrency(data.revenue_today);
    document.getElementById('revenueWeek').textContent = formatCurrency(data.revenue_week);
    document.getElementById('revenueMonth').textContent = formatCurrency(data.revenue_month);
    document.getElementById('totalOrders').textContent = formatNumber(data.total_orders);
    document.getElementById('avgOrderValue').textContent = formatCurrency(data.avg_order_value);
    document.getElementById('totalCustomers').textContent = formatNumber(data.total_customers);
    document.getElementById('popularItem').textContent = data.popular_item;
    document.getElementById('popularItemSold').textContent = formatNumber(data.popular_item_sold);
    document.getElementById('pendingOrders').textContent = formatNumber(data.pending_orders);
}

// Create all charts
function createCharts(data) {
    createSalesOverviewChart(data.sales_overview);
    createPopularItemsChart(data.popular_items);
    createOrderStatusChart(data.order_status_distribution);
    createRevenueByCategoryChart(data.revenue_by_category);
    createPeakHoursChart(data.peak_hours);
    createPaymentMethodsChart(data.payment_methods);
    createDailyOrdersChart(data.daily_orders);
}

// 1. Sales Overview Line Chart
function createSalesOverviewChart(salesData) {
    const ctx = document.getElementById('salesOverviewChart').getContext('2d');
    
    // Fill in missing dates with 0 revenue
    const dates = [];
    const revenues = [];
    const today = new Date();
    
    for (let i = 29; i >= 0; i--) {
        const date = new Date(today);
        date.setDate(date.getDate() - i);
        const dateStr = date.toISOString().split('T')[0];
        dates.push(formatDate(dateStr));
        
        const dataPoint = salesData.find(d => d.date === dateStr);
        revenues.push(dataPoint ? parseFloat(dataPoint.revenue) : 0);
    }
    
    charts.salesOverview = new Chart(ctx, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: 'Revenue (₱)',
                data: revenues,
                borderColor: chartColors.primary,
                backgroundColor: chartColors.primaryLight,
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: chartColors.primary,
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            return 'Revenue: ₱' + formatCurrency(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + formatCurrency(value);
                        }
                    }
                },
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });
}

// 2. Popular Menu Items Horizontal Bar Chart
function createPopularItemsChart(itemsData) {
    const ctx = document.getElementById('popularItemsChart').getContext('2d');
    
    const labels = itemsData.map(item => item.food_name);
    const quantities = itemsData.map(item => parseInt(item.total_sold));
    
    charts.popularItems = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Items Sold',
                data: quantities,
                backgroundColor: colorPalette,
                borderColor: colorPalette.map(color => color.replace('0.8', '1')),
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.x + ' items sold';
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    });
}

// 3. Order Status Distribution Doughnut Chart
function createOrderStatusChart(statusData) {
    const ctx = document.getElementById('orderStatusChart').getContext('2d');
    
    const labels = statusData.map(item => capitalizeFirst(item.order_status));
    const counts = statusData.map(item => parseInt(item.count));
    
    const statusColors = {
        'Pending': chartColors.warning,
        'Confirmed': chartColors.info,
        'Preparing': chartColors.purple,
        'Out_for_delivery': chartColors.teal,
        'Delivered': chartColors.success,
        'Cancelled': chartColors.danger
    };
    
    const backgroundColors = labels.map(label => 
        statusColors[label.replace(' ', '_')] || chartColors.primary
    );
    
    charts.orderStatus = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: counts,
                backgroundColor: backgroundColors,
                borderWidth: 3,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            size: 11
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}

// 4. Revenue by Category Bar Chart
function createRevenueByCategoryChart(categoryData) {
    const ctx = document.getElementById('revenueByCategoryChart').getContext('2d');
    
    const labels = categoryData.map(item => item.category);
    const revenues = categoryData.map(item => parseFloat(item.revenue));
    
    charts.revenueByCategory = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revenue (₱)',
                data: revenues,
                backgroundColor: [
                    chartColors.primary,
                    chartColors.secondary,
                    chartColors.success,
                    chartColors.warning
                ],
                borderColor: [
                    chartColors.primary.replace('1)', '1)'),
                    chartColors.secondary.replace('1)', '1)'),
                    chartColors.success.replace('1)', '1)'),
                    chartColors.warning.replace('1)', '1)')
                ],
                borderWidth: 2,
                borderRadius: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Revenue: ₱' + formatCurrency(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + formatCurrency(value);
                        }
                    }
                }
            }
        }
    });
}

// 5. Peak Hours Analysis Line Chart
function createPeakHoursChart(hoursData) {
    const ctx = document.getElementById('peakHoursChart').getContext('2d');
    
    // Create array for all 24 hours
    const hours = [];
    const orderCounts = [];
    
    for (let i = 0; i < 24; i++) {
        hours.push(formatHour(i));
        const dataPoint = hoursData.find(d => parseInt(d.hour) === i);
        orderCounts.push(dataPoint ? parseInt(dataPoint.order_count) : 0);
    }
    
    charts.peakHours = new Chart(ctx, {
        type: 'line',
        data: {
            labels: hours,
            datasets: [{
                label: 'Orders',
                data: orderCounts,
                borderColor: chartColors.info,
                backgroundColor: chartColors.infoLight,
                tension: 0.4,
                fill: true,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: chartColors.info,
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + ' orders';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}

// 6. Payment Method Distribution Pie Chart
function createPaymentMethodsChart(paymentData) {
    const ctx = document.getElementById('paymentMethodsChart').getContext('2d');
    
    const labels = paymentData.map(item => formatPaymentMethod(item.payment_method));
    const counts = paymentData.map(item => parseInt(item.count));
    
    charts.paymentMethods = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: counts,
                backgroundColor: [
                    chartColors.success,
                    chartColors.info,
                    chartColors.warning,
                    chartColors.secondary,
                    chartColors.purple
                ],
                borderWidth: 3,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            size: 11
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}

// 7. Daily Orders Area Chart
function createDailyOrdersChart(ordersData) {
    const ctx = document.getElementById('dailyOrdersChart').getContext('2d');
    
    // Fill in missing dates
    const dates = [];
    const orderCounts = [];
    const today = new Date();
    
    for (let i = 29; i >= 0; i--) {
        const date = new Date(today);
        date.setDate(date.getDate() - i);
        const dateStr = date.toISOString().split('T')[0];
        dates.push(formatDate(dateStr));
        
        const dataPoint = ordersData.find(d => d.date === dateStr);
        orderCounts.push(dataPoint ? parseInt(dataPoint.order_count) : 0);
    }
    
    charts.dailyOrders = new Chart(ctx, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: 'Orders',
                data: orderCounts,
                borderColor: chartColors.success,
                backgroundColor: chartColors.successLight,
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: chartColors.success,
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + ' orders';
                        }
                    }
                },
                filler: {
                    propagate: true
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                },
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });
}

// Utility functions
function formatCurrency(value) {
    return parseFloat(value).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function formatNumber(value) {
    return parseInt(value).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    const month = date.toLocaleString('en-US', { month: 'short' });
    const day = date.getDate();
    return month + ' ' + day;
}

function formatHour(hour) {
    if (hour === 0) return '12 AM';
    if (hour < 12) return hour + ' AM';
    if (hour === 12) return '12 PM';
    return (hour - 12) + ' PM';
}

function formatPaymentMethod(method) {
    return method.split('_').map(word => 
        word.charAt(0).toUpperCase() + word.slice(1)
    ).join(' ');
}

function capitalizeFirst(str) {
    return str.split('_').map(word => 
        word.charAt(0).toUpperCase() + word.slice(1)
    ).join(' ');
}

// Initialize dashboard on page load
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
    
    // Auto-refresh every 5 minutes
    setInterval(function() {
        loadDashboardData();
    }, 300000);
});
