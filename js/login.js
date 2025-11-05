// Slider functionality
const sliderItems = document.querySelectorAll('.slider-item');
const prevBtn = document.querySelector('[data-prev-btn]');
const nextBtn = document.querySelector('[data-next-btn]');
let currentSlide = 0;
let slideInterval;

const showSlide = (index) => {
    sliderItems.forEach(item => item.classList.remove('active'));
    currentSlide = (index + sliderItems.length) % sliderItems.length;
    sliderItems[currentSlide].classList.add('active');
};

const nextSlide = () => showSlide(currentSlide + 1);
const prevSlide = () => showSlide(currentSlide - 1);

const startAutoSlide = () => {
    slideInterval = setInterval(nextSlide, 5000); // Change slide every 5 seconds
};

const stopAutoSlide = () => {
    clearInterval(slideInterval);
};

// Add event listeners for manual navigation
prevBtn.addEventListener('click', () => {
    prevSlide();
    stopAutoSlide();
    startAutoSlide(); // Restart the timer
});

nextBtn.addEventListener('click', () => {
    nextSlide();
    stopAutoSlide();
    startAutoSlide(); // Restart the timer
});

// Start auto-sliding
startAutoSlide();

// Modal control
const modal = document.getElementById('loginModal');
const loginTriggers = document.querySelectorAll('.login-trigger');
const closeModal = document.querySelector('.close-modal');

loginTriggers.forEach(trigger => {
    trigger.addEventListener('click', (e) => {
        e.preventDefault();
        modal.classList.add('show');
    });
});

closeModal.addEventListener('click', () => {
    modal.classList.remove('show');
});

// Click outside modal to close
window.addEventListener('click', (e) => {
    if (e.target === modal) {
        modal.classList.remove('show');
    }
});

// Initialize Bootstrap modals
const statusSuccessModal = new bootstrap.Modal(document.getElementById('statusSuccessModal'));
const statusErrorsModal = new bootstrap.Modal(document.getElementById('statusErrorsModal'));

function showSuccessModal(userRole) {
    modal.classList.remove('show');
    statusSuccessModal.show();
    setTimeout(() => {
        statusSuccessModal.hide();
        // Redirect based on user role
        if (userRole === 'admin') {
            window.location.href = 'admin/dashboard.php';
        } else {
            window.location.href = 'badges_lab.html';
        }
    }, 2000);
}

function showFailedModal() {
    modal.classList.remove('show');
    statusErrorsModal.show();
    setTimeout(() => {
        statusErrorsModal.hide();
        modal.classList.add('show');
    }, 2000);
}

// Update the form submission handler
document.getElementById('loginForm').addEventListener('submit', function(event) {
    event.preventDefault();
    
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    
    console.log('Attempting login with username:', username);
    
    fetch('process_login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Login response:', data);
        if (data.success) {
            console.log('Login successful, user role:', data.user.role);
            localStorage.setItem('user', JSON.stringify({
                id: data.user.id,
                username: data.user.username,
                email: data.user.email,
                phone: data.user.phone,
                full_name: data.user.full_name,
                role: data.user.role
            }));
            showSuccessModal(data.user.role);
        } else {
            // Check if email verification is required
            if (data.requires_verification) {
                console.log('Email verification required for:', data.email);
                modal.classList.remove('show');
                alert('Please verify your email before logging in. Redirecting to verification page...');
                // Redirect to verification page
                window.location.href = 'verify_email.html';
            } else {
                console.error('Login failed:', data.message);
                showFailedModal();
            }
        }
    })
    .catch(error => {
        console.error('Error during login:', error);
        showFailedModal();
    });
});

// Remove old modal event listeners since we're using Bootstrap modals now