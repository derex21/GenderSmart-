// Student Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeStudentDashboard();
});

function initializeStudentDashboard() {
    // Load student data
    loadStudentData();
    
    // Load dashboard data
    loadDashboardData();
    
    // Setup event listeners
    setupEventListeners();
}

function setupEventListeners() {
    // Sidebar toggle
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }
    
    // Navigation items
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
        item.addEventListener('click', function() {
            const section = this.getAttribute('data-section');
            showSection(section);
        });
    });
}

function toggleSidebar() {
    const sidebar = document.querySelector('.faculty-sidebar');
    sidebar.classList.toggle('open');
}

function showSection(sectionName) {
    // Hide all sections
    const sections = document.querySelectorAll('.content-section');
    sections.forEach(section => section.classList.remove('active'));
    
    // Remove active class from nav items
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => item.classList.remove('active'));
    
    // Show selected section
    const targetSection = document.getElementById(sectionName);
    if (targetSection) {
        targetSection.classList.add('active');
    }
    
    // Add active class to nav item
    const activeNavItem = document.querySelector(`[data-section="${sectionName}"]`);
    if (activeNavItem) {
        activeNavItem.classList.add('active');
    }
    
    // Update page title
    const pageTitle = document.getElementById('page-title');
    const titles = {
        'dashboard': 'Dashboard',
        'modules': 'Modules',
        'activities': 'Activities',
        'leaderboard': 'Leaderboard',
        'game': 'Gender Game',
        'profile': 'My Profile'
    };
    pageTitle.textContent = titles[sectionName] || 'Dashboard';
    
    // Load section-specific data
    if (sectionName === 'modules') {
        loadModules();
    } else if (sectionName === 'activities') {
        loadActivities();
    } else if (sectionName === 'leaderboard') {
        loadLeaderboard();
    } else if (sectionName === 'game') {
        loadGame();
    }
}

function loadStudentData() {
    // Fetch student data from backend
    fetch('../BACKEND/student_profile_api.php?action=get_student_data')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateStudentUI(data.student);
            } else {
                console.error('Error loading student data:', data.error);
                showNotification('Error loading student data', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading student data:', error);
            showNotification('Error loading student data', 'error');
        });
}

function updateStudentUI(student) {
    // Update sidebar information
    const studentName = document.getElementById('student-name');
    const studentWelcome = document.getElementById('student-welcome');
    const studentCourse = document.getElementById('student-course');
    const studentYearLevel = document.getElementById('student-year-level');
    const studentId = document.getElementById('student-id');
    
    if (studentName) studentName.textContent = student.full_name || 'Student';
    if (studentWelcome) studentWelcome.textContent = `Welcome back, ${student.full_name || 'Student'}!`;
    if (studentCourse) studentCourse.textContent = student.course || 'N/A';
    if (studentYearLevel) studentYearLevel.textContent = student.year_level || 'N/A';
    if (studentId) studentId.textContent = student.student_id || 'N/A';
    
    // Update profile section information
    const profileName = document.getElementById('profile-name');
    const profileStudentId = document.getElementById('profile-student-id');
    const profileGender = document.getElementById('profile-gender');
    const profileCourse = document.getElementById('profile-course');
    const profileYearLevel = document.getElementById('profile-year-level');
    const profileStatus = document.getElementById('profile-status');
    const profileCreatedAt = document.getElementById('profile-created-at');
    
    if (profileName) profileName.textContent = student.full_name || 'Loading...';
    if (profileStudentId) profileStudentId.textContent = student.student_id || 'Loading...';
    if (profileGender) profileGender.textContent = student.gender || 'Loading...';
    if (profileCourse) profileCourse.textContent = student.course || 'Loading...';
    if (profileYearLevel) profileYearLevel.textContent = student.year_level || 'Loading...';
    if (profileStatus) {
        profileStatus.textContent = student.status || 'Accepted';
        profileStatus.className = `status-${student.status?.toLowerCase() || 'accepted'}`;
    }
    if (profileCreatedAt && student.created_at) {
        const date = new Date(student.created_at);
        profileCreatedAt.textContent = date.toLocaleDateString();
    }
    
    // Update profile statistics if available
    if (student.stats) {
        updateProfileStats(student.stats);
    }
}

