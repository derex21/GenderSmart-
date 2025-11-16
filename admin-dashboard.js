// Admin Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize dashboard
    initializeDashboard();
    
    // Load dashboard data from backend immediately
    loadDashboardData();
    
    // Setup event listeners
    setupEventListeners();
});

function initializeDashboard() {
    // Setup sidebar navigation
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
        item.addEventListener('click', function() {
            const section = this.getAttribute('data-section');
            showSection(section);
        });
    });
    
    // Setup sidebar toggle
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }
}

function showSection(sectionName) {
    // Hide all sections
    const sections = document.querySelectorAll('.content-section');
    sections.forEach(section => {
        section.classList.remove('active');
    });
    
    // Show selected section
    const targetSection = document.getElementById(sectionName);
    if (targetSection) {
        targetSection.classList.add('active');
    }
    
    // Update navigation
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
        item.classList.remove('active');
        if (item.getAttribute('data-section') === sectionName) {
            item.classList.add('active');
        }
    });
    
    // Update page title
    const pageTitle = document.getElementById('page-title');
    if (pageTitle) {
        pageTitle.textContent = sectionName.charAt(0).toUpperCase() + sectionName.slice(1);
    }
    
    // Load data for the section
    switch(sectionName) {
        case 'faculty':
            loadFacultyData();
            break;
        case 'admins':
            loadAdminData();
            break;
        case 'webinars':
            loadWebinarData();
            break;
    }
}

// Make showSection globally accessible for onclick handlers
window.showSection = showSection;

function toggleSidebar() {
    const sidebar = document.querySelector('.admin-sidebar');
    sidebar.classList.toggle('collapsed');
}

function setupEventListeners() {
    // Faculty form
    const facultyForm = document.getElementById('faculty-form');
    if (facultyForm) {
        facultyForm.addEventListener('submit', handleFacultySubmit);
    }
    
    // Edit faculty form
    const editFacultyForm = document.getElementById('edit-faculty-form');
    if (editFacultyForm) {
        editFacultyForm.addEventListener('submit', handleEditFacultySubmit);
    }
    
    // Admin form
    const adminForm = document.getElementById('admin-form');
    if (adminForm) {
        adminForm.addEventListener('submit', handleAdminSubmit);
    }
    
    // Edit admin form
    const editAdminForm = document.getElementById('edit-admin-form');
    if (editAdminForm) {
        editAdminForm.addEventListener('submit', handleEditAdminSubmit);
    }
    
    // Webinar form
    const webinarForm = document.getElementById('webinar-form');
    if (webinarForm) {
        webinarForm.addEventListener('submit', handleWebinarSubmit);
    }
    
    // Profile form
    const profileForm = document.getElementById('profile-form');
    if (profileForm) {
        profileForm.addEventListener('submit', handleProfileSubmit);
    }
}

// Faculty Management
function loadFacultyData() {
    fetch('../BACKEND/admin_api.php?action=get_faculty')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayFacultyData(data.data);
            } else {
                showNotification('Error loading faculty data: ' + data.error, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error loading faculty data', 'error');
        });
}

