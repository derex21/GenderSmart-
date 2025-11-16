<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Include backend initialization
include '../BACKEND/admin_dashboard_backend.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Admin Dashboard - Gender Smart</title>
  <link rel="stylesheet" href="css/admind.css">
    <link rel="stylesheet" href="css/animations.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
  <div class="admin-dashboard">
        <!-- Sidebar -->
    <div class="admin-sidebar">
            <div class="sidebar-header">
        <img src="../image/logo.png" alt="Gender Smart Logo" class="sidebar-logo">
        <h3>Admin Panel</h3>
            </div>
            
      <nav class="sidebar-nav">
        <ul>
          <li class="nav-item active" data-section="dashboard" onclick="showSection('dashboard')">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                </li>
          <li class="nav-item" data-section="faculty" onclick="showSection('faculty')">
            <i class="fas fa-chalkboard-teacher"></i>
            <span>Faculty Management</span>
                </li>
          <li class="nav-item" data-section="admins" onclick="showSection('admins')">
            <i class="fas fa-users-cog"></i>
            <span>Admin Accounts</span>
                </li>
          <li class="nav-item" data-section="webinars" onclick="showSection('webinars')">
            <i class="fas fa-video"></i>
            <span>Webinar Management</span>
                </li>
          <li class="nav-item" data-section="profile" onclick="showSection('profile')">
            <i class="fas fa-user-cog"></i>
            <span>My Profile</span>
                </li>
            </ul>
      </nav>
            
            <div class="sidebar-footer">
        <div class="admin-info">
          <div class="admin-avatar">
            <i class="fas fa-user-shield"></i>
          </div>
        <div class="admin-details">
          <div class="admin-name" id="admin-name"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></div>
          <div class="admin-role" id="admin-role"><?php echo htmlspecialchars(ucfirst($_SESSION['admin_role'] ?? 'admin')); ?></div>
        </div>
        </div>
                <button onclick="showLogoutModal()" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
          Logout
                </button>
            </div>
        </div>

        <!-- Main Content -->
    <div class="admin-main">
      <!-- Header -->
      <header class="admin-header">
        <div class="header-left">
          <button class="sidebar-toggle">
            <i class="fas fa-bars"></i>
          </button>
          <h1 id="page-title">Dashboard</h1>
                    </div>
        <div class="header-right">
          <div class="admin-welcome" id="admin-welcome">
            Welcome back, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>!
      </header>

            <!-- Dashboard Content -->
      <div class="admin-content">
        <!-- Dashboard Section -->
            <div class="content-section active" id="dashboard">
                <div class="stats-grid">
                    <div class="stat-card clickable" onclick="showSection('faculty')">
              <div class="stat-icon">
                <i class="fas fa-chalkboard-teacher"></i>
              </div>
              <div class="stat-content">
                <div class="stat-number" id="faculty-count">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
                <div class="stat-label">Active Faculty</div>
                        </div>
                    </div>
                    
                    <div class="stat-card clickable" onclick="showSection('admins')">
              <div class="stat-icon">
                <i class="fas fa-users-cog"></i>
              </div>
              <div class="stat-content">
                <div class="stat-number" id="admin-count">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
                <div class="stat-label">Admin Accounts</div>
                        </div>
                    </div>
                    
                    <div class="stat-card clickable" onclick="showSection('webinars')">
              <div class="stat-icon">
                <i class="fas fa-video"></i>
              </div>
              <div class="stat-content">
                <div class="stat-number" id="webinar-count">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
                <div class="stat-label">Active Webinars</div>
              </div>
                        </div>
                    </div>
                    
          <div class="dashboard-grid">
            <div class="dashboard-card">
              <div class="card-header">
                <h3>Recent Faculty</h3>
                <a href="#" class="view-all" onclick="showSection('faculty')">View All</a>
                        </div>
              <div class="card-content">
                <div class="faculty-list" id="recent-faculty-list">
                  <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading faculty data...</p>
                    </div>
                </div>
                        </div>
                    </div>
                    
            <div class="dashboard-card">
              <div class="card-header">
                <h3>Recent Webinars</h3>
                <a href="#" class="view-all" onclick="showSection('webinars')">View All</a>
                                </div>
              <div class="card-content">
                <div class="webinar-list" id="recent-webinar-list">
                  <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading webinar data...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Faculty Management Section -->
        <div class="content-section" id="faculty">
                <div class="section-header">
            <h2>Faculty Management</h2>
            <button class="btn btn-primary" onclick="openFacultyModal()">
                        <i class="fas fa-plus"></i>
              Add Faculty
                    </button>
                </div>
          
          <div class="faculty-table-container">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Faculty ID</th>
                  <th>Full Name</th>
                  <th>Email</th>
                  <th>Course</th>
                  <th>Year Level</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="faculty-table-body">
                <!-- Faculty data will be loaded here -->
              </tbody>
            </table>
          </div>
            </div>

        <!-- Admin Accounts Section -->
        <div class="content-section" id="admins">
                <div class="section-header">
            <h2>Admin Accounts</h2>
            <button class="btn btn-primary" onclick="openAdminModal()">
                        <i class="fas fa-plus"></i>
              Add Admin
                    </button>
                </div>
          
          <div class="admin-table-container">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Username</th>
                  <th>Full Name</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Last Login</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="admin-table-body">
                <!-- Admin data will be loaded here -->
              </tbody>
            </table>
          </div>
            </div>

        <!-- Webinar Management Section -->
        <div class="content-section" id="webinars">
                <div class="section-header">
            <h2>Webinar Management</h2>
            <button class="btn btn-primary" onclick="openWebinarModal()">
              <i class="fas fa-plus"></i>
              Add Webinar
            </button>
          </div>
          
          <div class="webinar-table-container">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Title</th>
                  <th>Speaker</th>
                  <th>Date</th>
                  <th>Category</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="webinar-table-body">
                <!-- Webinar data will be loaded here -->
              </tbody>
            </table>
                </div>
            </div>

        <!-- Profile Section -->
        <div class="content-section" id="profile">
                <div class="section-header">
            <h2>My Profile</h2>
          </div>
          
          <div class="profile-container">
            <div class="profile-card">
              <div class="profile-header">
                <div class="profile-avatar">
                  <i class="fas fa-user-shield"></i>
                </div>
                <div class="profile-info">
                  <h3 id="profile-name"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></h3>
                  <p id="profile-role"><?php echo htmlspecialchars(ucfirst($_SESSION['admin_role'] ?? 'admin') . ' Administrator'); ?></p>
        </div>
    </div>

              <form class="profile-form" id="profile-form">
                <div class="form-group">
                  <label>Full Name</label>
                  <input type="text" name="full_name" id="profile-full-name" value="<?php echo htmlspecialchars($_SESSION['admin_name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                  <label>Email</label>
                  <input type="email" name="email" id="profile-email" value="<?php echo htmlspecialchars($_SESSION['admin_email'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                  <label>Current Password</label>
                  <input type="password" name="current_password" placeholder="Enter current password">
                </div>
                
                <div class="form-group">
                  <label>New Password</label>
                  <input type="password" name="new_password" placeholder="Enter new password">
                </div>
                
                <div class="form-group">
                  <label>Confirm New Password</label>
                  <input type="password" name="confirm_password" placeholder="Confirm new password">
                </div>
                
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save"></i>
                  Update Profile
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Faculty Modal -->
  <div class="modal" id="faculty-modal">
    <div class="modal-content medium">
      <div class="modal-header">
        <h3>Add Faculty Account</h3>
        <button class="modal-close" onclick="closeModal('faculty-modal')">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <form id="faculty-form">
        <div class="form-grid">
        <div class="form-group">
          <label>Faculty ID</label>
          <input type="text" name="faculty_id" placeholder="Enter faculty ID" required>
        </div>
        
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="full_name" placeholder="Enter full name" required>
        </div>
        
        <div class="form-group full-width">
          <label>Email</label>
          <input type="email" name="email" placeholder="Enter email address" required>
        </div>
        
        <div class="form-group full-width">
          <label>Password</label>
          <input type="password" name="password" placeholder="Enter password" required>
        </div>
        
        <div class="form-group">
          <label>Course <span style="color: red;">*</span></label>
          <select name="course" id="course" required>
            <option value="">Select course</option>
            <option value="Bachelor of Science in Fisheries and Aquatic Sciences (BSFAS)">Bachelor of Science in Fisheries and Aquatic Sciences (BSFAS)</option>
            <option value="Bachelor of Secondary Education (BSEd) - Major in English">Bachelor of Secondary Education (BSEd) - Major in English</option>
            <option value="Bachelor of Secondary Education (BSEd) - Major in Biological Science">Bachelor of Secondary Education (BSEd) - Major in Biological Science</option>
            <option value="Bachelor of Elementary Education (BEEd)">Bachelor of Elementary Education (BEEd)</option>
            <option value="Bachelor of Science in Hospitality Management (BSHM)">Bachelor of Science in Hospitality Management (BSHM)</option>
            <option value="Bachelor of Science in Business Administration (BSBA)">Bachelor of Science in Business Administration (BSBA)</option>
            <option value="Bachelor of Science in Computer Science (BSCS)">Bachelor of Science in Computer Science (BSCS)</option>
            <option value="Bachelor of Science in Information Technology (BSIT)">Bachelor of Science in Information Technology (BSIT)</option>
          </select>
        </div>
        
        <div class="form-group">
          <label>Year Level <span style="color: red;">*</span></label>
          <select name="year_level" id="year_level" required>
            <option value="">Select year level</option>
            <option value="1st Year">1st Year</option>
            <option value="2nd Year">2nd Year</option>
            <option value="3rd Year">3rd Year</option>
            <option value="4th Year">4th Year</option>
            <option value="5th Year">5th Year</option>
          </select>
        </div>
        </div>
        
        <div class="modal-actions">
          <button type="button" class="btn btn-secondary" onclick="closeModal('faculty-modal')">Cancel</button>
          <button type="submit" class="btn btn-primary">Add Faculty</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Faculty Modal -->
  <div class="modal" id="edit-faculty-modal">
    <div class="modal-content medium">
      <div class="modal-header">
        <h3>Edit Faculty Account</h3>
        <button class="modal-close" onclick="closeModal('edit-faculty-modal')">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <form id="edit-faculty-form">
        <input type="hidden" name="id" id="edit-faculty-id">
        
        <div class="form-grid">
        <div class="form-group">
          <label>Faculty ID</label>
          <input type="text" name="faculty_id" id="edit-faculty-id-input" placeholder="Enter faculty ID" required>
        </div>
        
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="full_name" id="edit-faculty-name" placeholder="Enter full name" required>
        </div>
        
        <div class="form-group full-width">
          <label>Email</label>
          <input type="email" name="email" id="edit-faculty-email" placeholder="Enter email address" required>
        </div>
        
        <div class="form-group">
          <label>Course <span style="color: red;">*</span></label>
          <select name="course" id="edit-faculty-course" required>
            <option value="">Select course</option>
            <option value="Bachelor of Science in Fisheries and Aquatic Sciences (BSFAS)">Bachelor of Science in Fisheries and Aquatic Sciences (BSFAS)</option>
            <option value="Bachelor of Secondary Education (BSEd) - Major in English">Bachelor of Secondary Education (BSEd) - Major in English</option>
            <option value="Bachelor of Secondary Education (BSEd) - Major in Biological Science">Bachelor of Secondary Education (BSEd) - Major in Biological Science</option>
            <option value="Bachelor of Elementary Education (BEEd)">Bachelor of Elementary Education (BEEd)</option>
            <option value="Bachelor of Science in Hospitality Management (BSHM)">Bachelor of Science in Hospitality Management (BSHM)</option>
            <option value="Bachelor of Science in Business Administration (BSBA)">Bachelor of Science in Business Administration (BSBA)</option>
            <option value="Bachelor of Science in Computer Science (BSCS)">Bachelor of Science in Computer Science (BSCS)</option>
            <option value="Bachelor of Science in Information Technology (BSIT)">Bachelor of Science in Information Technology (BSIT)</option>
          </select>
        </div>
        
        <div class="form-group">
          <label>Year Level <span style="color: red;">*</span></label>
          <select name="year_level" id="edit-faculty-year" required>
            <option value="">Select year level</option>
            <option value="1st Year">1st Year</option>
            <option value="2nd Year">2nd Year</option>
            <option value="3rd Year">3rd Year</option>
            <option value="4th Year">4th Year</option>
            <option value="5th Year">5th Year</option>
          </select>
        </div>
        
        <div class="form-group">
          <label>Status</label>
          <select name="is_active" id="edit-faculty-status">
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
        </div>
        </div>
        
        <div class="modal-actions">
          <button type="button" class="btn btn-secondary" onclick="closeModal('edit-faculty-modal')">Cancel</button>
          <button type="submit" class="btn btn-primary">Update Faculty</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Faculty Confirmation Modal -->
  <div class="modal" id="delete-faculty-modal">
    <div class="modal-content small">
      <div class="modal-header">
        <h3>Delete Faculty Account</h3>
        <button class="modal-close" onclick="closeModal('delete-faculty-modal')">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div style="padding: 20px; text-align: center;">
        <div style="font-size: 48px; color: #e74c3c; margin-bottom: 20px;">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h4 style="margin-bottom: 10px;">Are you sure you want to delete this faculty account?</h4>
        <p style="color: #666; margin-bottom: 20px;">This action cannot be undone.</p>
        <div class="modal-actions">
          <button type="button" class="btn btn-secondary" onclick="closeModal('delete-faculty-modal')">Cancel</button>
          <button type="button" class="btn btn-danger" onclick="confirmDeleteFaculty()">Delete Faculty</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Admin Modal -->
  <div class="modal" id="admin-modal">
    <div class="modal-content medium">
      <div class="modal-header">
        <h3>Add Admin Account</h3>
        <button class="modal-close" onclick="closeModal('admin-modal')">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <form id="admin-form">
        <div class="form-grid">
        <div class="form-group">
          <label>Username</label>
          <input type="text" name="username" placeholder="Enter username" required>
        </div>
        
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="full_name" placeholder="Enter full name" required>
        </div>
        
        <div class="form-group full-width">
          <label>Email</label>
          <input type="email" name="email" placeholder="Enter email address" required>
        </div>
        
        <div class="form-group full-width">
          <label>Password</label>
          <input type="password" name="password" placeholder="Enter password" required>
        </div>
        
        <div class="form-group full-width">
          <label>Role</label>
          <select name="role" required>
            <option value="">Select role</option>
            <option value="admin">Admin</option>
            <option value="moderator">Moderator</option>
            <?php if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'super_admin'): ?>
            <option value="super_admin">Super Admin</option>
            <?php endif; ?>
          </select>
        </div>
        </div>
        
        <div class="modal-actions">
          <button type="button" class="btn btn-secondary" onclick="closeModal('admin-modal')">Cancel</button>
          <button type="submit" class="btn btn-primary">Add Admin</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Admin Modal -->
  <div class="modal" id="edit-admin-modal">
    <div class="modal-content medium">
      <div class="modal-header">
        <h3>Edit Admin Account</h3>
        <button class="modal-close" onclick="closeModal('edit-admin-modal')">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <form id="edit-admin-form">
        <input type="hidden" name="id" id="edit-admin-id">
        
        <div class="form-grid">
        <div class="form-group">
          <label>Username</label>
          <input type="text" name="username" id="edit-admin-username" placeholder="Enter username" required>
        </div>
        
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="full_name" id="edit-admin-name" placeholder="Enter full name" required>
        </div>
        
        <div class="form-group full-width">
          <label>Email</label>
          <input type="email" name="email" id="edit-admin-email" placeholder="Enter email address" required>
        </div>
        
        <div class="form-group full-width">
          <label>Role</label>
          <select name="role" id="edit-admin-role" required>
            <option value="">Select role</option>
            <option value="admin">Admin</option>
            <option value="moderator">Moderator</option>
            <?php if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'super_admin'): ?>
            <option value="super_admin">Super Admin</option>
            <?php endif; ?>
          </select>
        </div>
        
        <div class="form-group">
          <label>Status</label>
          <select name="is_active" id="edit-admin-status">
            <option value="1">Active</option>
            <option value="0">Inactive</option>
          </select>
        </div>
        </div>
        
        <div class="modal-actions">
          <button type="button" class="btn btn-secondary" onclick="closeModal('edit-admin-modal')">Cancel</button>
          <button type="submit" class="btn btn-primary">Update Admin</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Admin Confirmation Modal -->
  <div class="modal" id="delete-admin-modal">
    <div class="modal-content small">
      <div class="modal-header delete-modal-header">
        <h3>Delete Admin Account</h3>
        <button class="modal-close" onclick="closeModal('delete-admin-modal')">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="delete-confirmation-content">
        <div class="delete-icon">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h4>Are you sure you want to delete this admin account?</h4>
        <p class="delete-warning">This action cannot be undone. All data associated with this admin account will be permanently removed.</p>
        <div class="admin-info-preview" id="admin-to-delete-info">
          <!-- Admin info will be displayed here -->
        </div>
        <div class="modal-actions">
          <button type="button" class="btn btn-secondary" onclick="closeModal('delete-admin-modal')">Cancel</button>
          <button type="button" class="btn btn-danger" onclick="confirmDeleteAdmin()">Delete Admin</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Webinar Modal -->
  <div class="modal" id="webinar-modal">
    <div class="modal-content large">
      <div class="modal-header">
        <h3>Add Webinar</h3>
        <button class="modal-close" onclick="closeModal('webinar-modal')">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <form id="webinar-form">
        <div class="form-grid">
        <div class="form-group full-width">
          <label>Webinar Title</label>
          <input type="text" name="title" placeholder="Enter webinar title" required>
        </div>
        
        <div class="form-group full-width">
          <label>Description</label>
          <textarea name="description" placeholder="Enter webinar description" rows="3"></textarea>
        </div>
        
        <div class="form-group">
          <label>Speaker Name</label>
          <input type="text" name="speaker_name" placeholder="Enter speaker name" required>
        </div>
        
        <div class="form-group">
          <label>Speaker Title</label>
          <input type="text" name="speaker_title" placeholder="Enter speaker title">
        </div>
        
        <div class="form-group">
          <label>Webinar Date & Time</label>
          <input type="datetime-local" name="webinar_date" required>
        </div>
        
        <div class="form-group">
          <label>Duration (minutes)</label>
          <input type="number" name="duration" placeholder="Enter duration in minutes" required>
        </div>
        
        <div class="form-group">
          <label>Category</label>
          <select name="category" required>
            <option value="">Select category</option>
            <option value="upcoming">Upcoming</option>
            <option value="recorded">Recorded</option>
            <option value="popular">Popular</option>
          </select>
        </div>
        
        <div class="form-group full-width">
          <label>Webinar Link</label>
          <input type="url" name="webinar_link" placeholder="Enter webinar link (Zoom, Teams, etc.)">
        </div>
        
        <div class="form-group full-width">
          <label>Google Form Link</label>
          <input type="url" name="google_form_link" placeholder="Enter Google Form link">
        </div>
        </div>
        
        <div class="modal-actions">
          <button type="button" class="btn btn-secondary" onclick="closeModal('webinar-modal')">Cancel</button>
          <button type="submit" class="btn btn-primary">Add Webinar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Logout Confirmation Modal -->
  <div class="modal" id="logout-modal" style="display: none;">
    <div class="modal-content small">
      <div class="modal-header">
        <h3>Confirm Logout</h3>
        <button class="modal-close" onclick="closeModal('logout-modal')">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div style="padding: 20px; text-align: center;">
        <div style="font-size: 48px; color: #e74c3c; margin-bottom: 20px;">
          <i class="fas fa-sign-out-alt"></i>
        </div>
        <h4 style="margin-bottom: 10px;">Are you sure you want to logout?</h4>
        <p style="color: #666; margin-bottom: 20px;">You will be redirected to the login page.</p>
        <div class="modal-actions">
          <button type="button" class="btn btn-secondary" onclick="closeModal('logout-modal')">Cancel</button>
          <button type="button" class="btn btn-danger" onclick="confirmLogout()">Logout</button>
        </div>
      </div>
    </div>
  </div>

  <script src="js/admin-dashboard.js"></script>
  <script>
    // Pass initial statistics from PHP to JavaScript for faster initial load
    const initialStats = {
      faculty_count: <?php echo $faculty_count; ?>,
      admin_count: <?php echo $admin_count; ?>,
      webinar_count: <?php echo $webinar_count; ?>
    };
    
    // Update counts immediately after the script loads
    document.addEventListener('DOMContentLoaded', function() {
      // Small delay to ensure admin-dashboard.js has loaded
      setTimeout(function() {
        if (typeof updateDashboardStats === 'function') {
          updateDashboardStats(initialStats);
          console.log('Initial stats loaded:', initialStats);
        } else {
          console.error('updateDashboardStats function not found');
        }
      }, 100);
    });
  </script>
</body>
</html>