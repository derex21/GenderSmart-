-- Create modules table
CREATE TABLE IF NOT EXISTS modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(500) NOT NULL,
    file_type ENUM('pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png') NOT NULL,
    file_size INT NOT NULL,
    module_type ENUM('lesson', 'assignment', 'reference') NOT NULL,
    quiz_questions TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (faculty_id) REFERENCES faculty_accounts(id) ON DELETE CASCADE
);

-- Create activities table
CREATE TABLE IF NOT EXISTS activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(500),
    file_type ENUM('pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'),
    file_size INT,
    activity_type ENUM('assignment', 'quiz', 'project', 'presentation') NOT NULL,
    deadline DATETIME NOT NULL,
    points INT NOT NULL,
    close_after_deadline BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (faculty_id) REFERENCES faculty_accounts(id) ON DELETE CASCADE
);

-- Create student module access table
CREATE TABLE IF NOT EXISTS student_module_access (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    module_id INT NOT NULL,
    accessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completion_status ENUM('not_started', 'in_progress', 'completed') DEFAULT 'not_started',
    quiz_score DECIMAL(5,2),
    quiz_attempts INT DEFAULT 0,
    last_accessed TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_module (student_id, module_id)
);

-- Create student activity submissions table
CREATE TABLE IF NOT EXISTS student_activity_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    activity_id INT NOT NULL,
    submission_file VARCHAR(500),
    submission_text TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    grade DECIMAL(5,2),
    feedback TEXT,
    is_late BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_activity (student_id, activity_id)
);

-- Create indexes for performance
CREATE INDEX idx_modules_faculty_id ON modules(faculty_id);
CREATE INDEX idx_modules_created_at ON modules(created_at);
CREATE INDEX idx_activities_faculty_id ON activities(faculty_id);
CREATE INDEX idx_activities_deadline ON activities(deadline);
CREATE INDEX idx_activities_created_at ON activities(created_at);
CREATE INDEX idx_student_module_access_student_id ON student_module_access(student_id);
CREATE INDEX idx_student_module_access_module_id ON student_module_access(module_id);
CREATE INDEX idx_student_activity_submissions_student_id ON student_activity_submissions(student_id);
CREATE INDEX idx_student_activity_submissions_activity_id ON student_activity_submissions(activity_id);
