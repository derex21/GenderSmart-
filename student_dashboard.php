<?php
session_start();

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Gender Smart E-Learning</title>
    <link rel="stylesheet" href="css/faculty_dashboard.css">
    <link rel="stylesheet" href="css/student_profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="faculty-dashboard">
        <!-- Sidebar -->
        <div class="faculty-sidebar">
            <div class="sidebar-header">
                <img src="../image/logo.png" alt="Gender Smart Logo" class="sidebar-logo">
                <h3>Student Portal</h3>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li class="nav-item active" data-section="dashboard">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </li>
                    <li class="nav-item" data-section="modules">
                        <i class="fas fa-book"></i>
                        <span>Modules</span>
                    </li>
                    <li class="nav-item" data-section="activities">
                        <i class="fas fa-tasks"></i>
                        <span>Activities</span>
                    </li>
                    <li class="nav-item" data-section="leaderboard">
                        <i class="fas fa-trophy"></i>
                        <span>Leaderboard</span>
                    </li>
                    <li class="nav-item" data-section="game">
                        <i class="fas fa-gamepad"></i>
                        <span>Gender Game</span>
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
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="user-details">
                        <div class="user-name" id="student-name">Loading...</div>
                        <div class="user-role">Student</div>
                    </div>
                </div>
                <a href="../BACKEND/student_logout.php" class="logout-btn">
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
                    <div class="faculty-welcome" id="student-welcome">
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
                            <h2>Welcome to Your Student Portal</h2>
                            <p>Access your course materials, webinars, and track your academic progress.</p>
                        </div>
                        <div class="welcome-stats">
                            <div class="stat-item">
                                <div class="stat-icon">
                                    <i class="fas fa-book"></i>
                                </div>
                                <div class="stat-info">
                                    <div class="stat-label">Course</div>
                                    <div class="stat-value" id="student-course">Loading...</div>
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-icon">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <div class="stat-info">
                                    <div class="stat-label">Year Level</div>
                                    <div class="stat-value" id="student-year-level">Loading...</div>
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-icon">
                                    <i class="fas fa-id-card"></i>
                                </div>
                                <div class="stat-info">
                                    <div class="stat-label">Student ID</div>
                                    <div class="stat-value" id="student-id">Loading...</div>
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
                                        <span>View Modules</span>
                                    </button>
                                    <button class="action-btn" onclick="showSection('activities')">
                                        <i class="fas fa-tasks"></i>
                                        <span>View Activities</span>
                                    </button>
                                    <button class="action-btn" onclick="showSection('leaderboard')">
                                        <i class="fas fa-trophy"></i>
                                        <span>Leaderboard</span>
                                    </button>
                                    <button class="action-btn" onclick="showSection('game')">
                                        <i class="fas fa-gamepad"></i>
                                        <span>Play Game</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modules Section -->
                <div class="content-section" id="modules">
                    <div class="section-header">
                        <h2>Available Modules</h2>
                        <p>Access interactive modules uploaded by your faculty</p>
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
                        <h2>My Activities</h2>
                        <p>Submit your assignments and track your progress</p>
                    </div>
                    
                    <div class="activities-grid" id="activities-grid">
                        <div class="loading-spinner">
                            <i class="fas fa-spinner fa-spin"></i>
                            <p>Loading activities...</p>
                        </div>
                    </div>
                </div>

                <!-- Leaderboard Section -->
                <div class="content-section" id="leaderboard">
                    <div class="section-header">
                        <h2>Student Leaderboard</h2>
                        <p>See how you rank among your peers</p>
                    </div>
                    
                    <div class="leaderboard-container">
                        <div class="my-rank-card">
                            <h3>Your Current Rank</h3>
                            <div class="rank-info" id="my-rank-info">
                                <div class="loading-spinner">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <p>Loading your rank...</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="leaderboard-table">
                            <h3>Top Performers</h3>
                            <div class="table-container">
                                <table class="leaderboard-table-content">
                                    <thead>
                                        <tr>
                                            <th>Rank</th>
                                            <th>Student</th>
                                            <th>Course</th>
                                            <th>Year</th>
                                            <th>Score</th>
                                            <th>Modules</th>
                                            <th>Activities</th>
                                            <th>Game Points</th>
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
                </div>

                <!-- Game Section -->
                <div class="content-section" id="game">
                    <div class="section-header">

                        <h2>Learn about gender topics through interactive gameplay</h2>
                    </div>
                    
                    <!-- Engaging Game Hero -->
                    <div class="game-hero" style="display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 18px; border-radius: 14px; background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); color: #fff; margin-bottom: 18px; box-shadow: 0 10px 24px rgba(0,0,0,0.12);">
                        <div style="flex: 1;">
                            <div style="font-size: 1.4rem; font-weight: 700; margin-bottom: 6px;">Play & Learn: 4 Pics 1 Word</div>
                            <div style="opacity: 0.95; line-height: 1.5; margin-bottom: 10px;">Improve your understanding of gender studies through quick, fun mini‑games.</div>
                            <a href="game/4pics1words.html" style="display: inline-flex; align-items: center; gap: 8px; background: #ffffff; color: #2b2b2b; border-radius: 999px; padding: 10px 14px; font-weight: 600; text-decoration: none; box-shadow: 0 6px 16px rgba(0,0,0,0.12);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 22px rgba(0,0,0,0.18)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.12)';">
                                <i class="fas fa-play"></i> Play
                            </a>
                        </div>
                        <div style="flex: 0 0 220px; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 18px rgba(0,0,0,0.15); background: rgba(255,255,255,0.15); backdrop-filter: blur(2px);">
                            <img src="../image/3.jpg" alt="Game Preview" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                    </div>
                    
                       <!-- Featured Games -->
                       <div class="game-list" style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 6px;">
                        <!-- Gender Flip Hero-style Card -->
                        <div class="game-hero" style="display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 18px; border-radius: 14px; background: linear-gradient(135deg, #ff6a88 0%, #ff99ac 100%); color: #fff; box-shadow: 0 10px 24px rgba(0,0,0,0.12);">
                            <div style="flex: 1;">
                                <div style="font-size: 1.2rem; font-weight: 700; margin-bottom: 6px;">Play & Learn: Gender Flip</div>
                                <div style="opacity: 0.95; line-height: 1.5; margin-bottom: 10px;">Explore perspectives by flipping roles and scenarios.</div>
                                <a href="game/gamesflip.html" style="display: inline-flex; align-items: center; gap: 8px; background: #ffffff; color: #2b2b2b; border-radius: 999px; padding: 8px 12px; font-weight: 600; text-decoration: none; box-shadow: 0 6px 16px rgba(0,0,0,0.12);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 22px rgba(0,0,0,0.18)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.12)';">
                                    <i class="fas fa-play"></i> Play 
                                </a>
                            </div>
                            <div style="flex: 0 0 220px; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 18px rgba(0,0,0,0.15); background: rgba(255,255,255,0.15); backdrop-filter: blur(2px);">
                                <img src="../image/1.jpg" alt="Gender Flip Preview" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        </div>
                        <!-- Gender Stereotypes Hero-style Card -->
                        <div class="game-hero" style="display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 18px; border-radius: 14px; background: linear-gradient(135deg, #00b09b 0%, #96c93d 100%); color: #fff; box-shadow: 0 10px 24px rgba(0,0,0,0.12);">
                            <div style="flex: 1;">
                                <div style="font-size: 1.2rem; font-weight: 700; margin-bottom: 6px;">Play & Learn: Gender Stereotypes</div>
                                <div style="opacity: 0.95; line-height: 1.5; margin-bottom: 10px;">Challenge and learn about common gender stereotypes.</div>
                                <a href="game/genderstereo.html" style="display: inline-flex; align-items: center; gap: 8px; background: #ffffff; color: #2b2b2b; border-radius: 999px; padding: 8px 12px; font-weight: 600; text-decoration: none; box-shadow: 0 6px 16px rgba(0,0,0,0.12);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 22px rgba(0,0,0,0.18)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.12)';">
                                    <i class="fas fa-play"></i> Play
                                </a>
                            </div>
                            <div style="flex: 0 0 220px; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 18px rgba(0,0,0,0.15); background: rgba(255,255,255,0.15); backdrop-filter: blur(2px);">
                                <img src="../image/2.png" alt="Gender Stereotypes Preview" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        </div>
                    </div>

                

                <!-- Profile Section -->
                <div class="content-section" id="profile">
                    <div class="section-header">
                        <h2>My Profile</h2>
                        <p>Manage your student account information</p>
                    </div>
                    
                    <div class="profile-container">
                        <!-- Profile Overview Card -->
                        <div class="profile-card">
                            <div class="profile-header">
                                <div class="profile-avatar">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <div class="profile-info">
                                    <h3 id="profile-name">Loading...</h3>
                                    <p>Student</p>
                                    <button class="btn btn-primary" onclick="toggleEditMode()">
                                        <i class="fas fa-edit"></i>
                                        Edit Profile
                                    </button>
                                </div>
                            </div>
                            
                            <div class="profile-details">
                                <div class="detail-item">
                                    <label>Student ID:</label>
                                    <span id="profile-student-id">Loading...</span>
                                </div>
                                <div class="detail-item">
                                    <label>Gender:</label>
                                    <span id="profile-gender">Loading...</span>
                                </div>
                                <div class="detail-item">
                                    <label>Course:</label>
                                    <span id="profile-course">Loading...</span>
                                </div>
                                <div class="detail-item">
                                    <label>Year Level:</label>
                                    <span id="profile-year-level">Loading...</span>
                                </div>
                                <div class="detail-item">
                                    <label>Status:</label>
                                    <span id="profile-status" class="status-accepted">Accepted</span>
                                </div>
                                <div class="detail-item">
                                    <label>Member Since:</label>
                                    <span id="profile-created-at">Loading...</span>
                                </div>
                            </div>
                        </div>

                        <!-- Profile Statistics Card -->
                        <div class="profile-stats-card">
                            <h3>Your Statistics</h3>
                            <div class="stats-grid">
                                <div class="stat-item">
                                    <div class="stat-icon">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-number" id="profile-modules-completed">0</div>
                                        <div class="stat-label">Modules Completed</div>
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-icon">
                                        <i class="fas fa-tasks"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-number" id="profile-activities-completed">0</div>
                                        <div class="stat-label">Activities Completed</div>
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-icon">
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-number" id="profile-total-points">0</div>
                                        <div class="stat-label">Total Points</div>
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-icon">
                                        <i class="fas fa-gamepad"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-number" id="profile-game-points">0</div>
                                        <div class="stat-label">Game Points</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Edit Profile Form (Hidden by default) -->
                        <div class="profile-edit-form" id="profile-edit-form" style="display: none;">
                            <div class="form-header">
                                <h3>Edit Profile</h3>
                                <button class="btn btn-secondary" onclick="cancelEdit()">
                                    <i class="fas fa-times"></i>
                                    Cancel
                                </button>
                            </div>
                            <form id="edit-profile-form">
                                <div class="form-group">
                                    <label for="edit-full-name">Full Name:</label>
                                    <input type="text" id="edit-full-name" name="full_name" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit-gender">Gender:</label>
                                    <select id="edit-gender" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save"></i>
                                        Save Changes
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="cancelEdit()">
                                        <i class="fas fa-times"></i>
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/student-dashboard.js"></script>
</body>
</html>
