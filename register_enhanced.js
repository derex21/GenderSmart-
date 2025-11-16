// Register Form Enhanced JavaScript
const GOOGLE_CLIENT_ID = 'YOUR_GOOGLE_CLIENT_ID'; // TODO: replace

function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.querySelector('.password-toggle i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

function toggleConfirmPassword() {
    const passwordInput = document.getElementById('confirm_password');
    const toggleIcon = document.querySelectorAll('.password-toggle')[1].querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

// Page transition
document.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', function(e) {
        if (this.href.includes('login.html')) {
            e.preventDefault();
            const overlay = document.querySelector('.transition-overlay');
            overlay.style.opacity = '1';
            
            setTimeout(() => {
                window.location.href = this.href;
            }, 300);
        }
    });
});

// Google Identity Services setup
window.handleCredentialResponse = async function(response) {
    try {
        const formData = new FormData();
        formData.append('credential', response.credential);

        const res = await fetch('../BACKEND/google_login.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data && data.success) {
            window.location.href = '../FRONTEND/student_dashboard.html';
        } else {
            alert((data && data.message) || 'Google sign-in failed');
        }
    } catch (e) {
        alert('Network error during Google sign-in');
    }
};

function initGoogle() {
    if (!(window.google && window.google.accounts && window.google.accounts.id)) return;
    try {
        google.accounts.id.initialize({
            client_id: GOOGLE_CLIENT_ID,
            callback: handleCredentialResponse,
            ux_mode: 'popup'
        });
        const btn = document.querySelector('.gsi-material-button');
        if (btn) {
            btn.innerHTML = '';
            google.accounts.id.renderButton(btn, {
                theme: 'outline',
                size: 'large',
                shape: 'rectangular',
                text: 'continue_with',
                logo_alignment: 'left'
            });
        }
        // google.accounts.id.prompt();
    } catch (e) {
        // no-op
    }
}

window.addEventListener('DOMContentLoaded', initGoogle);

// Password strength checker
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthBar = document.getElementById('strengthFill');
    const strengthText = document.getElementById('strengthText');
    
    // Reset if password is empty
    if (password.length === 0) {
        strengthBar.style.width = '0%';
        strengthBar.style.backgroundColor = '#e0e0e0';
        strengthText.textContent = 'Password strength';
        strengthText.style.color = '#666';
        return;
    }
    
    let strength = 0;
    let strengthLabel = '';
    let strengthColor = '';
    
    // Length check (minimum 8 characters)
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++; // Bonus for longer passwords
    
    // Character type checks
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    // Bonus for complexity
    if (password.length >= 16) strength++; // Extra bonus for very long passwords
    if (/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9])/.test(password)) strength++; // All character types
    
    // Cap strength at 7 for display purposes
    strength = Math.min(strength, 7);
    
    switch(strength) {
        case 0:
        case 1:
            strengthLabel = 'Very Weak';
            strengthColor = '#ff4444';
            strengthBar.style.width = '14%';
            break;
        case 2:
            strengthLabel = 'Weak';
            strengthColor = '#ff8800';
            strengthBar.style.width = '28%';
            break;
        case 3:
            strengthLabel = 'Fair';
            strengthColor = '#ffbb00';
            strengthBar.style.width = '42%';
            break;
        case 4:
            strengthLabel = 'Good';
            strengthColor = '#88bb00';
            strengthBar.style.width = '56%';
            break;
        case 5:
            strengthLabel = 'Strong';
            strengthColor = '#00bb00';
            strengthBar.style.width = '70%';
            break;
        case 6:
            strengthLabel = 'Very Strong';
            strengthColor = '#00aa00';
            strengthBar.style.width = '85%';
            break;
        case 7:
            strengthLabel = 'Excellent';
            strengthColor = '#008800';
            strengthBar.style.width = '100%';
            break;
    }
    
    strengthBar.style.backgroundColor = strengthColor;
    strengthText.textContent = strengthLabel;
    strengthText.style.color = strengthColor;
    
    // Add visual feedback with animation
    strengthBar.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
    
    // Update password requirements checklist
    updatePasswordRequirements(password);
    
    // Show requirements when user starts typing
    if (password.length > 0) {
        document.getElementById('passwordRequirements').style.display = 'block';
    }
});

// Function to update password requirements checklist
function updatePasswordRequirements(password) {
    const requirements = {
        'req-length': password.length >= 8,
        'req-uppercase': /[A-Z]/.test(password),
        'req-lowercase': /[a-z]/.test(password),
        'req-number': /[0-9]/.test(password),
        'req-special': /[^A-Za-z0-9]/.test(password)
    };
    
    Object.keys(requirements).forEach(reqId => {
        const reqElement = document.getElementById(reqId);
        if (reqElement) {
            const icon = reqElement.querySelector('i');
            const span = reqElement.querySelector('span');
            
            if (requirements[reqId]) {
                icon.className = 'fas fa-check';
                icon.style.color = '#00bb00';
                span.style.color = '#00bb00';
                reqElement.style.opacity = '1';
            } else {
                icon.className = 'fas fa-times';
                icon.style.color = '#ff4444';
                span.style.color = '#666';
                reqElement.style.opacity = '0.7';
            }
        }
    });
}