function displayFacultyData(faculty) {
    const tbody = document.getElementById('faculty-table-body');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    faculty.forEach(faculty => {
        // Format courses for display (replace | with line break or comma)
        const courses = (faculty.course || '').split(' | ').filter(c => c.trim());
        const courseDisplay = courses.length > 1 ? courses.join('<br>') : courses[0] || '';
        
        // Format year levels for display
        const years = (faculty.year_level || '').split(' | ').filter(y => y.trim());
        const yearDisplay = years.length > 1 ? years.join('<br>') : years[0] || '';
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${faculty.faculty_id}</td>
            <td>${faculty.full_name}</td>
            <td>${faculty.email}</td>
            <td>${courseDisplay}</td>
            <td>${yearDisplay}</td>
            <td>
                <span class="status-badge ${faculty.is_active ? 'active' : 'inactive'}">
                    ${faculty.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="editFaculty(${faculty.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteFaculty(${faculty.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function handleFacultySubmit(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    
    // Validate required fields
    const course = formData.get('course');
    const yearLevel = formData.get('year_level');
    
    if (!course || course.trim() === '') {
        showNotification('Please select a course', 'error');
        return;
    }
    
    if (!yearLevel || yearLevel.trim() === '') {
        showNotification('Please select a year level', 'error');
        return;
    }
    
    // Send the form data
    fetch('../BACKEND/admin_api.php?action=add_faculty', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            closeModal('faculty-modal');
            e.target.reset();
            loadFacultyData();
            loadDashboardData(); // Reload dashboard stats
        } else {
            showNotification(data.error, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error adding faculty', 'error');
    });
}

function editFaculty(id) {
    // Fetch faculty data and populate edit form
    fetch('../BACKEND/admin_api.php?action=get_faculty')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const faculty = data.data.find(f => f.id == id);
                if (faculty) {
                    // Populate edit form
                    document.getElementById('edit-faculty-id').value = faculty.id;
                    document.getElementById('edit-faculty-id-input').value = faculty.faculty_id;
                    document.getElementById('edit-faculty-name').value = faculty.full_name;
                    document.getElementById('edit-faculty-email').value = faculty.email;
                    document.getElementById('edit-faculty-status').value = faculty.is_active;
                    
                    // Get course (take first if multiple exist)
                    const course = (faculty.course || '').split(' | ').filter(c => c.trim())[0] || '';
                    document.getElementById('edit-faculty-course').value = course;
                    
                    // Get year level (take first if multiple exist)
                    const yearLevel = (faculty.year_level || '').split(' | ').filter(y => y.trim())[0] || '';
                    document.getElementById('edit-faculty-year').value = yearLevel;
                    
                    // Show edit modal
                    document.getElementById('edit-faculty-modal').style.display = 'flex';
                } else {
                    showNotification('Faculty not found', 'error');
                }
            } else {
                showNotification('Error loading faculty data', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error loading faculty data', 'error');
        });
}

function handleEditFacultySubmit(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    
    // Validate required fields
    const course = formData.get('course');
    const yearLevel = formData.get('year_level');
    
    if (!course || course.trim() === '') {
        showNotification('Please select a course', 'error');
        return;
    }
    
    if (!yearLevel || yearLevel.trim() === '') {
        showNotification('Please select a year level', 'error');
        return;
    }
    
    fetch('../BACKEND/admin_api.php?action=update_faculty', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            closeModal('edit-faculty-modal');
            e.target.reset();
            loadFacultyData();
            loadDashboardData(); // Reload dashboard stats
        } else {
            showNotification(data.error, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error updating faculty', 'error');
    });
}

// Store the faculty ID to delete
let facultyToDelete = null;

function deleteFaculty(id) {
    facultyToDelete = id;
    document.getElementById('delete-faculty-modal').style.display = 'flex';
}

function confirmDeleteFaculty() {
    if (facultyToDelete) {
        const formData = new FormData();
        formData.append('id', facultyToDelete);
        
        fetch('../BACKEND/admin_api.php?action=delete_faculty', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                loadFacultyData();
                loadDashboardData(); // Reload dashboard stats
            } else {
                showNotification(data.error, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error deleting faculty', 'error');
        })
        .finally(() => {
            closeModal('delete-faculty-modal');
            facultyToDelete = null;
        });
    }
}

// Admin Management
function loadAdminData() {
    fetch('../BACKEND/admin_api.php?action=get_admins')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayAdminData(data.data);
            } else {
                showNotification('Error loading admin data: ' + data.error, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error loading admin data', 'error');
        });
}

function displayAdminData(admins) {
    const tbody = document.getElementById('admin-table-body');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    admins.forEach(admin => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${admin.username}</td>
            <td>${admin.full_name}</td>
            <td>${admin.email}</td>
            <td>
                <span class="role-badge ${admin.role}">
                    ${admin.role.replace('_', ' ').toUpperCase()}
                </span>
            </td>
            <td>${admin.last_login ? new Date(admin.last_login).toLocaleDateString() : 'Never'}</td>
            <td>
                <span class="status-badge ${admin.is_active ? 'active' : 'inactive'}">
                    ${admin.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="editAdmin(${admin.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteAdmin(${admin.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function handleAdminSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    
    fetch('../BACKEND/admin_api.php?action=add_admin', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            closeModal('admin-modal');
            e.target.reset();
            loadAdminData();
            loadDashboardData(); // Reload dashboard stats
        } else {
            showNotification(data.error, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error adding admin', 'error');
    });
}

function handleEditAdminSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    
    fetch('../BACKEND/admin_api.php?action=update_admin', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            closeModal('edit-admin-modal');
            e.target.reset();
            loadAdminData();
            loadDashboardData(); // Reload dashboard stats
        } else {
            showNotification(data.error, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error updating admin', 'error');
    });
}

// Store the admin ID to delete
let adminToDelete = null;

function editAdmin(id) {
    // Fetch admin data and populate edit form
    fetch('../BACKEND/admin_api.php?action=get_admins')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const admin = data.data.find(a => a.id == id);
                if (admin) {
                    // Populate edit form
                    document.getElementById('edit-admin-id').value = admin.id;
                    document.getElementById('edit-admin-username').value = admin.username;
                    document.getElementById('edit-admin-name').value = admin.full_name;
                    document.getElementById('edit-admin-email').value = admin.email;
                    document.getElementById('edit-admin-role').value = admin.role;
                    document.getElementById('edit-admin-status').value = admin.is_active;
                    
                    // Show edit modal
                    document.getElementById('edit-admin-modal').style.display = 'flex';
                } else {
                    showNotification('Admin not found', 'error');
                }
            } else {
                showNotification('Error loading admin data', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error loading admin data', 'error');
        });
}

function deleteAdmin(id) {
    // Fetch admin data to show in confirmation modal
    fetch('../BACKEND/admin_api.php?action=get_admins')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const admin = data.data.find(a => a.id == id);
                if (admin) {
                    adminToDelete = id;
                    
                    // Display admin info in the modal
                    const infoPreview = document.getElementById('admin-to-delete-info');
                    infoPreview.innerHTML = `
                        <div class="admin-preview-item">
                            <span class="preview-label">Username:</span>
                            <span class="preview-value">${admin.username}</span>
                        </div>
                        <div class="admin-preview-item">
                            <span class="preview-label">Full Name:</span>
                            <span class="preview-value">${admin.full_name}</span>
                        </div>
                        <div class="admin-preview-item">
                            <span class="preview-label">Email:</span>
                            <span class="preview-value">${admin.email}</span>
                        </div>
                        <div class="admin-preview-item">
                            <span class="preview-label">Role:</span>
                            <span class="preview-value">${admin.role.replace('_', ' ').toUpperCase()}</span>
                        </div>
                    `;
                    
                    // Show delete modal
                    document.getElementById('delete-admin-modal').style.display = 'flex';
                } else {
                    showNotification('Admin not found', 'error');
                }
            } else {
                showNotification('Error loading admin data', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error loading admin data', 'error');
        });
}

