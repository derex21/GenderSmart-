// Faculty Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize dashboard
    initializeDashboard();
    
    // Load dashboard data from backend
    loadDashboardData();
    
    // Load initial data
    loadModules();
    loadActivities();
    
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

	// Global modal listeners: ESC to close and backdrop click to close
	document.addEventListener('keydown', function(e) {
		if (e.key === 'Escape') {
			const openModals = Array.from(document.querySelectorAll('.modal.show'));
			const topModal = openModals[openModals.length - 1];
			if (topModal) {
				closeModal(topModal.id);
			}
		}
	});

	document.addEventListener('click', function(e) {
		const modal = e.target.closest('.modal');
		if (modal && e.target === modal) {
			closeModal(modal.id);
		}
	});
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
        const titles = {
            'dashboard': 'Dashboard',
            'modules': 'Modules',
            'activities': 'Activities',
            'students': 'Student Management',
			'profile': 'My Profile',
			'analytics': 'Analytics',
			'leaderboard': 'Leaderboard'
        };
        pageTitle.textContent = titles[sectionName] || 'Dashboard';
    }
    
    // Load section-specific data
    if (sectionName === 'modules') {
        loadModules();
    } else if (sectionName === 'activities') {
        loadActivities();
    } else if (sectionName === 'analytics') {
        loadAnalytics();
    } else if (sectionName === 'leaderboard') {
        loadLeaderboard();
    } else if (sectionName === 'students') {
        loadStudentStats();
        loadStudentData('pending');
    }
}

function toggleSidebar() {
    const sidebar = document.querySelector('.faculty-sidebar');
    sidebar.classList.toggle('open');
}

function setupEventListeners() {
    // Add any additional event listeners here
}

// Load dashboard data from backend
function loadDashboardData() {
    fetch('../BACKEND/faculty_dashboard_backend.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateDashboardUI(data.data);
            } else {
                console.error('Error loading dashboard data:', data.error);
                showNotification('Error loading dashboard data', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading dashboard data:', error);
            showNotification('Error loading dashboard data', 'error');
        });
}

function updateDashboardUI(data) {
    // Update faculty info
    const facultyInfo = data.faculty_info;
    document.getElementById('faculty-name').textContent = facultyInfo.name;
    document.getElementById('faculty-welcome').textContent = `Welcome back, ${facultyInfo.name}!`;
    document.getElementById('profile-name').textContent = facultyInfo.name;
    document.getElementById('profile-faculty-id').textContent = facultyInfo.faculty_id;
    document.getElementById('profile-email').textContent = facultyInfo.email;
    document.getElementById('profile-course').textContent = facultyInfo.course;
    document.getElementById('profile-year-level').textContent = facultyInfo.year_level;

    // Update dashboard stats
    document.getElementById('faculty-course').textContent = facultyInfo.course;
    document.getElementById('faculty-year-level').textContent = facultyInfo.year_level;
    document.getElementById('faculty-id').textContent = facultyInfo.faculty_id;

    // Update recent modules and activities
    if (data.recent_modules) {
        displayRecentModules(data.recent_modules);
    }
    if (data.recent_activities) {
        displayRecentActivities(data.recent_activities);
    }
}

