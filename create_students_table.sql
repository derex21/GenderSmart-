-- Students Table Creation Script
-- This script creates the students table and related tables for the Gender Smart system

-- Create students table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    gender VARCHAR(20) NOT NULL,
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

-- Create student activity log table
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

-- Insert sample data (optional - for testing)
-- INSERT INTO students (student_id, full_name, password, gender, course, year_level, faculty_id) 
-- VALUES ('2024001', 'John Doe', '$2y$10$example_hash', 'Male', 'Computer Science', '1st Year', 1);

-- Create indexes for better performance
CREATE INDEX idx_students_student_id ON students(student_id);
CREATE INDEX idx_students_status ON students(status);
CREATE INDEX idx_students_faculty_id ON students(faculty_id);
CREATE INDEX idx_students_course ON students(course);
CREATE INDEX idx_activity_log_student_id ON student_activity_log(student_id);
CREATE INDEX idx_activity_log_action ON student_activity_log(action);
CREATE INDEX idx_activity_log_created_at ON student_activity_log(created_at);

-- Show table structure
DESCRIBE students;
DESCRIBE student_activity_log;
