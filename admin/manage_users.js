// User Management JavaScript with AJAX
let allUsers = [];
let userModal;

document.addEventListener('DOMContentLoaded', function() {
    userModal = new bootstrap.Modal(document.getElementById('userModal'));
    loadUsers();
    loadStats();
    
    // Search functionality with debouncing
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadUsers();
        }, 300);
    });
});

// Load user statistics
async function loadStats() {
    try {
        const response = await fetch('api_users.php?action=get_stats');
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('totalUsers').textContent = result.stats.total;
            document.getElementById('activeUsers').textContent = result.stats.active;
            document.getElementById('adminUsers').textContent = result.stats.admins;
            document.getElementById('newUsersToday').textContent = result.stats.new_today;
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

// Load all users via AJAX
async function loadUsers() {
    const role = document.getElementById('roleFilter').value;
    const status = document.getElementById('statusFilter').value;
    const search = document.getElementById('searchInput').value;
    
    try {
        const params = new URLSearchParams({
            action: 'get_all',
            role: role,
            status: status,
            search: search
        });
        
        const response = await fetch(`api_users.php?${params}`);
        const result = await response.json();
        
        if (result.success) {
            allUsers = result.data;
            displayUsers(allUsers);
        } else {
            showError('Failed to load users: ' + result.message);
        }
    } catch (error) {
        showError('Error loading users: ' + error.message);
    }
}

// Display users
function displayUsers(users) {
    const container = document.getElementById('usersList');
    
    if (users.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                <p class="text-muted">No users found</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    users.forEach(user => {
        const initials = user.username.substring(0, 2).toUpperCase();
        const roleClass = user.role === 'admin' ? 'badge-admin' : 'badge-user';
        const statusClass = user.status === 'active' ? 'badge-active' : 'badge-inactive';
        const createdDate = new Date(user.created_at).toLocaleDateString();
        const lastLogin = user.last_login ? new Date(user.last_login).toLocaleString() : 'Never';
        
        html += `
            <div class="user-card">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="user-avatar">${initials}</div>
                    </div>
                    <div class="col">
                        <h5 class="mb-1">
                            ${user.username}
                            <span class="badge badge-role ${roleClass} ms-2">${user.role.toUpperCase()}</span>
                            <span class="badge badge-status ${statusClass} ms-1">${user.status.toUpperCase()}</span>
                        </h5>
                        <p class="text-muted mb-1 small">
                            <i class="fas fa-envelope me-1"></i>${user.email || 'N/A'}
                            <span class="ms-3"><i class="fas fa-phone me-1"></i>${user.phone || 'N/A'}</span>
                        </p>
                        <p class="text-muted mb-0 small">
                            <i class="fas fa-calendar me-1"></i>Joined: ${createdDate}
                            <span class="ms-3"><i class="fas fa-clock me-1"></i>Last login: ${lastLogin}</span>
                        </p>
                    </div>
                    <div class="col-auto text-end">
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editUser(${user.id})" title="Edit User">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-info me-1" onclick="viewUserDetails(${user.id})" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning me-1" onclick="resetPassword(${user.id}, '${user.username}')" title="Reset Password">
                            <i class="fas fa-key"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(${user.id}, '${user.username}')" title="Delete User">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Show add modal
function showAddModal() {
    document.getElementById('modalTitle').textContent = 'Add New User';
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('passwordNote').textContent = '*';
    document.getElementById('password').required = true;
    userModal.show();
}

// Edit user
async function editUser(id) {
    const user = allUsers.find(u => u.id == id);
    if (!user) return;
    
    document.getElementById('modalTitle').textContent = 'Edit User';
    document.getElementById('userId').value = user.id;
    document.getElementById('username').value = user.username;
    document.getElementById('email').value = user.email || '';
    document.getElementById('phone').value = user.phone || '';
    document.getElementById('password').value = '';
    document.getElementById('password').required = false;
    document.getElementById('passwordNote').textContent = '(Leave empty to keep current)';
    document.getElementById('role').value = user.role;
    document.getElementById('status').value = user.status;
    document.getElementById('address').value = user.address || '';
    
    userModal.show();
}

// Save user (Add or Update) via AJAX
async function saveUser() {
    const id = document.getElementById('userId').value;
    const username = document.getElementById('username').value.trim();
    const email = document.getElementById('email').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const password = document.getElementById('password').value;
    const role = document.getElementById('role').value;
    const status = document.getElementById('status').value;
    const address = document.getElementById('address').value.trim();
    
    if (!username || !email) {
        alert('Please fill in all required fields');
        return;
    }
    
    if (!id && !password) {
        alert('Password is required for new users');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', id ? 'update' : 'add');
    if (id) formData.append('id', id);
    formData.append('username', username);
    formData.append('email', email);
    formData.append('phone', phone);
    if (password) formData.append('password', password);
    formData.append('role', role);
    formData.append('status', status);
    formData.append('address', address);
    
    try {
        const response = await fetch('api_users.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showSuccess(id ? 'User updated successfully!' : 'User added successfully!');
            userModal.hide();
            loadUsers();
            loadStats();
        } else {
            showError('Failed to save user: ' + result.message);
        }
    } catch (error) {
        showError('Error saving user: ' + error.message);
    }
}

// Delete user via AJAX
async function deleteUser(id, username) {
    if (!confirm(`Are you sure you want to delete user "${username}"?\n\nThis action cannot be undone.`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);
    
    try {
        const response = await fetch('api_users.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showSuccess('User deleted successfully!');
            loadUsers();
            loadStats();
        } else {
            showError('Failed to delete user: ' + result.message);
        }
    } catch (error) {
        showError('Error deleting user: ' + error.message);
    }
}

// Reset password
async function resetPassword(id, username) {
    const newPassword = prompt(`Enter new password for "${username}":`);
    if (!newPassword) return;
    
    if (newPassword.length < 6) {
        alert('Password must be at least 6 characters long');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'reset_password');
    formData.append('id', id);
    formData.append('password', newPassword);
    
    try {
        const response = await fetch('api_users.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showSuccess('Password reset successfully!');
        } else {
            showError('Failed to reset password: ' + result.message);
        }
    } catch (error) {
        showError('Error resetting password: ' + error.message);
    }
}

// View user details
async function viewUserDetails(id) {
    const user = allUsers.find(u => u.id == id);
    if (!user) return;
    
    const details = `
Username: ${user.username}
Email: ${user.email || 'N/A'}
Phone: ${user.phone || 'N/A'}
Role: ${user.role.toUpperCase()}
Status: ${user.status.toUpperCase()}
Address: ${user.address || 'N/A'}
Created: ${new Date(user.created_at).toLocaleString()}
Last Login: ${user.last_login ? new Date(user.last_login).toLocaleString() : 'Never'}
    `;
    
    alert(details);
}

// Utility functions
function showSuccess(message) {
    // Create a Bootstrap alert
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

function showError(message) {
    // Create a Bootstrap alert
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}