function confirmDeleteAdmin() {
    if (adminToDelete) {
        const formData = new FormData();
        formData.append('id', adminToDelete);
        
        fetch('../BACKEND/admin_api.php?action=delete_admin', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                loadAdminData();
                loadDashboardData(); // Reload dashboard stats
            } else {
                showNotification(data.error, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error deleting admin', 'error');
        })
        .finally(() => {
            closeModal('delete-admin-modal');
            adminToDelete = null;
        });
    }
}

// Webinar Management
function loadWebinarData() {
    fetch('../BACKEND/admin_api.php?action=get_webinars')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayWebinarData(data.data);
            } else {
                showNotification('Error loading webinar data: ' + data.error, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error loading webinar data', 'error');
        });
}

function displayWebinarData(webinars) {
    const tbody = document.getElementById('webinar-table-body');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    webinars.forEach(webinar => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${webinar.title}</td>
            <td>${webinar.speaker_name}</td>
            <td>${new Date(webinar.webinar_date).toLocaleDateString()}</td>
            <td>
                <span class="category-badge ${webinar.category}">
                    ${webinar.category.toUpperCase()}
                </span>
            </td>
            <td>
                <span class="status-badge ${webinar.is_active ? 'active' : 'inactive'}">
                    ${webinar.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="editWebinar(${webinar.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteWebinar(${webinar.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function handleWebinarSubmit(e) {
    e.preventDefault();
    
    console.log('Webinar form submitted');
    
    const formData = new FormData(e.target);
    
    // Debug: Log form data
    console.log('Form data being sent:');
    for (let [key, value] of formData.entries()) {
        console.log(key + ': ' + value);
    }
    
    fetch('../BACKEND/admin_api.php?action=add_webinar', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        return response.json();
    })
    .then(data => {
        console.log('API response:', data);
        if (data.success) {
            showNotification(data.message, 'success');
            closeModal('webinar-modal');
            e.target.reset();
            loadWebinarData();
            loadDashboardData(); // Reload dashboard stats
        } else {
            showNotification(data.error, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error adding webinar: ' + error.message, 'error');
    });
}

function editWebinar(id) {
    // Implementation for editing webinar
    showNotification('Edit functionality coming soon', 'info');
}

function deleteWebinar(id) {
    if (confirm('Are you sure you want to delete this webinar?')) {
        const formData = new FormData();
        formData.append('id', id);
        
        fetch('../BACKEND/admin_api.php?action=delete_webinar', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                loadWebinarData();
                loadDashboardData(); // Reload dashboard stats
            } else {
                showNotification(data.error, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error deleting webinar', 'error');
        });
    }
}

// Profile Management
function handleProfileSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    
    fetch('../BACKEND/admin_api.php?action=update_profile', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
        } else {
            showNotification(data.error, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error updating profile', 'error');
    });
}

// Modal Functions
function openFacultyModal() {
    document.getElementById('faculty-modal').style.display = 'flex';
}

function openAdminModal() {
    document.getElementById('admin-modal').style.display = 'flex';
}

function openWebinarModal() {
    document.getElementById('webinar-modal').style.display = 'flex';
}

// Make modal functions globally accessible
window.openFacultyModal = openFacultyModal;
window.openAdminModal = openAdminModal;
window.openWebinarModal = openWebinarModal;

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Make closeModal globally accessible
window.closeModal = closeModal;

// Notification System
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="notification-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

function getNotificationIcon(type) {
    switch(type) {
        case 'success': return 'check-circle';
        case 'error': return 'exclamation-triangle';
        case 'warning': return 'exclamation-circle';
        default: return 'info-circle';
    }
}

// Load dashboard data from backend
function loadDashboardData() {
    console.log('Loading dashboard data...');
    
    // Load dashboard statistics
    fetch('../BACKEND/admin_api.php?action=get_dashboard_stats')
        .then(response => {
            console.log('Dashboard stats response:', response);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Dashboard stats data:', data);
            if (data.success) {
                updateDashboardStats(data.data);
            } else {
                console.error('Error loading dashboard stats:', data.error);
                // Set default values on error without showing notification to avoid spam
                updateDashboardStats({
                    faculty_count: 0,
                    admin_count: 0,
                    webinar_count: 0
                });
            }
        })
        .catch(error => {
            console.error('Error loading dashboard stats:', error);
            // Set default values on error without showing notification to avoid spam
            updateDashboardStats({
                faculty_count: 0,
                admin_count: 0,
                webinar_count: 0
            });
        });

    // Load recent faculty
    loadRecentFaculty();
    
    // Load recent webinars
    loadRecentWebinars();
    
    // Load admin profile info
    loadAdminProfile();
}

// Load admin profile information
function loadAdminProfile() {
    fetch('../BACKEND/admin_api.php?action=get_profile')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateAdminProfile(data.data);
            } else {
                console.error('Error loading admin profile:', data.error);
            }
        })
        .catch(error => {
            console.error('Error loading admin profile:', error);
        });
}