function displayRecentWebinars(webinars) {
    const webinarList = document.getElementById('recent-webinar-list');
    if (!webinarList) return;
    
    if (webinars.length === 0) {
        webinarList.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-video"></i>
                <p>No webinars available</p>
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
                <div class="webinar-details">
                    ${webinar.speaker_name} | ${formattedDate}
                </div>
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

function loadWebinars() {
    const webinarsGrid = document.getElementById('webinars-grid');
    if (!webinarsGrid) return;
    
    // Show loading spinner
    webinarsGrid.innerHTML = `
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Loading webinars...</p>
        </div>
    `;
    
    // Fetch webinars from the public API
    fetch('../BACKEND/get_webinars_simple.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayWebinars(data.data);
            } else {
                showErrorMessage('Error loading webinars: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error loading webinars:', error);
            showErrorMessage('Error loading webinars');
        });
}

function displayWebinars(webinars) {
    const webinarsGrid = document.getElementById('webinars-grid');
    if (!webinarsGrid) return;
    
    if (webinars.length === 0) {
        webinarsGrid.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-video"></i>
                <p>No webinars available</p>
            </div>
        `;
        return;
    }
    
    webinarsGrid.innerHTML = '';
    
    webinars.forEach(webinar => {
        const webinarCard = createWebinarCard(webinar);
        webinarsGrid.appendChild(webinarCard);
    });
}

function createWebinarCard(webinar) {
    const card = document.createElement('div');
    card.className = 'webinar-card';
    
    const webinarDate = new Date(webinar.webinar_date);
    const formattedDate = webinarDate.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit'
    });

    const isUpcoming = webinar.category === 'upcoming' && webinarDate > new Date();
    const actionButton = isUpcoming ? 
        `<button class="join-btn" onclick="openWebinarLink('${webinar.webinar_link}')">Join Webinar</button>` :
        `<button class="watch-btn" onclick="openWebinarLink('${webinar.webinar_link}')">Watch Recording</button>`;

    card.innerHTML = `
        <div class="webinar-card-content">
            <div class="webinar-header">
                <div class="webinar-date">${formattedDate}</div>
                <div class="webinar-category">
                    <span class="category-badge ${webinar.category}">
                        ${webinar.category.charAt(0).toUpperCase() + webinar.category.slice(1)}
                    </span>
                </div>
            </div>
            <div class="webinar-body">
                <h3 class="webinar-title">${webinar.title}</h3>
                <p class="webinar-description">${webinar.description || 'Join us for an informative session on gender and development topics.'}</p>
                <div class="webinar-meta">
                    <div class="webinar-speaker">
                        <i class="fas fa-user"></i>
                        <span>${webinar.speaker_name}</span>
                        ${webinar.speaker_title ? `<span class="speaker-title">${webinar.speaker_title}</span>` : ''}
                    </div>
                    <div class="webinar-duration">
                        <i class="fas fa-clock"></i>
                        <span>${webinar.duration} mins</span>
                    </div>
                </div>
            </div>
            <div class="webinar-footer">
                ${actionButton}
                ${webinar.google_form_link ? `<button class="form-btn" onclick="openFormLink('${webinar.google_form_link}')">Fill Form</button>` : ''}
            </div>
        </div>
    `;

    return card;
}

function openWebinarLink(link) {
    if (link && link.trim() !== '') {
        window.open(link, '_blank');
    } else {
        showNotification('Webinar link not available yet. Please check back later.', 'warning');
    }
}

function openFormLink(link) {
    if (link && link.trim() !== '') {
        window.open(link, '_blank');
    } else {
        showNotification('Form link not available yet. Please check back later.', 'warning');
    }
}

function showErrorMessage(message) {
    const webinarsGrid = document.getElementById('webinars-grid');
    if (webinarsGrid) {
        webinarsGrid.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Error Loading Webinars</h3>
                <p>${message}</p>
                <button onclick="loadWebinars()" class="retry-btn">Try Again</button>
            </div>
        `;
    }
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
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

// Add CSS for notifications and webinar cards
const style = document.createElement('style');
style.textContent = `
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        z-index: 1000;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideInRight 0.3s ease;
    }
    
    .notification.error {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    }
    
    .notification.warning {
        background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    }
    
    .notification.success {
        background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    }
    
    .notification-content {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .notification-close {
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        font-size: 14px;
        opacity: 0.8;
        transition: opacity 0.3s ease;
    }
    
    .notification-close:hover {
        opacity: 1;
    }
    
    .webinar-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
        margin-bottom: 20px;
    }
    
    .webinar-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    }
    
    .webinar-card-content {
        padding: 25px;
    }
    
    .webinar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .webinar-date {
        font-size: 14px;
        color: #7f8c8d;
        font-weight: 500;
    }
    
    .webinar-body {
        margin-bottom: 20px;
    }
    
    .webinar-title {
        font-size: 20px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 10px;
    }
    
    .webinar-description {
        color: #7f8c8d;
        line-height: 1.6;
        margin-bottom: 15px;
    }
    
    .webinar-meta {
        display: flex;
        gap: 20px;
        font-size: 14px;
        color: #7f8c8d;
    }
    
    .webinar-speaker, .webinar-duration {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .speaker-title {
        font-style: italic;
        margin-left: 5px;
    }
    
    .webinar-footer {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .join-btn, .watch-btn, .form-btn {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .join-btn {
        background: linear-gradient(135deg, #27ae60, #2ecc71);
        color: white;
    }
    
    .watch-btn {
        background: linear-gradient(135deg, #3498db, #2980b9);
        color: white;
    }
    
    .form-btn {
        background: linear-gradient(135deg, #9b59b6, #8e44ad);
        color: white;
    }
    
    .join-btn:hover, .watch-btn:hover, .form-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
    
    .retry-btn {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        margin-top: 10px;
    }
    
    .retry-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }
    
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
`;
document.head.appendChild(style);

// Student Management Functions
let currentStudentId = null;

function showStudentTab(tab) {
    // Hide all student lists
    document.querySelectorAll('.student-list').forEach(list => {
        list.style.display = 'none';
    });
    
    // Show selected tab
    document.getElementById(tab + '-students').style.display = 'block';
    
	// Update button states safely (no reliance on global event)
	const buttons = document.querySelectorAll('.btn-group .btn');
	buttons.forEach(btn => btn.classList.remove('active'));
	// Best-effort: mark button whose onclick calls this tab
	buttons.forEach(btn => {
		const onClick = btn.getAttribute('onclick') || '';
		if (onClick.includes(`showStudentTab('${tab}')`)) {
			btn.classList.add('active');
		}
	});
    
    // Load data for the selected tab
    loadStudentData(tab);
}

function loadStudentData(status) {
    fetch(`../BACKEND/faculty_student_api.php?action=get_${status}_students`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayStudents(data.data, status);
            } else {
                console.error('Error loading students:', data.error);
                showNotification('Error loading students: ' + data.error, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error loading students', 'error');
        });
}

function displayStudents(students, status) {
    const tbody = document.getElementById(`${status}-students-body`);
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    if (students.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">No students found</td></tr>';
        return;
    }
    
    students.forEach(student => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${student.student_id}</td>
            <td>${student.full_name}</td>
            <td>${student.gender}</td>
            <td>${student.course}</td>
            <td>${student.year_level}</td>
            <td>${formatDate(student.created_at)}</td>
            <td>
                ${getActionButtons(student, status)}
            </td>
        `;
        tbody.appendChild(row);
    });
}

function getActionButtons(student, status) {
    switch (status) {
        case 'pending':
            return `
                <button class="btn btn-success btn-sm" onclick="openAcceptModal(${student.id}, '${student.full_name}', '${student.student_id}')">
                    <i class="fas fa-check"></i> Accept
                </button>
                <button class="btn btn-danger btn-sm" onclick="openRejectModal(${student.id}, '${student.full_name}', '${student.student_id}')">
                    <i class="fas fa-times"></i> Reject
                </button>
            `;
        case 'accepted':
            return `<span class="badge badge-success">Accepted</span>`;
        case 'rejected':
            return `
                <button class="btn btn-warning btn-sm" onclick="openRestoreModal(${student.id}, '${student.full_name}', '${student.student_id}')">
                    <i class="fas fa-undo"></i> Restore
                </button>
            `;
        default:
            return '';
    }
}

function openAcceptModal(id, name, studentId) {
    currentStudentId = id;
    document.getElementById('accept-student-info').innerHTML = `
        <div class="student-details">
            <p><strong>Name:</strong> ${name}</p>
            <p><strong>Student ID:</strong> ${studentId}</p>
        </div>
    `;
	openModal('accept-student-modal');
}

function openRejectModal(id, name, studentId) {
    currentStudentId = id;
    document.getElementById('reject-student-info').innerHTML = `
        <div class="student-details">
            <p><strong>Name:</strong> ${name}</p>
            <p><strong>Student ID:</strong> ${studentId}</p>
        </div>
    `;
	openModal('reject-student-modal');
}

function openRestoreModal(id, name, studentId) {
    currentStudentId = id;
    document.getElementById('restore-student-info').innerHTML = `
        <div class="student-details">
            <p><strong>Name:</strong> ${name}</p>
            <p><strong>Student ID:</strong> ${studentId}</p>
        </div>
    `;
	openModal('restore-student-modal');
}

function acceptStudent() {
    if (!currentStudentId) return;
    
    const formData = new FormData();
    formData.append('student_id', currentStudentId);
    
    fetch('../BACKEND/faculty_student_api.php?action=accept_student', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Student accepted successfully!', 'success');
            closeModal('accept-student-modal');
            loadStudentData('pending');
            loadStudentStats();
        } else {
            showNotification('Error: ' + data.error, 'error');
        }
    })
    .catch(error => {
        showNotification('Error: ' + error, 'error');
    });
}

function rejectStudent() {
    if (!currentStudentId) return;
    
    const rejectionReason = document.getElementById('rejection-reason').value;
    if (!rejectionReason.trim()) {
        showNotification('Please provide a rejection reason', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('student_id', currentStudentId);
    formData.append('rejection_reason', rejectionReason);
    
    fetch('../BACKEND/faculty_student_api.php?action=reject_student', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Student rejected successfully!', 'success');
            closeModal('reject-student-modal');
            loadStudentData('pending');
            loadStudentStats();
        } else {
            showNotification('Error: ' + data.error, 'error');
        }
    })
    .catch(error => {
        showNotification('Error: ' + error, 'error');
    });
}

function restoreStudent() {
    if (!currentStudentId) return;
    
    const formData = new FormData();
    formData.append('student_id', currentStudentId);
    
    fetch('../BACKEND/faculty_student_api.php?action=restore_student', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Student restored successfully!', 'success');
            closeModal('restore-student-modal');
            loadStudentData('rejected');
            loadStudentStats();
        } else {
            showNotification('Error: ' + data.error, 'error');
        }
    })
    .catch(error => {
        showNotification('Error: ' + error, 'error');
    });
}

function loadStudentStats() {
    fetch('../BACKEND/faculty_student_api.php?action=get_student_stats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('pending-count').textContent = data.data.pending;
                document.getElementById('accepted-count').textContent = data.data.accepted;
                document.getElementById('rejected-count').textContent = data.data.rejected;
            }
        })
        .catch(error => {
            console.error('Error loading student stats:', error);
        });
}

// NOTE: closeModal defined later for shared usage

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

// Module Management Functions
function loadModules() {
    const modulesGrid = document.getElementById('modules-grid');
    const recentModuleList = document.getElementById('recent-module-list');
    
    if (modulesGrid) {
        modulesGrid.innerHTML = `
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading modules...</p>
            </div>
        `;
    }
    
    fetch('../BACKEND/faculty_modules_activities_api.php?action=get_modules')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayModules(data.modules);
                if (recentModuleList) {
                    displayRecentModules(data.modules.slice(0, 3));
                }
            } else {
                showNotification('Error loading modules: ' + data.error, 'error');
            }
        })
        .catch(error => {
            console.error('Error loading modules:', error);
            showNotification('Error loading modules', 'error');
        });
}

function displayModules(modules) {
    const modulesGrid = document.getElementById('modules-grid');
    if (!modulesGrid) return;
    
    if (modules.length === 0) {
        modulesGrid.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-book"></i>
                <h3>No Modules Found</h3>
                <p>Upload your first module to get started</p>
                <button class="btn btn-primary" onclick="openModuleUploadModal()">
                    <i class="fas fa-upload"></i>
                    Upload Module
                </button>
            </div>
        `;
        return;
    }
    
    modulesGrid.innerHTML = '';
    modules.forEach(module => {
        const moduleCard = createModuleCard(module);
        modulesGrid.appendChild(moduleCard);
    });
}

function displayRecentModules(modules) {
    const recentModuleList = document.getElementById('recent-module-list');
    if (!recentModuleList) return;
    
    if (modules.length === 0) {
        recentModuleList.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-book"></i>
                <p>No modules available</p>
            </div>
        `;
        return;
    }
    
    recentModuleList.innerHTML = '';
    modules.forEach(module => {
        const moduleItem = document.createElement('div');
        moduleItem.className = 'module-item';
        moduleItem.innerHTML = `
            <div class="module-info">
                <div class="module-title">${module.title}</div>
                <div class="module-details">
                    ${module.module_type.charAt(0).toUpperCase() + module.module_type.slice(1)} | ${formatDate(module.created_at)}
                </div>
            </div>
            <div class="module-status">
                <span class="status-badge ${module.is_active ? 'active' : 'inactive'}">
                    ${module.is_active ? 'Active' : 'Inactive'}
                </span>
            </div>
        `;
        recentModuleList.appendChild(moduleItem);
    });
}

function createModuleCard(module) {
    const card = document.createElement('div');
    card.className = 'module-card';
    
    const fileIcon = getFileIcon(module.file_type);
    const statusClass = module.is_active ? 'active' : 'inactive';
    
    card.innerHTML = `
        <div class="module-card-content">
            <div class="module-header">
                <div class="module-icon">
                    <i class="fas fa-${fileIcon}"></i>
                </div>
                <div class="module-meta">
                    <div class="module-type">${module.module_type.charAt(0).toUpperCase() + module.module_type.slice(1)}</div>
                    <div class="module-date">${formatDate(module.created_at)}</div>
                </div>
                <div class="module-status">
                    <span class="status-badge ${statusClass}">
                        ${module.is_active ? 'Active' : 'Inactive'}
                    </span>
                </div>
            </div>
            <div class="module-body">
                <h3 class="module-title">${module.title}</h3>
                <p class="module-description">${module.description}</p>
                <div class="module-details">
                    <div class="module-file">
                        <i class="fas fa-file"></i>
                        <span>${formatFileSize(module.file_size)}</span>
                    </div>
                    ${module.quiz_questions ? '<div class="module-quiz"><i class="fas fa-question-circle"></i><span>Has Quiz</span></div>' : ''}
                </div>
            </div>
            <div class="module-footer">
                <button class="btn btn-primary btn-sm" onclick="viewModule(${module.id})">
                    <i class="fas fa-eye"></i>
                    View
                </button>
                <button class="btn btn-secondary btn-sm" onclick="toggleModuleStatus(${module.id})">
                    <i class="fas fa-${module.is_active ? 'pause' : 'play'}"></i>
                    ${module.is_active ? 'Deactivate' : 'Activate'}
                </button>
                <button class="btn btn-danger btn-sm" onclick="deleteModule(${module.id})">
                    <i class="fas fa-trash"></i>
                    Delete
                </button>
            </div>
        </div>
    `;
    
    return card;
}

// Activity Management Functions
function loadActivities() {
    const activitiesGrid = document.getElementById('activities-grid');
    const recentActivityList = document.getElementById('recent-activity-list');
    
    if (activitiesGrid) {
        activitiesGrid.innerHTML = `
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading activities...</p>
            </div>
        `;
    }
    
    fetch('../BACKEND/faculty_modules_activities_api.php?action=get_activities')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayActivities(data.activities);
                if (recentActivityList) {
                    displayRecentActivities(data.activities.slice(0, 3));
                }
            } else {
                showNotification('Error loading activities: ' + data.error, 'error');
            }
        })
        .catch(error => {
            console.error('Error loading activities:', error);
            showNotification('Error loading activities', 'error');
        });
}

function displayActivities(activities) {
    const activitiesGrid = document.getElementById('activities-grid');
    if (!activitiesGrid) return;
    
    if (activities.length === 0) {
        activitiesGrid.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-tasks"></i>
                <h3>No Activities Found</h3>
                <p>Create your first activity to get started</p>
                <button class="btn btn-primary" onclick="openActivityUploadModal()">
                    <i class="fas fa-plus"></i>
                    Create Activity
                </button>
            </div>
        `;
        return;
    }
    
    activitiesGrid.innerHTML = '';
    activities.forEach(activity => {
        const activityCard = createActivityCard(activity);
        activitiesGrid.appendChild(activityCard);
    });
}

function displayRecentActivities(activities) {
    const recentActivityList = document.getElementById('recent-activity-list');
    if (!recentActivityList) return;
    
    if (activities.length === 0) {
        recentActivityList.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-tasks"></i>
                <p>No activities available</p>
            </div>
        `;
        return;
    }
    
    recentActivityList.innerHTML = '';
    activities.forEach(activity => {
        const activityItem = document.createElement('div');
        activityItem.className = 'activity-item';
        
        const deadlineDate = new Date(activity.deadline);
        const isOverdue = deadlineDate < new Date();
        const deadlineClass = isOverdue ? 'overdue' : 'upcoming';
        
        activityItem.innerHTML = `
            <div class="activity-info">
                <div class="activity-title">${activity.title}</div>
                <div class="activity-details">
                    ${activity.activity_type.charAt(0).toUpperCase() + activity.activity_type.slice(1)} | ${activity.points} points
                </div>
            </div>
            <div class="activity-deadline">
                <span class="deadline-badge ${deadlineClass}">
                    ${formatDate(activity.deadline)}
                </span>
            </div>
        `;
        recentActivityList.appendChild(activityItem);
    });
}

function createActivityCard(activity) {
    const card = document.createElement('div');
    card.className = 'activity-card';
    
    const deadlineDate = new Date(activity.deadline);
    const isOverdue = deadlineDate < new Date();
    const deadlineClass = isOverdue ? 'overdue' : 'upcoming';
    const statusClass = activity.is_active ? 'active' : 'inactive';
    
    card.innerHTML = `
        <div class="activity-card-content">
            <div class="activity-header">
                <div class="activity-icon">
                    <i class="fas fa-${getActivityIcon(activity.activity_type)}"></i>
                </div>
                <div class="activity-meta">
                    <div class="activity-type">${activity.activity_type.charAt(0).toUpperCase() + activity.activity_type.slice(1)}</div>
                    <div class="activity-points">${activity.points} points</div>
                </div>
                <div class="activity-status">
                    <span class="status-badge ${statusClass}">
                        ${activity.is_active ? 'Active' : 'Inactive'}
                    </span>
                </div>
            </div>
            <div class="activity-body">
                <h3 class="activity-title">${activity.title}</h3>
                <p class="activity-description">${activity.description}</p>
                <div class="activity-details">
                    <div class="activity-deadline">
                        <i class="fas fa-clock"></i>
                        <span class="deadline-badge ${deadlineClass}">
                            Deadline: ${formatDate(activity.deadline)}
                        </span>
                    </div>
                    ${activity.close_after_deadline ? '<div class="activity-auto-close"><i class="fas fa-lock"></i><span>Auto-close enabled</span></div>' : ''}
                </div>
            </div>
            <div class="activity-footer">
                <button class="btn btn-primary btn-sm" onclick="viewActivity(${activity.id})">
                    <i class="fas fa-eye"></i>
                    View
                </button>
                <button class="btn btn-secondary btn-sm" onclick="toggleActivityStatus(${activity.id})">
                    <i class="fas fa-${activity.is_active ? 'pause' : 'play'}"></i>
                    ${activity.is_active ? 'Deactivate' : 'Activate'}
                </button>
                <button class="btn btn-danger btn-sm" onclick="deleteActivity(${activity.id})">
                    <i class="fas fa-trash"></i>
                    Delete
                </button>
            </div>
        </div>
    `;
    
    return card;
}

// Modal Functions
function openModuleUploadModal() {
	openModal('module-upload-modal');
}

function openActivityUploadModal() {
	openModal('activity-upload-modal');
}

function openModal(modalId) {
	const modal = document.getElementById(modalId);
	if (!modal) return;
	modal.classList.remove('closing');
	modal.classList.add('show');
	modal.style.display = 'flex';
	document.body.classList.add('modal-open');
	// Focus first input/textarea/select if available
	const focusable = modal.querySelector('input, textarea, select, button');
	if (focusable) {
		setTimeout(() => focusable.focus(), 50);
	}
}

function closeModal(modalId) {
	const modal = document.getElementById(modalId);
	if (!modal) return;
	modal.classList.remove('show');
	modal.classList.add('closing');
	setTimeout(() => {
		modal.style.display = 'none';
		modal.classList.remove('closing');
		// Remove scroll lock if no modals are open
		if (document.querySelectorAll('.modal.show').length === 0) {
			document.body.classList.remove('modal-open');
		}
	}, 200);
	currentStudentId = null;
}

// Module Actions
function uploadModule() {
    const form = document.getElementById('module-upload-form');
    const formData = new FormData(form);
    formData.append('action', 'upload_module');
    
    fetch('../BACKEND/faculty_modules_activities_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Module uploaded successfully!', 'success');
            closeModal('module-upload-modal');
            form.reset();
            loadModules();
        } else {
            showNotification('Error: ' + data.error, 'error');
        }
    })
    .catch(error => {
        showNotification('Error: ' + error, 'error');
    });
}

function createActivity() {
    const form = document.getElementById('activity-upload-form');
    const formData = new FormData(form);
    formData.append('action', 'create_activity');
    
    fetch('../BACKEND/faculty_modules_activities_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Activity created successfully!', 'success');
            closeModal('activity-upload-modal');
            form.reset();
            loadActivities();
        } else {
            showNotification('Error: ' + data.error, 'error');
        }
    })
    .catch(error => {
        showNotification('Error: ' + error, 'error');
    });
}

function deleteModule(moduleId) {
    if (!confirm('Are you sure you want to delete this module?')) return;
    
    const formData = new FormData();
    formData.append('action', 'delete_module');
    formData.append('module_id', moduleId);
    
    fetch('../BACKEND/faculty_modules_activities_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Module deleted successfully!', 'success');
            loadModules();
        } else {
            showNotification('Error: ' + data.error, 'error');
        }
    })
    .catch(error => {
        showNotification('Error: ' + error, 'error');
    });
}

function deleteActivity(activityId) {
    if (!confirm('Are you sure you want to delete this activity?')) return;
    
    const formData = new FormData();
    formData.append('action', 'delete_activity');
    formData.append('activity_id', activityId);
    
    fetch('../BACKEND/faculty_modules_activities_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Activity deleted successfully!', 'success');
            loadActivities();
        } else {
            showNotification('Error: ' + data.error, 'error');
        }
    })
    .catch(error => {
        showNotification('Error: ' + error, 'error');
    });
}

function toggleModuleStatus(moduleId) {
    const formData = new FormData();
    formData.append('action', 'toggle_module_status');
    formData.append('module_id', moduleId);
    
    fetch('../BACKEND/faculty_modules_activities_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Module status updated successfully!', 'success');
            loadModules();
        } else {
            showNotification('Error: ' + data.error, 'error');
        }
    })
    .catch(error => {
        showNotification('Error: ' + error, 'error');
    });
}

function toggleActivityStatus(activityId) {
    const formData = new FormData();
    formData.append('action', 'toggle_activity_status');
    formData.append('activity_id', activityId);
    
    fetch('../BACKEND/faculty_modules_activities_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Activity status updated successfully!', 'success');
            loadActivities();
        } else {
            showNotification('Error: ' + data.error, 'error');
        }
    })
    .catch(error => {
        showNotification('Error: ' + error, 'error');
    });
}

function viewModule(moduleId) {
    // Implement module viewing functionality
    showNotification('Module viewing functionality coming soon!', 'info');
}

function viewActivity(activityId) {
    // Implement activity viewing functionality
    showNotification('Activity viewing functionality coming soon!', 'info');
}

function refreshModules() {
    loadModules();
}

function refreshActivities() {
    loadActivities();
}

// Utility Functions
function getFileIcon(fileType) {
    const icons = {
        'pdf': 'file-pdf',
        'doc': 'file-word',
        'docx': 'file-word',
        'jpg': 'file-image',
        'jpeg': 'file-image',
        'png': 'file-image'
    };
    return icons[fileType] || 'file';
}

function getActivityIcon(activityType) {
    const icons = {
        'assignment': 'file-alt',
        'quiz': 'question-circle',
        'project': 'project-diagram',
        'presentation': 'chalkboard'
    };
    return icons[activityType] || 'tasks';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Analytics Functions
function loadAnalytics() {
    // Load course filters
    loadCourseFilters();
    
    // Load analytics data
    fetchAnalyticsData();
    
    // Load incomplete activities
    loadIncompleteActivities();
}

function loadCourseFilters() {
    fetch('../BACKEND/faculty_analytics_api.php?action=get_courses')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const courseFilter = document.getElementById('course-filter');
                const leaderboardCourse = document.getElementById('leaderboard-course');
                
                if (courseFilter) {
                    courseFilter.innerHTML = '<option value="">All Courses</option>';
                    data.courses.forEach(course => {
                        courseFilter.innerHTML += `<option value="${course}">${course}</option>`;
                    });
                }
                
                if (leaderboardCourse) {
                    leaderboardCourse.innerHTML = '<option value="">All Courses</option>';
                    data.courses.forEach(course => {
                        leaderboardCourse.innerHTML += `<option value="${course}">${course}</option>`;
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error loading course filters:', error);
        });
}

function fetchAnalyticsData() {
    const course = document.getElementById('course-filter')?.value || '';
    const year = document.getElementById('year-filter')?.value || '';
    const period = document.getElementById('period-filter')?.value || 'all';
    
    const params = new URLSearchParams({
        action: 'get_analytics',
        course: course,
        year: year,
        period: period
    });
    
    fetch(`../BACKEND/faculty_analytics_api.php?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateAnalyticsUI(data.data);
                updateCharts(data.data);
            } else {
                showNotification('Error loading analytics: ' + data.error, 'error');
            }
        })
        .catch(error => {
            console.error('Error loading analytics:', error);
            showNotification('Error loading analytics', 'error');
        });
}

