// Orders Management JavaScript with AJAX
let allOrders = [];
let orderDetailsModal;

document.addEventListener('DOMContentLoaded', function() {
    orderDetailsModal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
    loadOrders();
    
    // Search functionality with debounce
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => loadOrders(), 300);
    });
});

// Load orders via AJAX
async function loadOrders() {
    const statusFilter = document.getElementById('statusFilter').value;
    const paymentFilter = document.getElementById('paymentFilter').value;
    const searchTerm = document.getElementById('searchInput').value;
    
    try {
        const params = new URLSearchParams({
            action: 'get_all',
            status: statusFilter,
            payment: paymentFilter,
            search: searchTerm
        });
        
        const response = await fetch(`api_orders.php?${params}`);
        const result = await response.json();
        
        if (result.success) {
            allOrders = result.data;
            displayOrders(allOrders);
        } else {
            showError('Failed to load orders: ' + result.message);
        }
    } catch (error) {
        showError('Error loading orders: ' + error.message);
    }
}

// Display orders
function displayOrders(orders) {
    const container = document.getElementById('ordersList');
    
    if (orders.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">No orders found</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    orders.forEach(order => {
        html += `
            <div class="order-card">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <h6 class="mb-1"><i class="fas fa-receipt me-2"></i>${order.order_number}</h6>
                        <p class="text-muted small mb-1">${order.customer_name}</p>
                        <p class="text-muted small mb-0">${formatDate(order.created_at)}</p>
                    </div>
                    <div class="col-md-2">
                        <p class="mb-1 small text-muted">Total Amount</p>
                        <h5 class="text-primary mb-0">₱${parseFloat(order.total_amount).toFixed(2)}</h5>
                    </div>
                    <div class="col-md-2">
                        <p class="mb-1 small text-muted">Payment</p>
                        <span class="badge ${getPaymentBadgeClass(order.payment_status)}">
                            ${order.payment_status}
                        </span>
                        <p class="small mb-0">${formatPaymentMethod(order.payment_method)}</p>
                    </div>
                    <div class="col-md-3">
                        <p class="mb-1 small text-muted">Order Status</p>
                        <select class="form-select form-select-sm status-badge status-${order.order_status}" 
                                onchange="updateOrderStatus(${order.id}, this.value)">
                            <option value="pending" ${order.order_status === 'pending' ? 'selected' : ''}>Pending</option>
                            <option value="confirmed" ${order.order_status === 'confirmed' ? 'selected' : ''}>Confirmed</option>
                            <option value="preparing" ${order.order_status === 'preparing' ? 'selected' : ''}>Preparing</option>
                            <option value="out_for_delivery" ${order.order_status === 'out_for_delivery' ? 'selected' : ''}>Out for Delivery</option>
                            <option value="delivered" ${order.order_status === 'delivered' ? 'selected' : ''}>Delivered</option>
                            <option value="cancelled" ${order.order_status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2 text-end">
                        <button class="btn btn-sm btn-outline-primary" onclick="viewOrderDetails(${order.id})">
                            <i class="fas fa-eye"></i> View Details
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Update order status via AJAX
async function updateOrderStatus(orderId, newStatus) {
    if (!confirm(`Change order status to "${formatStatus(newStatus)}"?`)) {
        loadOrders(); // Reload to reset dropdown
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('order_id', orderId);
    formData.append('status', newStatus);
    
    try {
        const response = await fetch('api_orders.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showSuccess('Order status updated successfully!');
            loadOrders(); // Reload orders
        } else {
            showError('Failed to update order status: ' + result.message);
            loadOrders(); // Reload to reset dropdown
        }
    } catch (error) {
        showError('Error updating order status: ' + error.message);
        loadOrders();
    }
}

// View order details
async function viewOrderDetails(orderId) {
    try {
        const response = await fetch(`api_orders.php?action=get_details&order_id=${orderId}`);
        const result = await response.json();
        
        if (result.success) {
            displayOrderDetails(result.data);
            orderDetailsModal.show();
        } else {
            showError('Failed to load order details: ' + result.message);
        }
    } catch (error) {
        showError('Error loading order details: ' + error.message);
    }
}

// Display order details in modal
function displayOrderDetails(order) {
    const content = document.getElementById('orderDetailsContent');
    
    let itemsHtml = '';
    order.items.forEach(item => {
        itemsHtml += `
            <tr>
                <td>${item.food_name}</td>
                <td class="text-center">${item.quantity}</td>
                <td class="text-end">₱${parseFloat(item.price).toFixed(2)}</td>
                <td class="text-end"><strong>₱${parseFloat(item.subtotal).toFixed(2)}</strong></td>
            </tr>
        `;
    });
    
    content.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-primary mb-3">Order Information</h6>
                <table class="table table-sm">
                    <tr><th width="150">Order Number:</th><td>${order.order_number}</td></tr>
                    <tr><th>Date:</th><td>${formatDateTime(order.created_at)}</td></tr>
                    <tr><th>Status:</th><td><span class="status-badge status-${order.order_status}">${formatStatus(order.order_status)}</span></td></tr>
                    <tr><th>Payment Method:</th><td>${formatPaymentMethod(order.payment_method)}</td></tr>
                    <tr><th>Payment Status:</th><td><span class="badge ${getPaymentBadgeClass(order.payment_status)}">${order.payment_status}</span></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-primary mb-3">Customer Information</h6>
                <table class="table table-sm">
                    <tr><th width="150">Name:</th><td>${order.customer_name}</td></tr>
                    <tr><th>Email:</th><td>${order.customer_email || 'N/A'}</td></tr>
                    <tr><th>Phone:</th><td>${order.customer_phone || 'N/A'}</td></tr>
                    <tr><th>Delivery Address:</th><td>${order.delivery_address}</td></tr>
                </table>
            </div>
        </div>
        
        <h6 class="text-primary mb-3 mt-4">Order Items</h6>
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Item</th>
                    <th class="text-center">Quantity</th>
                    <th class="text-end">Price</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                ${itemsHtml}
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <th colspan="3" class="text-end">Total:</th>
                    <th class="text-end">₱${parseFloat(order.total_amount).toFixed(2)}</th>
                </tr>
            </tfoot>
        </table>
        
        ${order.notes ? `
            <div class="alert alert-info mt-3">
                <strong>Notes:</strong> ${order.notes}
            </div>
        ` : ''}
    `;
}

// Utility functions
function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

function formatDateTime(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatStatus(status) {
    return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}

function formatPaymentMethod(method) {
    return method.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}

function getPaymentBadgeClass(status) {
    const classes = {
        'paid': 'bg-success',
        'pending': 'bg-warning',
        'failed': 'bg-danger'
    };
    return classes[status] || 'bg-secondary';
}

function showSuccess(message) {
    alert(message);
}

function showError(message) {
    alert('Error: ' + message);
    console.error(message);
}