function updateProfileStats(stats) {
    const modulesCompleted = document.getElementById('profile-modules-completed');
    const activitiesCompleted = document.getElementById('profile-activities-completed');
    const totalPoints = document.getElementById('profile-total-points');
    const gamePoints = document.getElementById('profile-game-points');
    
    if (modulesCompleted) modulesCompleted.textContent = stats.modules_completed || 0;
    if (activitiesCompleted) activitiesCompleted.textContent = stats.activities_completed || 0;
    if (totalPoints) totalPoints.textContent = stats.total_points || 0;
    if (gamePoints) gamePoints.textContent = stats.game_points || 0;
}

// Profile editing functions
function toggleEditMode() {
    const editForm = document.getElementById('profile-edit-form');
    const profileCard = document.querySelector('.profile-card');
    
    if (editForm.style.display === 'none') {
        editForm.style.display = 'block';
        profileCard.style.display = 'none';
        loadEditForm();
    } else {
        editForm.style.display = 'none';
        profileCard.style.display = 'block';
    }
}

function loadEditForm() {
    // Populate edit form with current data
    const fullName = document.getElementById('profile-name').textContent;
    const gender = document.getElementById('profile-gender').textContent;
    
    document.getElementById('edit-full-name').value = fullName;
    document.getElementById('edit-gender').value = gender;
    
    // Setup form submission
    const form = document.getElementById('edit-profile-form');
    form.onsubmit = handleProfileUpdate;
}

function handleProfileUpdate(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = {
        full_name: formData.get('full_name'),
        gender: formData.get('gender')
    };
    
    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Saving...';
    submitBtn.disabled = true;
    
    fetch('../BACKEND/student_profile_api.php?action=update_profile', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Profile updated successfully!', 'success');
            // Reload student data to update UI
            loadStudentData();
            cancelEdit();
        } else {
            showNotification('Error updating profile: ' + data.error, 'error');
        }
    })
    .catch(error => {
        console.error('Error updating profile:', error);
        showNotification('Error updating profile', 'error');
    })
    .finally(() => {
        // Restore button state
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
}

function cancelEdit() {
    const editForm = document.getElementById('profile-edit-form');
    const profileCard = document.querySelector('.profile-card');
    
    editForm.style.display = 'none';
    profileCard.style.display = 'block';
}

function loadDashboardData() {
    // Load recent modules and activities
    loadRecentModules();
    loadRecentActivities();
}

function loadRecentModules() {
    fetch('../BACKEND/student_modules_api.php?action=get_recent_modules')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayRecentModules(data.modules);
            } else {
                console.error('Error loading recent modules:', data.error);
            }
        })
        .catch(error => {
            console.error('Error loading recent modules:', error);
        });
}

function displayRecentModules(modules) {
    const container = document.getElementById('recent-module-list');
    if (!container) return;
    
    if (modules.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-book"></i>
                <p>No modules available yet</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = '';
    modules.slice(0, 3).forEach(module => {
        const moduleCard = createModuleCard(module);
        container.appendChild(moduleCard);
    });
}

function createModuleCard(module) {
    const card = document.createElement('div');
    card.className = 'module-card';
    
    const fileIcon = getFileIcon(module.file_type);
    const isCompleted = module.completed_at ? true : false;
    
    card.innerHTML = `
        <div class="module-header">
            <div class="module-icon">
                <i class="fas fa-${fileIcon}"></i>
            </div>
            <div class="module-info">
                <h4>${module.title}</h4>
                <p>${module.module_type}</p>
            </div>
            <div class="module-status">
                ${isCompleted ? '<span class="status-completed">Completed</span>' : '<span class="status-pending">Pending</span>'}
            </div>
        </div>
        <div class="module-actions">
            <button class="btn btn-primary" onclick="viewModule(${module.id})">
                <i class="fas fa-eye"></i>
                View Module
            </button>
            ${module.quiz_questions ? `
                <button class="btn btn-secondary" onclick="takeQuiz(${module.id})">
                    <i class="fas fa-question-circle"></i>
                    Take Quiz
                </button>
            ` : ''}
        </div>
    `;
    
    return card;
}

function loadRecentActivities() {
    fetch('../BACKEND/student_activities_api.php?action=get_recent_activities')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayRecentActivities(data.activities);
            } else {
                console.error('Error loading recent activities:', data.error);
            }
        })
        .catch(error => {
            console.error('Error loading recent activities:', error);
        });
}