function updateAnalyticsUI(data) {
    // Update performance metrics
    document.getElementById('avg-grade').textContent = Math.round(data.performance.avg_grade || 0);
    document.getElementById('completion-rate').textContent = 
        Math.round(((data.performance.active_students || 0) / (data.performance.total_students || 1)) * 100) + '%';
    
    // Update module engagement
    document.getElementById('module-views').textContent = data.module_engagement.total_views || 0;
    document.getElementById('quiz-attempts').textContent = data.module_engagement.quiz_attempts || 0;
    
    // Update activity engagement
    document.getElementById('activity-submissions').textContent = data.activity_engagement.total_submissions || 0;
    document.getElementById('late-submissions').textContent = data.activity_engagement.late_submissions || 0;
    
    // Update student distribution
    document.getElementById('total-students').textContent = data.performance.total_students || 0;
    document.getElementById('active-students').textContent = data.performance.active_students || 0;
}

function updateCharts(data) {
    // Update performance trends chart
    updatePerformanceChart(data.performance_trends);
    
    // Update course engagement chart
    updateCourseEngagementChart(data.course_engagement);
}

function updatePerformanceChart(trends) {
    const ctx = document.getElementById('performance-chart');
    if (!ctx) return;
    
    const labels = trends.map(t => new Date(t.date).toLocaleDateString());
    const grades = trends.map(t => parseFloat(t.avg_grade) || 0);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Average Grade',
                data: grades,
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
}