function updateAdminProfile(adminInfo) {
    // Update admin info in sidebar and header
    const adminName = adminInfo.full_name || adminInfo.username || 'Admin';
    const adminRole = adminInfo.role || 'admin';
    
    if (document.getElementById('admin-name')) {
        document.getElementById('admin-name').textContent = adminName;
    }
    if (document.getElementById('admin-role')) {
        document.getElementById('admin-role').textContent = adminRole.charAt(0).toUpperCase() + adminRole.slice(1);
    }
    if (document.getElementById('admin-welcome')) {
        document.getElementById('admin-welcome').textContent = `Welcome back, ${adminName}!`;
    }
    if (document.getElementById('profile-name')) {
        document.getElementById('profile-name').textContent = adminName;
    }
    if (document.getElementById('profile-role')) {
        document.getElementById('profile-role').textContent = `${adminRole.charAt(0).toUpperCase() + adminRole.slice(1)} Administrator`;
    }
    if (document.getElementById('profile-full-name')) {
        document.getElementById('profile-full-name').value = adminName;
    }
    if (document.getElementById('profile-email')) {
        document.getElementById('profile-email').value = adminInfo.email || '';
    }
}

// Dashboard Statistics Functions
function loadDashboardStats() {
    fetch('../BACKEND/admin_api.php?action=get_dashboard_stats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateDashboardStats(data.data);
            } else {
                console.error('Error loading dashboard stats:', data.error);
            }
        })
        .catch(error => {
            console.error('Error loading dashboard stats:', error);
        });
}