// Function to toggle password requirements visibility
function togglePasswordRequirements() {
    const requirements = document.getElementById('passwordRequirements');
    const toggleBtn = document.querySelector('.toggle-requirements i');
    const requirementsList = document.querySelector('.requirements-list');
    
    if (requirementsList.style.display === 'none' || requirementsList.style.display === '') {
        requirementsList.style.display = 'block';
        toggleBtn.className = 'fas fa-chevron-down';
    } else {
        requirementsList.style.display = 'none';
        toggleBtn.className = 'fas fa-chevron-up';
    }
}

// Function to reset password requirements checklist
function resetPasswordRequirements() {
    const requirements = ['req-length', 'req-uppercase', 'req-lowercase', 'req-number', 'req-special'];
    
    requirements.forEach(reqId => {
        const reqElement = document.getElementById(reqId);
        if (reqElement) {
            const icon = reqElement.querySelector('i');
            const span = reqElement.querySelector('span');
            
            icon.className = 'fas fa-times';
            icon.style.color = '#ff4444';
            span.style.color = '#666';
            reqElement.style.opacity = '0.7';
        }
    });
}

// Handle student registration form submission
document.getElementById('register-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    // Validate passwords match
    if (password !== confirmPassword) {
        showMessage('Registration Failed', 'Passwords do not match!', 'error');
        return false;
    }
    
    // Enhanced password strength validation
    if (password.length < 8) {
        showMessage('Registration Failed', 'Password must be at least 8 characters long!', 'error');
        return false;
    }
    
    if (!/[A-Z]/.test(password)) {
        showMessage('Registration Failed', 'Password must contain at least one uppercase letter!', 'error');
        return false;
    }
    
    if (!/[a-z]/.test(password)) {
        showMessage('Registration Failed', 'Password must contain at least one lowercase letter!', 'error');
        return false;
    }
    
    if (!/[0-9]/.test(password)) {
        showMessage('Registration Failed', 'Password must contain at least one number!', 'error');
        return false;
    }
    
    if (!/[^A-Za-z0-9]/.test(password)) {
        showMessage('Registration Failed', 'Password must contain at least one special character!', 'error');
        return false;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Creating Account...';
    submitBtn.disabled = true;
    
    const formData = new FormData(this);
    
    // Debug: Log form data being sent
    console.log('Form data being sent:');
    for (let [key, value] of formData.entries()) {
        console.log(key + ': ' + value);
    }
    
    fetch('../BACKEND/register.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        // Check if response is actually JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                console.error('Non-JSON response received:', text);
                throw new Error('Server returned an invalid response. Please try again.');
            });
        }
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Registration response:', data);
        if (data.success) {
            showMessage('Registration Successful!', data.message || 'Your account has been created successfully. You will be redirected to the login page.', 'success');
            document.getElementById('register-form').reset();
            // Reset password strength indicator
            const strengthBar = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');
            strengthBar.style.width = '0%';
            strengthBar.style.backgroundColor = '#e0e0e0';
            strengthText.textContent = 'Password strength';
            strengthText.style.color = '#666';
            
            // Reset password requirements checklist
            resetPasswordRequirements();
            
            // Redirect to login page after brief transition
            try {
                const overlay = document.querySelector('.transition-overlay');
                if (overlay) overlay.style.opacity = '1';
            } catch (e) { /* no-op */ }
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 600);
        } else {
            // Restore button state on error
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
            showMessage('Registration Failed', data.error || 'An error occurred during registration.', 'error');
        }
    })
    .catch(error => {
        console.error('Registration error:', error);
        // Restore button state on error
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
        showMessage('Registration Failed', 'An error occurred: ' + error.message, 'error');
    });
});

function showMessage(title, description, type) {
    // Prevent body scroll
    preventBodyScroll();
    
    // Remove any existing modals first
    const existingModal = document.querySelector('.message-popup');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Create modal notification element
    const notification = document.createElement('div');
    notification.className = `message-popup ${type}`;
    notification.setAttribute('role', 'dialog');
    notification.setAttribute('aria-modal', 'true');
    notification.setAttribute('aria-labelledby', 'modal-title');
    notification.setAttribute('tabindex', '-1');
    notification.innerHTML = `
        <div class="message-popup-content">
            <div class="message-content">
                <div class="message-icon">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                </div>
                <div class="message-text">
                    <h3 id="modal-title">${title}</h3>
                    <p>${description}</p>
                </div>
                <button class="message-close" onclick="closeMessageModal()" aria-label="Close modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Show modal with animation
    setTimeout(() => {
        notification.classList.add('show');
        // Move focus to modal for accessibility
        notification.focus();
    }, 10);
    
    // For success messages, don't auto-remove (let the redirect handle it)
    if (type !== 'success') {
        // Remove error messages after 5 seconds
        setTimeout(() => {
            closeMessageModal();
        }, 5000);
    }
}

function closeMessageModal() {
    const notification = document.querySelector('.message-popup');
    if (notification) {
        notification.classList.remove('show');
        // Restore body scroll
        restoreBodyScroll();
        setTimeout(() => notification.remove(), 300);
    }
}

// Disable closing when clicking outside to keep true modal behavior

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeMessageModal();
    }
});

// Prevent body scroll when modal is open
function preventBodyScroll() {
    document.body.style.overflow = 'hidden';
}

function restoreBodyScroll() {
    document.body.style.overflow = '';
}

// Make functions globally accessible
window.togglePasswordRequirements = togglePasswordRequirements;
window.closeMessageModal = closeMessageModal;
