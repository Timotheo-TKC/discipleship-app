-- Initialize MySQL database for Laravel Discipleship App
-- This script runs when the MySQL container starts for the first time

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS discipleship_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user if it doesn't exist
CREATE USER IF NOT EXISTS 'discipleship'@'%' IDENTIFIED BY 'password';

-- Grant privileges
GRANT ALL PRIVILEGES ON discipleship_app.* TO 'discipleship'@'%';
GRANT ALL PRIVILEGES ON discipleship_app.* TO 'root'@'%';

-- Flush privileges
FLUSH PRIVILEGES;

-- Use the database
USE discipleship_app;

-- Set timezone
SET time_zone = '+03:00';
