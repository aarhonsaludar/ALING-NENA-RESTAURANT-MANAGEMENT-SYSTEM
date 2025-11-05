/**
 * ============================================
 * ALING NENA'S KITCHEN - UX UTILITIES
 * Toast Notifications, Confirmations, Loading
 * ============================================
 */

// ============================================
// TOAST NOTIFICATIONS
// ============================================
class ToastNotification {
    constructor() {
        this.container = this.createContainer();
    }

    createContainer() {
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        return container;
    }

    show(message, type = 'info', duration = 4000) {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };

        const titles = {
            success: 'Success!',
            error: 'Error!',
            warning: 'Warning!',
            info: 'Info'
        };

        toast.innerHTML = `
            <div class="toast-icon">${icons[type]}</div>
            <div class="toast-content">
                <div class="toast-title">${titles[type]}</div>
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()">×</button>
        `;

        this.container.appendChild(toast);

        // Auto remove after duration
        setTimeout(() => {
            toast.classList.add('removing');
            setTimeout(() => toast.remove(), 300);
        }, duration);

        return toast;
    }

    success(message, duration) {
        return this.show(message, 'success', duration);
    }

    error(message, duration) {
        return this.show(message, 'error', duration);
    }

    warning(message, duration) {
        return this.show(message, 'warning', duration);
    }

    info(message, duration) {
        return this.show(message, 'info', duration);
    }
}

// Create global toast instance
const toast = new ToastNotification();

