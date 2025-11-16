<?php
session_start();

// Check if faculty is logged in
if (!isset($_SESSION['faculty_id'])) {
    header("Location: faculty_login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard - Gender Smart E-Learning</title>
    <link rel="stylesheet" href="css/faculty_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="faculty-dashboard">
        <!-- Sidebar -->
        <div class="faculty-sidebar">
            <div class="sidebar-header">
                <img src="../image/logo.png" alt="Gender Smart Logo" class="sidebar-logo">
                <h3>Faculty Portal</h3>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li class="nav-item active" data-section="dashboard">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </li>
                    <li class="nav-item" data-section="students">
                        <i class="fas fa-users"></i>
                        <span>Student Management</span>
                    </li>
                    <li class="nav-item" data-section="modules">
                        <i class="fas fa-book"></i>
                        <span>Modules</span>
                    </li>
                    <li class="nav-item" data-section="activities">
                        <i class="fas fa-tasks"></i>
                        <span>Activities</span>
                    </li>
                    <li class="nav-item" data-section="analytics">
                        <i class="fas fa-chart-bar"></i>
                        <span>Analytics</span>
                    </li>
                    <li class="nav-item" data-section="leaderboard">
                        <i class="fas fa-trophy"></i>
                        <span>Leaderboard</span>
                    </li>
                    <li class="nav-item" data-section="profile">
                        <i class="fas fa-user"></i>
                        <span>My Profile</span>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-details">
                        <div class="user-name" id="faculty-name">Loading...</div>
                        <div class="user-role">Faculty</div>
                    </div>
                </div>
                <a href="../BACKEND/faculty_logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="faculty-main">
            <!-- Header -->
            <header class="faculty-header">
                <div class="header-left">
                    <button class="sidebar-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 id="page-title">Dashboard</h1>
                </div>
                <div class="header-right">
                    <div class="faculty-welcome" id="faculty-welcome">
                        Welcome back, Loading...!
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="faculty-content">
                <!-- Dashboard Section -->
                <div class="content-section active" id="dashboard">
                    <div class="welcome-card">
                        <div class="welcome-content">
                            <h2>Welcome to Your Faculty Portal</h2>
                            <p>Access your course materials, webinars, and manage your academic activities.</p>
                        </div>
                        <div class="welcome-stats">
                            <div class="stat-item">
                                <div class="stat-icon">
                                    <i class="fas fa-book"></i>
                                </div>
                                <div class="stat-info">
                                    <div class="stat-label">Course</div>
                                    <div class="stat-value" id="faculty-course">Loading...</div>
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-icon">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <div class="stat-info">
                                    <div class="stat-label">Year Level</div>
                                    <div class="stat-value" id="faculty-year-level">Loading...</div>
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-icon">
                                    <i class="fas fa-id-card"></i>
                                </div>
                                <div class="stat-info">
                                    <div class="stat-label">Faculty ID</div>
                                    <div class="stat-value" id="faculty-id">Loading...</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="dashboard-grid">
                        <div class="dashboard-card">
                            <div class="card-header">
                                <h3>Recent Modules</h3>
                                <a href="#" class="view-all" onclick="showSection('modules')">View All</a>
                            </div>
                            <div class="card-content">
                                <div class="module-list" id="recent-module-list">
                                    <div class="loading-spinner">
                                        <i class="fas fa-spinner fa-spin"></i>
                                        <p>Loading modules...</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="dashboard-card">
                            <div class="card-header">
                                <h3>Recent Activities</h3>
                                <a href="#" class="view-all" onclick="showSection('activities')">View All</a>
                            </div>
                            <div class="card-content">
                                <div class="activity-list" id="recent-activity-list">
                                    <div class="loading-spinner">
                                        <i class="fas fa-spinner fa-spin"></i>
                                        <p>Loading activities...</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="dashboard-card">
                            <div class="card-header">
                                <h3>Quick Actions</h3>
                            </div>
                            <div class="card-content">
                                <div class="quick-actions">
                                    <button class="action-btn" onclick="showSection('modules')">
                                        <i class="fas fa-book"></i>
                                        <span>Manage Modules</span>
                                    </button>
                                    <button class="action-btn" onclick="showSection('activities')">
                                        <i class="fas fa-tasks"></i>
                                        <span>Manage Activities</span>
                                    </button>
                                    <button class="action-btn" onclick="showSection('profile')">
                                        <i class="fas fa-user"></i>
                                        <span>My Profile</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Student Management Section -->
                <div class="content-section" id="students">
                    <div class="section-header">
                        <h2>Student Management</h2>
                        <div class="btn-group">
                            <button class="btn btn-primary" onclick="showStudentTab('pending')">
                                <i class="fas fa-clock"></i>
                                Pending Students
                            </button>
                            <button class="btn btn-success" onclick="showStudentTab('accepted')">
                                <i class="fas fa-check"></i>
                                Accepted Students
                            </button>
                            <button class="btn btn-warning" onclick="showStudentTab('rejected')">
                                <i class="fas fa-trash"></i>
                                Rejected Students
                            </button>
                        </div>
                    </div>
                    
                    <!-- Student Stats -->
                    <div class="stats-grid" id="student-stats">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" id="pending-count">0</div>
                                <div class="stat-label">Pending</div>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" id="accepted-count">0</div>
                                <div class="stat-label">Accepted</div>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-times"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number" id="rejected-count">0</div>
                                <div class="stat-label">Rejected</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Student Lists -->
                    <div class="student-lists">
                        <!-- Pending Students -->
                        <div class="student-list" id="pending-students">
                            <h3>Pending Students</h3>
                            <div class="student-table-container">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Student ID</th>
                                            <th>Name</th>
                                            <th>Gender</th>
                                            <th>Course</th>
                                            <th>Year Level</th>
                                            <th>Registration Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pending-students-body">
                                        <!-- Pending students will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Accepted Students -->
                        <div class="student-list" id="accepted-students" style="display: none;">
                            <h3>Accepted Students</h3>
                            <div class="student-table-container">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Student ID</th>
                                            <th>Name</th>
                                            <th>Gender</th>
                                            <th>Course</th>
                                            <th>Year Level</th>
                                            <th>Accepted Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="accepted-students-body">
                                        <!-- Accepted students will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Rejected Students -->
                        <div class="student-list" id="rejected-students" style="display: none;">
                            <h3>Rejected Students</h3>
                            <div class="student-table-container">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Student ID</th>
                                            <th>Name</th>
                                            <th>Gender</th>
                                            <th>Course</th>
                                            <th>Year Level</th>
                                            <th>Rejection Reason</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="rejected-students-body">
                                        <!-- Rejected students will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modules Section -->
                <div class="content-section" id="modules">
                    <div class="section-header">
                        <h2>Module Management</h2>
                        <div class="btn-group">
                            <button class="btn btn-primary" onclick="openModuleUploadModal()">
                                <i class="fas fa-upload"></i>
                                Upload Module
                            </button>
                            <button class="btn btn-secondary" onclick="refreshModules()">
                                <i class="fas fa-refresh"></i>
                                Refresh
                            </button>
                        </div>
                    </div>
                    
                    <div class="modules-grid" id="modules-grid">
                        <div class="loading-spinner">
                            <i class="fas fa-spinner fa-spin"></i>
                            <p>Loading modules...</p>
                        </div>
                    </div>
                </div>

                <!-- Activities Section -->
                <div class="content-section" id="activities">
                    <div class="section-header">
                        <h2>Activity Management</h2>
                        <div class="btn-group">
                            <button class="btn btn-primary" onclick="openActivityUploadModal()">
                                <i class="fas fa-plus"></i>
                                Create Activity
                            </button>
                            <button class="btn btn-secondary" onclick="refreshActivities()">
                                <i class="fas fa-refresh"></i>
                                Refresh
                            </button>
                        </div>
                    </div>
                    
                    <div class="activities-grid" id="activities-grid">
                        <div class="loading-spinner">
                            <i class="fas fa-spinner fa-spin"></i>
                            <p>Loading activities...</p>
                        </div>
                    </div>
                </div>

                <!-- Analytics Section -->
                <div class="content-section" id="analytics">
                    <div class="section-header">
                        <h2>Analytics Dashboard</h2>
                        <p>Track student performance and engagement metrics</p>
                    </div>
                    
                    <!-- Analytics Filters -->
                    <div class="analytics-filters">
                        <div class="filter-group">
                            <label>Filter by Course:</label>
                            <select id="course-filter" onchange="filterAnalytics()">
                                <option value="">All Courses</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Filter by Year:</label>
                            <select id="year-filter" onchange="filterAnalytics()">
                                <option value="">All Years</option>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Time Period:</label>
                            <select id="period-filter" onchange="filterAnalytics()">
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                                <option value="semester">This Semester</option>
                                <option value="all">All Time</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Analytics Cards -->
                    <div class="analytics-grid">
                        <div class="analytics-card">
                            <div class="card-header">
                                <h3>Student Performance</h3>
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="card-content">
                                <div class="metric-item">
                                    <div class="metric-value" id="avg-grade">0</div>
                                    <div class="metric-label">Average Grade</div>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-value" id="completion-rate">0%</div>
                                    <div class="metric-label">Completion Rate</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="analytics-card">
                            <div class="card-header">
                                <h3>Module Engagement</h3>
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="card-content">
                                <div class="metric-item">
                                    <div class="metric-value" id="module-views">0</div>
                                    <div class="metric-label">Total Module Views</div>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-value" id="quiz-attempts">0</div>
                                    <div class="metric-label">Quiz Attempts</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="analytics-card">
                            <div class="card-header">
                                <h3>Activity Engagement</h3>
                                <i class="fas fa-tasks"></i>
                            </div>
                            <div class="card-content">
                                <div class="metric-item">
                                    <div class="metric-value" id="activity-submissions">0</div>
                                    <div class="metric-label">Activity Submissions</div>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-value" id="late-submissions">0</div>
                                    <div class="metric-label">Late Submissions</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="analytics-card">
                            <div class="card-header">
                                <h3>Student Distribution</h3>
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="card-content">
                                <div class="metric-item">
                                    <div class="metric-value" id="total-students">0</div>
                                    <div class="metric-label">Total Students</div>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-value" id="active-students">0</div>
                                    <div class="metric-label">Active Students</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Charts Section -->
                    <div class="charts-section">
                        <div class="chart-container">
                            <h3>Performance Trends</h3>
                            <canvas id="performance-chart"></canvas>
                        </div>
                        <div class="chart-container">
                            <h3>Engagement by Course</h3>
                            <canvas id="course-engagement-chart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Incomplete Activities -->
                    <div class="incomplete-activities">
                        <h3>Students with Incomplete Activities</h3>
                        <div class="incomplete-list" id="incomplete-activities-list">
                            <div class="loading-spinner">
                                <i class="fas fa-spinner fa-spin"></i>
                                <p>Loading incomplete activities...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leaderboard Section -->
                <div class="content-section" id="leaderboard">
                    <div class="section-header">
                        <h2>Student Leaderboard</h2>
                        <p>Top performing students and engagement rankings</p>
                    </div>
                    
                    <!-- Leaderboard Filters -->
                    <div class="leaderboard-filters">
                        <div class="filter-group">
                            <label>Ranking Type:</label>
                            <select id="ranking-type" onchange="loadLeaderboard()">
                                <option value="overall">Overall Performance</option>
                                <option value="modules">Module Engagement</option>
                                <option value="activities">Activity Completion</option>
                                <option value="quizzes">Quiz Performance</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Course:</label>
                            <select id="leaderboard-course" onchange="loadLeaderboard()">
                                <option value="">All Courses</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Year:</label>
                            <select id="leaderboard-year" onchange="loadLeaderboard()">
                                <option value="">All Years</option>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Top Performers -->
                    <div class="top-performers">
                        <div class="podium">
                            <div class="podium-place second">
                                <div class="student-card">
                                    <div class="rank">2</div>
                                    <div class="student-info" id="second-place">
                                        <div class="student-name">Loading...</div>
                                        <div class="student-score">0 points</div>
                                    </div>
                                </div>
                            </div>
                            <div class="podium-place first">
                                <div class="student-card">
                                    <div class="rank">1</div>
                                    <div class="student-info" id="first-place">
                                        <div class="student-name">Loading...</div>
                                        <div class="student-score">0 points</div>
                                    </div>
                                </div>
                            </div>
                            <div class="podium-place third">
                                <div class="student-card">
                                    <div class="rank">3</div>
                                    <div class="student-info" id="third-place">
                                        <div class="student-name">Loading...</div>
                                        <div class="student-score">0 points</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Full Leaderboard -->
                    <div class="leaderboard-table">
                        <h3>Complete Rankings</h3>
                        <div class="table-container">
                            <table class="leaderboard-table-content">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Student</th>
                                        <th>Course</th>
                                        <th>Year</th>
                                        <th>Score</th>
                                        <th>Modules Completed</th>
                                        <th>Activities Completed</th>
                                        <th>Quiz Average</th>
                                    </tr>
                                </thead>
                                <tbody id="leaderboard-tbody">
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <div class="loading-spinner">
                                                <i class="fas fa-spinner fa-spin"></i>
                                                <p>Loading leaderboard...</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Profile Section -->
                <div class="content-section" id="profile">
                    <div class="section-header">
                        <h2>My Profile</h2>
                        <p>Manage your faculty account information</p>
                    </div>
                    
                    <div class="profile-card">
                        <div class="profile-header">
                            <div class="profile-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="profile-info">
                                <h3 id="profile-name">Loading...</h3>
                                <p>Faculty Member</p>
                            </div>
                        </div>
                        
                        <div class="profile-details">
                            <div class="detail-item">
                                <label>Faculty ID:</label>
                                <span id="profile-faculty-id">Loading...</span>
                            </div>
                            <div class="detail-item">
                                <label>Email:</label>
                                <span id="profile-email">Loading...</span>
                            </div>
                            <div class="detail-item">
                                <label>Course:</label>
                                <span id="profile-course">Loading...</span>
                            </div>
                            <div class="detail-item">
                                <label>Year Level:</label>
                                <span id="profile-year-level">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Accept Student Modal -->
    <div class="modal" id="accept-student-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Accept Student</h3>
                <button class="modal-close" onclick="closeModal('accept-student-modal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to accept this student?</p>
                <div class="student-info" id="accept-student-info">
                    <!-- Student info will be loaded here -->
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('accept-student-modal')">Cancel</button>
                <button type="button" class="btn btn-success" onclick="acceptStudent()">Accept Student</button>
            </div>
        </div>
    </div>

    <!-- Reject Student Modal -->
    <div class="modal" id="reject-student-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Reject Student</h3>
                <button class="modal-close" onclick="closeModal('reject-student-modal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p>Please provide a reason for rejecting this student:</p>
                <div class="student-info" id="reject-student-info">
                    <!-- Student info will be loaded here -->
                </div>
                <div class="form-group">
                    <label>Rejection Reason</label>
                    <textarea id="rejection-reason" placeholder="Enter reason for rejection..." rows="3" required></textarea>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('reject-student-modal')">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="rejectStudent()">Reject Student</button>
            </div>
        </div>
    </div>

    <!-- Restore Student Modal -->
    <div class="modal" id="restore-student-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Restore Student</h3>
                <button class="modal-close" onclick="closeModal('restore-student-modal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to restore this student to pending status?</p>
                <div class="student-info" id="restore-student-info">
                    <!-- Student info will be loaded here -->
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('restore-student-modal')">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="restoreStudent()">Restore Student</button>
            </div>
        </div>
    </div>

    <!-- Module Upload Modal -->
    <div class="modal" id="module-upload-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Upload Module</h3>
                <button class="modal-close" onclick="closeModal('module-upload-modal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="module-upload-form" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Module Title</label>
                        <input type="text" name="module_title" placeholder="Enter module title" required>
                    </div>
                    <div class="form-group">
                        <label>Module Description</label>
                        <textarea name="module_description" placeholder="Enter module description" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Module File</label>
                        <input type="file" name="module_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                        <small>Supported formats: PDF, Word documents, Images (JPG, PNG)</small>
                    </div>
                    <div class="form-group">
                        <label>Quiz Questions (Optional)</label>
                        <textarea name="quiz_questions" placeholder="Enter quiz questions separated by new lines. Each question should be on a new line." rows="5"></textarea>
                        <small>Enter questions one per line. The system will automatically generate an interactive quiz.</small>
                    </div>
                    <div class="form-group">
                        <label>Module Type</label>
                        <select name="module_type" required>
                            <option value="">Select module type</option>
                            <option value="lesson">Lesson</option>
                            <option value="assignment">Assignment</option>
                            <option value="reference">Reference Material</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('module-upload-modal')">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="uploadModule()">Upload Module</button>
            </div>
        </div>
    </div>

    <!-- Activity Upload Modal -->
    <div class="modal" id="activity-upload-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Create Activity</h3>
                <button class="modal-close" onclick="closeModal('activity-upload-modal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="activity-upload-form" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Activity Title</label>
                        <input type="text" name="activity_title" placeholder="Enter activity title" required>
                    </div>
                    <div class="form-group">
                        <label>Activity Description</label>
                        <textarea name="activity_description" placeholder="Enter activity description" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Activity File (Optional)</label>
                        <input type="file" name="activity_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        <small>Supported formats: PDF, Word documents, Images (JPG, PNG)</small>
                    </div>
                    <div class="form-group">
                        <label>Deadline</label>
                        <input type="datetime-local" name="deadline" required>
                    </div>
                    <div class="form-group">
                        <label>Close After Deadline</label>
                        <div class="checkbox-group">
                            <input type="checkbox" name="close_after_deadline" id="close-after-deadline">
                            <label for="close-after-deadline">Automatically close activity after deadline passes</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Activity Type</label>
                        <select name="activity_type" required>
                            <option value="">Select activity type</option>
                            <option value="assignment">Assignment</option>
                            <option value="quiz">Quiz</option>
                            <option value="project">Project</option>
                            <option value="presentation">Presentation</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Points</label>
                        <input type="number" name="points" placeholder="Enter points for this activity" min="1" required>
                    </div>
                </form>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('activity-upload-modal')">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createActivity()">Create Activity</button>
            </div>
        </div>
    </div>

    <script src="js/faculty-dashboard.js"></script>
</body>
</html>