// Make updateDashboardStats globally available for inline scripts
function updateDashboardStats(stats) {
    console.log('Updating dashboard stats:', stats);
    
    if (!stats) {
        console.error('No stats data provided');
        // Set all counts to 0 if no data
        animateCount('faculty-count', 0);
        animateCount('admin-count', 0);
        animateCount('webinar-count', 0);
        return;
    }
    
    // Animate faculty count - handle different property names
    const facultyCount = stats.faculty_count || stats.facultyCount || 0;
    console.log('Setting faculty count to:', facultyCount);
    animateCount('faculty-count', facultyCount);
    
    // Animate admin count - handle different property names
    const adminCount = stats.admin_count || stats.adminCount || 0;
    console.log('Setting admin count to:', adminCount);
    animateCount('admin-count', adminCount);
    
    // Animate webinar count - handle different property names
    const webinarCount = stats.webinar_count || stats.webinarCount || 0;
    console.log('Setting webinar count to:', webinarCount);
    animateCount('webinar-count', webinarCount);
}

// Add smooth counting animation
function animateCount(elementId, targetValue) {
    const element = document.getElementById(elementId);
    if (!element) {
        console.error('Element not found:', elementId);
        return;
    }
    
    // Ensure targetValue is a number
    targetValue = parseInt(targetValue) || 0;
    
    // Clear any loading spinner or existing content
    element.innerHTML = '';
    
    const duration = 1000; // 1 second animation
    const startValue = 0; // Always start from 0 for better UX
    
    // If target is 0, just set it immediately
    if (targetValue === 0) {
        element.textContent = targetValue;
        return;
    }
    
    const increment = 1;
    const steps = targetValue;
    const stepDuration = steps > 0 ? Math.max(50, duration / steps) : 0; // Minimum 50ms per step
    
    let currentValue = 0;
    const timer = setInterval(() => {
        currentValue += increment;
        element.textContent = currentValue;
        
        if (currentValue >= targetValue) {
            clearInterval(timer);
            element.textContent = targetValue;
        }
    }, stepDuration);
}