// ============================================
// CONFIRMATION DIALOGS
// ============================================
function showConfirmDialog(options) {
    return new Promise((resolve) => {
        const defaults = {
            title: 'Are you sure?',
            message: 'This action cannot be undone.',
            confirmText: 'Confirm',
            cancelText: 'Cancel',
            type: 'warning' // warning or danger
        };

        const config = { ...defaults, ...options };

        const overlay = document.createElement('div');
        overlay.className = 'confirm-dialog-overlay';

        const iconSymbol = config.type === 'danger' ? '⚠' : '❗';

        overlay.innerHTML = `
            <div class="confirm-dialog">
                <div class="confirm-dialog-icon ${config.type}">
                    ${iconSymbol}
                </div>
                <h3 class="confirm-dialog-title">${config.title}</h3>
                <p class="confirm-dialog-message">${config.message}</p>
                <div class="confirm-dialog-buttons">
                    <button class="confirm-dialog-button cancel">${config.cancelText}</button>
                    <button class="confirm-dialog-button confirm">${config.confirmText}</button>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);

        const dialog = overlay.querySelector('.confirm-dialog');
        const cancelBtn = dialog.querySelector('.cancel');
        const confirmBtn = dialog.querySelector('.confirm');

        function close(result) {
            overlay.style.animation = 'fadeOut 0.2s ease-out';
            setTimeout(() => {
                overlay.remove();
                resolve(result);
            }, 200);
        }

        cancelBtn.addEventListener('click', () => close(false));
        confirmBtn.addEventListener('click', () => close(true));
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) close(false);
        });
    });
}

// ============================================
// LOADING OVERLAY
// ============================================
class LoadingOverlay {
    constructor() {
        this.overlay = null;
    }

    show(text = 'Loading...') {
        if (this.overlay) return;

        this.overlay = document.createElement('div');
        this.overlay.className = 'loading-overlay';
        this.overlay.innerHTML = `
            <div>
                <div class="loading-spinner"></div>
                <div class="loading-text">${text}</div>
            </div>
        `;
        document.body.appendChild(this.overlay);
    }

    hide() {
        if (this.overlay) {
            this.overlay.remove();
            this.overlay = null;
        }
    }
}

const loading = new LoadingOverlay();

// ============================================
// FORM VALIDATION
// ============================================
class FormValidator {
    constructor(formElement) {
        this.form = formElement;
        this.errors = {};
    }

    validate(rules) {
        this.errors = {};
        let isValid = true;

        for (const [fieldName, fieldRules] of Object.entries(rules)) {
            const field = this.form.querySelector(`[name="${fieldName}"]`);
            if (!field) continue;

            const value = field.value.trim();
            const fieldGroup = field.closest('.form-group');

            // Clear previous state
            if (fieldGroup) {
                fieldGroup.classList.remove('has-error', 'has-success');
            }

            for (const rule of fieldRules) {
                if (rule.type === 'required' && !value) {
                    this.addError(fieldName, rule.message || 'This field is required');
                    isValid = false;
                    break;
                }

                if (rule.type === 'email' && value) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(value)) {
                        this.addError(fieldName, rule.message || 'Invalid email address');
                        isValid = false;
                        break;
                    }
                }

                if (rule.type === 'min' && value.length < rule.value) {
                    this.addError(fieldName, rule.message || `Minimum ${rule.value} characters required`);
                    isValid = false;
                    break;
                }

                if (rule.type === 'max' && value.length > rule.value) {
                    this.addError(fieldName, rule.message || `Maximum ${rule.value} characters allowed`);
                    isValid = false;
                    break;
                }

                if (rule.type === 'match') {
                    const matchField = this.form.querySelector(`[name="${rule.field}"]`);
                    if (matchField && value !== matchField.value.trim()) {
                        this.addError(fieldName, rule.message || 'Fields do not match');
                        isValid = false;
                        break;
                    }
                }

                if (rule.type === 'pattern' && value) {
                    if (!rule.value.test(value)) {
                        this.addError(fieldName, rule.message || 'Invalid format');
                        isValid = false;
                        break;
                    }
                }
            }

            // Update UI
            if (this.errors[fieldName]) {
                if (fieldGroup) {
                    fieldGroup.classList.add('has-error');
                    let errorMsg = fieldGroup.querySelector('.error-message-field');
                    if (!errorMsg) {
                        errorMsg = document.createElement('div');
                        errorMsg.className = 'error-message-field';
                        field.parentNode.appendChild(errorMsg);
                    }
                    errorMsg.textContent = this.errors[fieldName];
                }
            } else if (value) {
                if (fieldGroup) {
                    fieldGroup.classList.add('has-success');
                }
            }
        }

        return isValid;
    }

    addError(field, message) {
        this.errors[field] = message;
    }

    getErrors() {
        return this.errors;
    }

    clearErrors() {
        this.errors = {};
        const formGroups = this.form.querySelectorAll('.form-group');
        formGroups.forEach(group => {
            group.classList.remove('has-error', 'has-success');
            const errorMsg = group.querySelector('.error-message-field');
            if (errorMsg) errorMsg.remove();
        });
    }
}

// ============================================
// CART BADGE UPDATE
// ============================================
function updateCartBadge(count) {
    const badge = document.querySelector('.cart-badge .badge-count');
    if (badge) {
        badge.textContent = count;
        if (count > 0) {
            badge.style.display = 'block';
        } else {
            badge.style.display = 'none';
        }
    }
}

function updateWishlistBadge(count) {
    const badge = document.querySelector('.wishlist-badge .badge-count');
    if (badge) {
        badge.textContent = count;
        if (count > 0) {
            badge.style.display = 'block';
        } else {
            badge.style.display = 'none';
        }
    }
}

// ============================================
// PRINT RECEIPT
// ============================================
function printReceipt() {
    window.print();
}

// ============================================
// SMOOTH SCROLL
// ============================================
function smoothScrollTo(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// ============================================
// DEBOUNCE UTILITY
// ============================================
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// ============================================
// FORMAT CURRENCY
// ============================================
function formatCurrency(amount) {
    return '₱' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

// ============================================
// COPY TO CLIPBOARD
// ============================================
async function copyToClipboard(text) {
    try {
        await navigator.clipboard.writeText(text);
        toast.success('Copied to clipboard!');
    } catch (err) {
        toast.error('Failed to copy');
    }
}

// ============================================
// AUTO-HIDE ALERTS
// ============================================
function autoHideAlerts() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
}

// ============================================
// INITIALIZE ON PAGE LOAD
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts
    autoHideAlerts();
    
    // Add ripple effect to buttons
    document.querySelectorAll('.btn-filipino, .btn-filipino-outline').forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(255, 255, 255, 0.5);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.6s ease-out;
                pointer-events: none;
            `;
            
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });
    });
});

// Add ripple animation
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }
`;
document.head.appendChild(style);

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        toast,
        showConfirmDialog,
        loading,
        FormValidator,
        updateCartBadge,
        updateWishlistBadge,
        printReceipt,
        smoothScrollTo,
        debounce,
        formatCurrency,
        copyToClipboard
    };
}