function displayRecentActivities(activities) {
    const container = document.getElementById('recent-activity-list');
    if (!container) return;
    
    if (activities.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-tasks"></i>
                <p>No activities available yet</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = '';
    activities.slice(0, 3).forEach(activity => {
        const activityCard = createActivityCard(activity);
        container.appendChild(activityCard);
    });
}

function createActivityCard(activity) {
    const card = document.createElement('div');
    card.className = 'activity-card';
    
    const deadlineDate = new Date(activity.deadline);
    const isOverdue = deadlineDate < new Date() && !activity.submitted_at;
    const isSubmitted = activity.submitted_at ? true : false;
    
    card.innerHTML = `
        <div class="activity-header">
            <div class="activity-icon">
                <i class="fas fa-${getActivityIcon(activity.activity_type)}"></i>
            </div>
            <div class="activity-info">
                <h4>${activity.title}</h4>
                <p>${activity.activity_type} • ${activity.points} points</p>
            </div>
            <div class="activity-status">
                ${isSubmitted ? '<span class="status-submitted">Submitted</span>' : 
                  isOverdue ? '<span class="status-overdue">Overdue</span>' : 
                  '<span class="status-pending">Pending</span>'}
            </div>
        </div>
        <div class="activity-deadline">
            <i class="fas fa-clock"></i>
            <span>Due: ${formatDate(activity.deadline)}</span>
        </div>
        <div class="activity-actions">
            <button class="btn btn-primary" onclick="viewActivity(${activity.id})">
                <i class="fas fa-eye"></i>
                View Activity
            </button>
            ${!isSubmitted ? `
                <button class="btn btn-success" onclick="submitActivity(${activity.id})">
                    <i class="fas fa-upload"></i>
                    Submit
                </button>
            ` : ''}
        </div>
    `;
    
    return card;
}

// Modules Section Functions
function loadModules() {
    fetch('../BACKEND/student_modules_api.php?action=get_modules')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayModules(data.modules);
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
    const container = document.getElementById('modules-grid');
    if (!container) return;
    
    if (modules.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-book"></i>
                <p>No modules available yet</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = '';
    modules.forEach(module => {
        const moduleCard = createModuleCard(module);
        container.appendChild(moduleCard);
    });
}

function viewModule(moduleId) {
    // Open module in new window or modal
    window.open(`../BACKEND/view_module.php?id=${moduleId}`, '_blank');
}

function takeQuiz(moduleId) {
    // Open quiz in new window or modal
    window.open(`../BACKEND/take_quiz.php?module_id=${moduleId}`, '_blank');
}

// Activities Section Functions
function loadActivities() {
    fetch('../BACKEND/student_activities_api.php?action=get_activities')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayActivities(data.activities);
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
    const container = document.getElementById('activities-grid');
    if (!container) return;
    
    if (activities.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-tasks"></i>
                <p>No activities available yet</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = '';
    activities.forEach(activity => {
        const activityCard = createActivityCard(activity);
        container.appendChild(activityCard);
    });
}

function viewActivity(activityId) {
    // Open activity details modal
    openActivityModal(activityId);
}

function submitActivity(activityId) {
    // Open submission modal
    openSubmissionModal(activityId);
}

// Leaderboard Functions
function loadLeaderboard() {
    fetch('../BACKEND/student_leaderboard_api.php?action=get_leaderboard')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayLeaderboard(data.leaderboard);
                displayMyRank(data.my_rank);
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
            <td>${student.game_points || 0}</td>
        `;
        tbody.appendChild(row);
    });
}

function displayMyRank(myRank) {
    const container = document.getElementById('my-rank-info');
    if (!container) return;
    
    container.innerHTML = `
        <div class="rank-display">
            <div class="rank-number">#${myRank.rank}</div>
            <div class="rank-details">
                <div class="rank-score">${Math.round(myRank.overall_score)} points</div>
                <div class="rank-breakdown">
                    <span>Modules: ${myRank.modules_completed}</span>
                    <span>Activities: ${myRank.activities_completed}</span>
                    <span>Game: ${myRank.game_points || 0}</span>
                </div>
            </div>
        </div>
    `;
}

// Game Functions
function loadGame() {
    // Load game statistics
    loadGameStats();
    
    // Initialize the game
    initializeGenderGame();
}

function loadGameStats() {
    fetch('../BACKEND/student_game_api.php?action=get_game_stats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateGameStats(data.stats);
            }
        })
        .catch(error => {
            console.error('Error loading game stats:', error);
        });
}

function updateGameStats(stats) {
    document.getElementById('total-points').textContent = stats.total_points || 0;
    document.getElementById('games-played').textContent = stats.games_played || 0;
    document.getElementById('high-score').textContent = stats.high_score || 0;
}

function initializeGenderGame() {
    const gameFrame = document.getElementById('game-frame');
    if (!gameFrame) return;
    
    // Create a simple gender equality quiz game
    gameFrame.innerHTML = `
        <div class="game-container">
            <div class="game-header">
                <h3>Gender Equality Quiz</h3>
                <div class="game-score">
                    <span>Score: </span>
                    <span id="game-score">0</span>
                </div>
            </div>
            <div class="game-content">
                <div id="question-container">
                    <div class="question" id="current-question">
                        <h4 id="question-text">Loading questions...</h4>
                        <div id="question-options">
                            <!-- Options will be loaded here -->
                        </div>
                    </div>
                </div>
                <div class="game-controls">
                    <button id="next-question" class="btn btn-primary" onclick="nextQuestion()" disabled>
                        Next Question
                    </button>
                    <button id="submit-game" class="btn btn-success" onclick="submitGame()" style="display: none;">
                        Submit Game
                    </button>
                </div>
            </div>
        </div>
    `;
    
    // Load game questions
    loadGameQuestions();
}

function loadGameQuestions() {
    // Sample gender equality questions
    const questions = [
        {
            question: "What is the primary goal of gender equality?",
            options: [
                "To make women superior to men",
                "To ensure equal rights and opportunities for all genders",
                "To eliminate all gender differences",
                "To focus only on women's rights"
            ],
            correct: 1,
            points: 10
        },
        {
            question: "Which of the following is an example of gender discrimination?",
            options: [
                "Paying different salaries for the same job based on gender",
                "Providing equal opportunities for all genders",
                "Respecting different gender identities",
                "Promoting diversity in the workplace"
            ],
            correct: 0,
            points: 10
        },
        {
            question: "What does 'intersectionality' mean in the context of gender equality?",
            options: [
                "Focusing only on women's issues",
                "Understanding how different forms of discrimination intersect",
                "Eliminating all gender categories",
                "Promoting only one gender perspective"
            ],
            correct: 1,
            points: 15
        },
        {
            question: "Which is a key principle of gender equality in education?",
            options: [
                "Separating students by gender",
                "Providing equal access to education regardless of gender",
                "Focusing only on traditional gender roles",
                "Eliminating gender studies programs"
            ],
            correct: 1,
            points: 10
        },
        {
            question: "What is the importance of gender equality in the workplace?",
            options: [
                "To favor one gender over another",
                "To create equal opportunities and fair treatment for all genders",
                "To eliminate all gender differences",
                "To focus only on women's advancement"
            ],
            correct: 1,
            points: 15
        }
    ];
    
    startGame(questions);
}

let currentQuestion = 0;
let gameScore = 0;
let gameQuestions = [];
let selectedAnswers = [];

function startGame(questions) {
    gameQuestions = questions;
    currentQuestion = 0;
    gameScore = 0;
    selectedAnswers = [];
    
    displayQuestion();
}

function displayQuestion() {
    const question = gameQuestions[currentQuestion];
    const questionText = document.getElementById('question-text');
    const questionOptions = document.getElementById('question-options');
    const nextButton = document.getElementById('next-question');
    const submitButton = document.getElementById('submit-game');
    
    questionText.textContent = question.question;
    
    questionOptions.innerHTML = '';
    question.options.forEach((option, index) => {
        const optionElement = document.createElement('div');
        optionElement.className = 'option';
        optionElement.innerHTML = `
            <input type="radio" name="answer" value="${index}" id="option-${index}">
            <label for="option-${index}">${option}</label>
        `;
        questionOptions.appendChild(optionElement);
    });
    
    nextButton.disabled = true;
    nextButton.onclick = () => selectAnswer();
    
    if (currentQuestion === gameQuestions.length - 1) {
        nextButton.style.display = 'none';
        submitButton.style.display = 'inline-block';
    }
}

function selectAnswer() {
    const selectedOption = document.querySelector('input[name="answer"]:checked');
    if (!selectedOption) return;
    
    const answerIndex = parseInt(selectedOption.value);
    selectedAnswers[currentQuestion] = answerIndex;
    
    // Check if answer is correct
    const question = gameQuestions[currentQuestion];
    if (answerIndex === question.correct) {
        gameScore += question.points;
        document.getElementById('game-score').textContent = gameScore;
    }
    
    // Move to next question
    currentQuestion++;
    if (currentQuestion < gameQuestions.length) {
        displayQuestion();
    } else {
        submitGame();
    }
}

function nextQuestion() {
    selectAnswer();
}

function submitGame() {
    // Calculate final score
    const finalScore = gameScore;
    const maxScore = gameQuestions.reduce((sum, q) => sum + q.points, 0);
    const percentage = Math.round((finalScore / maxScore) * 100);
    
    // Show results
    const gameFrame = document.getElementById('game-frame');
    gameFrame.innerHTML = `
        <div class="game-results">
            <div class="results-header">
                <h3>Game Complete!</h3>
                <div class="final-score">
                    <span>Final Score: ${finalScore}/${maxScore}</span>
                    <span>Percentage: ${percentage}%</span>
                </div>
            </div>
            <div class="results-details">
                <p>Great job! You've earned ${finalScore} points for your leaderboard ranking.</p>
                <div class="results-actions">
                    <button class="btn btn-primary" onclick="saveGameScore(${finalScore})">
                        Save Score
                    </button>
                    <button class="btn btn-secondary" onclick="playAgain()">
                        Play Again
                    </button>
                </div>
            </div>
        </div>
    `;
}

function saveGameScore(score) {
    fetch('../BACKEND/student_game_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'save_score',
            score: score
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Score saved successfully!', 'success');
            loadGameStats(); // Refresh stats
            loadLeaderboard(); // Refresh leaderboard
        } else {
            showNotification('Error saving score: ' + data.error, 'error');
        }
    })
    .catch(error => {
        console.error('Error saving score:', error);
        showNotification('Error saving score', 'error');
    });
}

function playAgain() {
    initializeGenderGame();
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
        'presentation': 'presentation'
    };
    return icons[activityType] || 'tasks';
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Remove notification after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}