// Make it globally accessible
window.updateDashboardStats = updateDashboardStats;
window.animateCount = animateCount;

function loadRecentFaculty() {
    fetch('../BACKEND/admin_api.php?action=get_recent_faculty')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayRecentFaculty(data.data);
            } else {
                console.error('Error loading recent faculty:', data.error);
            }
        })
        .catch(error => {
            console.error('Error loading recent faculty:', error);
        });
}

function displayRecentFaculty(faculty) {
    const facultyList = document.getElementById('recent-faculty-list');
    if (!facultyList) return;
    
    if (faculty.length === 0) {
        facultyList.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-user-plus"></i>
                <p>No faculty accounts yet</p>
            </div>
        `;
        return;
    }
    
    facultyList.innerHTML = '';
    faculty.forEach(member => {
        const facultyItem = document.createElement('div');
        facultyItem.className = 'faculty-item';
        // Format courses for display
        const courses = (member.course || '').split(' | ').filter(c => c.trim());
        const courseDisplay = courses.length > 1 ? courses.join(', ') : courses[0] || '';
        
        facultyItem.innerHTML = `
            <div class="faculty-info">
                <div class="faculty-name">${member.full_name}</div>
                <div class="faculty-details">${member.faculty_id} • ${courseDisplay}</div>
            </div>
            <div class="faculty-status">
                <span class="status-badge ${member.is_active ? 'active' : 'inactive'}">
                    ${member.is_active ? 'Active' : 'Inactive'}
                </span>
            </div>
        `;
        facultyList.appendChild(facultyItem);
    });
}

function loadRecentWebinars() {
    fetch('../BACKEND/admin_api.php?action=get_recent_webinars')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayRecentWebinars(data.data);
            } else {
                console.error('Error loading recent webinars:', data.error);
            }
        })
        .catch(error => {
            console.error('Error loading recent webinars:', error);
        });
}

function displayRecentWebinars(webinars) {
    const webinarList = document.getElementById('recent-webinar-list');
    if (!webinarList) return;
    
    if (webinars.length === 0) {
        webinarList.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-video"></i>
                <p>No webinars yet</p>
            </div>
        `;
        return;
    }
    
    webinarList.innerHTML = '';
    webinars.forEach(webinar => {
        const webinarItem = document.createElement('div');
        webinarItem.className = 'webinar-item';
        
        const webinarDate = new Date(webinar.webinar_date);
        const formattedDate = webinarDate.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
        
        webinarItem.innerHTML = `
            <div class="webinar-info">
                <div class="webinar-title">${webinar.title}</div>
                <div class="webinar-details">${webinar.speaker_name} • ${formattedDate}</div>
            </div>
            <div class="webinar-category">
                <span class="category-badge ${webinar.category}">
                    ${webinar.category.charAt(0).toUpperCase() + webinar.category.slice(1)}
                </span>
            </div>
        `;
        webinarList.appendChild(webinarItem);
    });
}

// Logout Modal Functions
function showLogoutModal() {
    document.getElementById('logout-modal').style.display = 'flex';
}

// Make showLogoutModal globally accessible
window.showLogoutModal = showLogoutModal;

function confirmLogout() {
    // Redirect to logout page
    window.location.href = '../BACKEND/admin_logout.php';
}

// Make confirmLogout globally accessible
window.confirmLogout = confirmLogout;

// Make faculty functions globally accessible
window.editFaculty = editFaculty;
window.deleteFaculty = deleteFaculty;
window.confirmDeleteFaculty = confirmDeleteFaculty;

// Make admin functions globally accessible
window.editAdmin = editAdmin;
window.deleteAdmin = deleteAdmin;
window.confirmDeleteAdmin = confirmDeleteAdmin;

// Close modals when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        closeModal(e.target.id);
    }
});