function updateCourseEngagementChart(engagement) {
    const ctx = document.getElementById('course-engagement-chart');
    if (!ctx) return;
    
    const labels = engagement.map(e => e.course);
    const students = engagement.map(e => e.total_students);
    const views = engagement.map(e => e.module_views);
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Students',
                data: students,
                backgroundColor: 'rgba(102, 126, 234, 0.8)'
            }, {
                label: 'Module Views',
                data: views,
                backgroundColor: 'rgba(118, 75, 162, 0.8)'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function loadIncompleteActivities() {
    fetch('../BACKEND/faculty_analytics_api.php?action=get_incomplete_activities')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayIncompleteActivities(data.incomplete);
            } else {
                showNotification('Error loading incomplete activities: ' + data.error, 'error');
            }
        })
        .catch(error => {
            console.error('Error loading incomplete activities:', error);
            showNotification('Error loading incomplete activities', 'error');
        });
}

function displayIncompleteActivities(incomplete) {
    const container = document.getElementById('incomplete-activities-list');
    if (!container) return;
    
    if (incomplete.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-check-circle"></i>
                <p>All students have completed their activities!</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = '';
    incomplete.forEach(item => {
        const activityItem = document.createElement('div');
        activityItem.className = 'incomplete-item';
        
        const deadlineDate = new Date(item.deadline);
        const isOverdue = deadlineDate < new Date();
        const statusClass = isOverdue ? 'overdue' : 'pending';
        
        activityItem.innerHTML = `
            <div class="student-info">
                <div class="student-name">${item.full_name}</div>
                <div class="student-details">${item.student_id} | ${item.course} | ${item.year_level}</div>
            </div>
            <div class="activity-info">
                <div class="activity-title">${item.activity_title}</div>
                <div class="activity-deadline">
                    <span class="deadline-badge ${statusClass}">
                        ${formatDate(item.deadline)}
                    </span>
                </div>
            </div>
        `;
        container.appendChild(activityItem);
    });
}

function filterAnalytics() {
    fetchAnalyticsData();
}

// Leaderboard Functions
function loadLeaderboard() {
    const rankingType = document.getElementById('ranking-type')?.value || 'overall';
    const course = document.getElementById('leaderboard-course')?.value || '';
    const year = document.getElementById('leaderboard-year')?.value || '';
    
    const params = new URLSearchParams({
        action: 'get_leaderboard',
        ranking_type: rankingType,
        course: course,
        year: year
    });
    
    fetch(`../BACKEND/faculty_analytics_api.php?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayLeaderboard(data.leaderboard);
            } else {
                showNotification('Error loading leaderboard: ' + data.error, 'error');
            }
        })
        .catch(error => {
            console.error('Error loading leaderboard:', error);
            showNotification('Error loading leaderboard', 'error');
        });
}

function displayLeaderboard(leaderboard) {
    // Update podium
    updatePodium(leaderboard.slice(0, 3));
    
    // Update full leaderboard table
    updateLeaderboardTable(leaderboard);
}

function updatePodium(topThree) {
    const firstPlace = document.getElementById('first-place');
    const secondPlace = document.getElementById('second-place');
    const thirdPlace = document.getElementById('third-place');
    
    if (firstPlace && topThree[0]) {
        firstPlace.innerHTML = `
            <div class="student-name">${topThree[0].full_name}</div>
            <div class="student-score">${Math.round(topThree[0].overall_score)} points</div>
        `;
    }
    
    if (secondPlace && topThree[1]) {
        secondPlace.innerHTML = `
            <div class="student-name">${topThree[1].full_name}</div>
            <div class="student-score">${Math.round(topThree[1].overall_score)} points</div>
        `;
    }
    
    if (thirdPlace && topThree[2]) {
        thirdPlace.innerHTML = `
            <div class="student-name">${topThree[2].full_name}</div>
            <div class="student-score">${Math.round(topThree[2].overall_score)} points</div>
        `;
    }
}

function updateLeaderboardTable(leaderboard) {
    const tbody = document.getElementById('leaderboard-tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    leaderboard.forEach((student, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${student.rank}</td>
            <td>
                <div class="student-info">
                    <div class="student-name">${student.full_name}</div>
                    <div class="student-id">${student.student_id}</div>
                </div>
            </td>
            <td>${student.course}</td>
            <td>${student.year_level}</td>
            <td>
                <span class="score-badge">${Math.round(student.overall_score)}</span>
            </td>
            <td>${student.modules_completed}</td>
            <td>${student.activities_completed}</td>
            <td>${Math.round(student.quiz_score || 0)}%</td>
        `;
        tbody.appendChild(row);
    });
}
