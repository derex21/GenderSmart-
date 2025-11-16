// Faculty Login Enhanced JavaScript

// Faculty-specific error popup functionality
function closeErrorPopup() {
    const popup = document.getElementById('errorPopup');
    if (!popup) return;
    popup.style.transition = 'opacity 250ms ease-out, transform 250ms ease-out';
    popup.style.opacity = '0';
    popup.style.transform = 'translateY(-8px)';
    setTimeout(() => { popup.style.display = 'none'; }, 260);
}

// Auto-hide error popup after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const popup = document.getElementById('errorPopup');
    if (!popup) return;
    popup.style.opacity = '0';
    popup.style.transform = 'translateY(-8px)';
    // Start fully visible after frame, then fade out after delay
    requestAnimationFrame(() => {
        popup.style.opacity = '1';
        popup.style.transform = 'translateY(0)';
    });
    setTimeout(() => {
        closeErrorPopup();
    }, 4200);
});

// Enhanced Message Popup System
function showMessage(title, description, type) {
    // Create modal notification element
    const notification = document.createElement('div');
    notification.className = `message-popup ${type}`;
    notification.innerHTML = `
        <div class="message-popup-content" role="alertdialog" aria-modal="true" aria-labelledby="message-popup-title" aria-describedby="message-popup-desc">
            <div class="message-content">
                <div class="message-icon">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}" aria-hidden="true"></i>
                </div>
                <div class="message-text">
                    <h3 id="message-popup-title">${title}</h3>
                    <p id="message-popup-desc">${description}</p>
                </div>
                <button class="message-close" onclick="closeMessageModal()" aria-label="Close notification">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Show modal with animation
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
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
        setTimeout(() => notification.remove(), 300);
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('message-popup')) {
        closeMessageModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeMessageModal();
    }
});

// Handle faculty login form submission
document.getElementById('faculty-login-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Show loading state
    const submitBtn = this.querySelector('.signin-btn');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Signing In...';
    submitBtn.disabled = true;
    
    fetch('../BACKEND/faculty_login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Login Successful!', 'Redirecting to dashboard...', 'success');
            setTimeout(() => {
                window.location.href = 'faculty_dashboard.php';
            }, 1500);
        } else {
            // Restore button state on error
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
            showMessage('Login Failed', data.error, 'error');
        }
    })
    .catch(error => {
        // Restore button state on error
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
        showMessage('Login Failed', 'An error occurred. Please try again.', 'error');
    });
});

// Add enhanced styling for message popups
const style = document.createElement('style');
style.textContent = `
    /* Enhanced Message Popup Styling */
    .message-popup {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(20px);
        opacity: 0;
        visibility: hidden;
        transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    .message-popup.show {
        opacity: 1;
        visibility: visible;
    }

    .message-popup-content {
        min-width: 320px;
        max-width: 400px;
        width: 90%;
        margin: 20px;
        background: linear-gradient(135deg, #4CAF50, #45a049, #2E7D32);
        border-radius: 20px;
        overflow: hidden;
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 30px 60px rgba(0, 0, 0, 0.5);
        transform: scale(0.6) translateY(30px);
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        position: relative;
    }

    .message-popup.show .message-popup-content {
        transform: scale(1) translateY(0);
    }

    .message-popup.error .message-popup-content {
        background: linear-gradient(135deg, #ff6b6b, #ee5a52, #c0392b);
    }

    .message-popup-content::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%);
        animation: shimmer 3s infinite;
    }

    .message-popup.success .message-popup-content::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
        background-size: 20px 20px;
        animation: particleFloat 8s linear infinite;
        pointer-events: none;
    }

    .message-content {
        display: flex;
        align-items: center;
        padding: 18px;
        gap: 14px;
        position: relative;
        z-index: 2;
    }

    .message-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        position: relative;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.25);
        backdrop-filter: blur(10px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        animation: successIconFloat 3s ease-in-out infinite;
    }

    .message-popup.error .message-icon {
        animation: errorIconFloat 3s ease-in-out infinite;
    }

    .message-icon i {
        font-size: 24px;
        color: #fff;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        animation: iconBounce 2s ease-in-out infinite;
    }

    .message-text {
        flex: 1;
        color: #fff;
    }

    .message-text h3 {
        margin: 0 0 6px 0;
        font-size: 16px;
        font-weight: 700;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        letter-spacing: 0.5px;
    }

    .message-text p {
        margin: 0;
        font-size: 13px;
        opacity: 0.95;
        line-height: 1.4;
        font-weight: 500;
    }

    .message-close {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: #fff;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        flex-shrink: 0;
    }

    .message-close:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: scale(1.1);
    }

    .message-close i {
        font-size: 12px;
    }

    /* Enhanced Animations */
    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }

    @keyframes successIconFloat {
        0%, 100% { 
            transform: translateY(0) scale(1);
            box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4);
        }
        50% { 
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 0 0 8px rgba(255, 255, 255, 0);
        }
    }

    @keyframes errorIconFloat {
        0%, 100% { 
            transform: translateY(0) scale(1);
            box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4);
        }
        50% { 
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 0 0 8px rgba(255, 255, 255, 0);
        }
    }

    @keyframes iconBounce {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }

    @keyframes particleFloat {
        0% {
            transform: translateY(0) rotate(0deg);
        }
        100% {
            transform: translateY(-100px) rotate(360deg);
        }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .message-popup-content {
            min-width: 280px;
            max-width: calc(100vw - 40px);
            margin: 0 20px;
        }
        
        .message-content {
            padding: 16px;
            gap: 12px;
        }
        
        .message-icon {
            width: 45px;
            height: 45px;
        }
        
        .message-icon i {
            font-size: 20px;
        }
        
        .message-text h3 {
            font-size: 15px;
        }
        
        .message-text p {
            font-size: 12px;
        }
    }
`;
document.head.appendChild(style);
