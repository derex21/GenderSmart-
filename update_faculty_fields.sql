-- Migration script to update faculty_accounts table
-- Increases field sizes to support multiple courses and year levels
-- Run this script to update your existing database

USE gendersmart_db;

-- Update course field from VARCHAR(100) to VARCHAR(200)
-- This allows storing up to 2 courses separated by " | "
ALTER TABLE faculty_accounts 
MODIFY COLUMN course VARCHAR(200) NOT NULL;

-- Update year_level field from VARCHAR(20) to VARCHAR(50)
-- This allows storing up to 2 year levels separated by " | "
ALTER TABLE faculty_accounts 
MODIFY COLUMN year_level VARCHAR(50) NOT NULL;

-- Verify the changes
DESCRIBE faculty_accounts;

