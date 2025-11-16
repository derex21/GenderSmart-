-- Final Students Table Structure
-- This is the complete SQL for the students table with faculty approval workflow

-- Students table with complete structure
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    course VARCHAR(100) NOT NULL,
    year_level VARCHAR(20) NOT NULL,
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    faculty_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    accepted_at TIMESTAMP NULL,
    rejected_at TIMESTAMP NULL,
    rejection_reason TEXT,
    FOREIGN KEY (faculty_id) REFERENCES faculty_accounts(id) ON DELETE CASCADE
);

-- Student activity log table
CREATE TABLE IF NOT EXISTS student_activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_students_student_id ON students(student_id);
CREATE INDEX IF NOT EXISTS idx_students_status ON students(status);
CREATE INDEX IF NOT EXISTS idx_students_faculty_id ON students(faculty_id);
CREATE INDEX IF NOT EXISTS idx_students_course ON students(course);
CREATE INDEX IF NOT EXISTS idx_activity_log_student_id ON student_activity_log(student_id);
CREATE INDEX IF NOT EXISTS idx_activity_log_action ON student_activity_log(action);
CREATE INDEX IF NOT EXISTS idx_activity_log_created_at ON student_activity_log(created_at);

-- Show table structure
DESCRIBE students;
DESCRIBE student_activity_log;
