// Form validation
const validateForm = () => {
    let isValid = true;
    
    // Get form values
    const fullName = document.getElementById('full_name').value.trim();
    const username = document.getElementById('username').value.trim();
    const email = document.getElementById('email').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    // Reset error states
    document.querySelectorAll('.error-message').forEach(msg => msg.classList.remove('show'));
    document.querySelectorAll('input').forEach(input => input.classList.remove('error'));
    
    // Validate full name
    if (fullName.length < 2) {
        document.getElementById('full_name_error').classList.add('show');
        document.getElementById('full_name').classList.add('error');
        isValid = false;
    }
    
    // Validate username
    if (username.length < 3) {
        document.getElementById('username_error').classList.add('show');
        document.getElementById('username').classList.add('error');
        isValid = false;
    }
    
    // Validate email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        document.getElementById('email_error').classList.add('show');
        document.getElementById('email').classList.add('error');
        isValid = false;
    }
    
    // Validate phone
    const phoneRegex = /^(09|\+639)\d{9}$/;
    if (!phoneRegex.test(phone.replace(/[-\s]/g, ''))) {
        document.getElementById('phone_error').classList.add('show');
        document.getElementById('phone').classList.add('error');
        isValid = false;
    }
    
    // Validate password
    if (password.length < 6) {
        document.getElementById('password_error').classList.add('show');
        document.getElementById('password').classList.add('error');
        isValid = false;
    }
    
    // Validate confirm password
    if (password !== confirmPassword) {
        document.getElementById('confirm_password_error').classList.add('show');
        document.getElementById('confirm_password').classList.add('error');
        isValid = false;
    }
    
    return isValid;
};

// Initialize modals
const successModal = new bootstrap.Modal(document.getElementById('successModal'));
const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));

// Form submission
document.getElementById('registerForm').addEventListener('submit', function(event) {
    event.preventDefault();
    
    if (!validateForm()) {
        return;
    }
    
    // Get form data
    const formData = new FormData();
    formData.append('full_name', document.getElementById('full_name').value.trim());
    formData.append('username', document.getElementById('username').value.trim());
    formData.append('email', document.getElementById('email').value.trim());
    formData.append('phone', document.getElementById('phone').value.trim());
    formData.append('password', document.getElementById('password').value);
    
    // Submit registration
    fetch('process_register.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Check if email verification is required
            if (data.requires_verification) {
                // Show success message briefly, then redirect to verification page
                successModal.show();
                document.querySelector('#successModal h3').textContent = 'Check Your Email!';
                document.querySelector('#successModal p').textContent = 'We sent a verification code to your email. Redirecting to verification page...';
                
                setTimeout(() => {
                    successModal.hide();
                    // Redirect to email verification page
                    window.location.href = 'verify_email.html';
                }, 2000);
            } else {
                // No verification needed - redirect to login
                successModal.show();
                setTimeout(() => {
                    successModal.hide();
                    window.location.href = 'index.html';
                }, 2000);
            }
        } else {
            document.getElementById('errorModalMessage').textContent = data.message;
            errorModal.show();
            setTimeout(() => {
                errorModal.hide();
            }, 3000);
        }
    })
    .catch(error => {
        console.error('Error during registration:', error);
        document.getElementById('errorModalMessage').textContent = 'An error occurred. Please try again.';
        errorModal.show();
        setTimeout(() => {
            errorModal.hide();
        }, 3000);
    });
});

// Real-time validation
document.getElementById('username').addEventListener('blur', function() {
    if (this.value.trim().length < 3) {
        document.getElementById('username_error').classList.add('show');
        this.classList.add('error');
    } else {
        document.getElementById('username_error').classList.remove('show');
        this.classList.remove('error');
    }
});

document.getElementById('email').addEventListener('blur', function() {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(this.value.trim())) {
        document.getElementById('email_error').classList.add('show');
        this.classList.add('error');
    } else {
        document.getElementById('email_error').classList.remove('show');
        this.classList.remove('error');
    }
});

document.getElementById('confirm_password').addEventListener('blur', function() {
    const password = document.getElementById('password').value;
    if (this.value !== password) {
        document.getElementById('confirm_password_error').classList.add('show');
        this.classList.add('error');
    } else {
        document.getElementById('confirm_password_error').classList.remove('show');
        this.classList.remove('error');
    }
});
