-- Gender Smart Admin Database Creation Script
-- Run this script to create the admin database and table

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS gendersmart_db;
USE gendersmart_db;

-- Create admin_users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('super_admin', 'admin', 'moderator') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL
);

-- Create admin_activity_log table for tracking admin actions
CREATE TABLE IF NOT EXISTS admin_activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE CASCADE
);

-- Insert default super admin (password: admin123)
-- Change this password immediately after first login
INSERT INTO admin_users (username, password, full_name, email, role) 
VALUES (
    'superadmin', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'Super Administrator', 
    'admin@gendersmart.com', 
    'super_admin'
) ON DUPLICATE KEY UPDATE username=username;

-- Create indexes for better performance
CREATE INDEX idx_admin_username ON admin_users(username);
CREATE INDEX idx_admin_email ON admin_users(email);
CREATE INDEX idx_admin_active ON admin_users(is_active);
CREATE INDEX idx_activity_admin_id ON admin_activity_log(admin_id);
CREATE INDEX idx_activity_created_at ON admin_activity_log(created_at);

-- Grant necessary permissions (adjust as needed for your setup)
-- GRANT ALL PRIVILEGES ON gendersmart_db.* TO 'your_db_user'@'localhost';
-- FLUSH PRIVILEGES;
