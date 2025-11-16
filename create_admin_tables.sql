-- Additional tables for admin dashboard functionality
-- Run this after creating the basic admin_users table

USE gendersmart_db;

-- Faculty accounts table
CREATE TABLE IF NOT EXISTS faculty_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_id VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
        course VARCHAR(200) NOT NULL,
        year_level VARCHAR(50) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admin_users(id) ON DELETE CASCADE
);

-- Webinars table
CREATE TABLE IF NOT EXISTS webinars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    speaker_name VARCHAR(100) NOT NULL,
    speaker_title VARCHAR(100),
    speaker_avatar VARCHAR(255),
    webinar_date DATETIME NOT NULL,
    duration INT NOT NULL, -- in minutes
    category ENUM('upcoming', 'recorded', 'popular') DEFAULT 'upcoming',
    webinar_link VARCHAR(500),
    google_form_link VARCHAR(500),
    image_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admin_users(id) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX idx_faculty_id ON faculty_accounts(faculty_id);
CREATE INDEX idx_faculty_email ON faculty_accounts(email);
CREATE INDEX idx_faculty_active ON faculty_accounts(is_active);
CREATE INDEX idx_webinar_category ON webinars(category);
CREATE INDEX idx_webinar_date ON webinars(webinar_date);
CREATE INDEX idx_webinar_active ON webinars(is_active);
