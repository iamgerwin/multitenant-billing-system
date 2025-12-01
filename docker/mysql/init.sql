-- MySQL initialization script for Multitenant Billing System
-- This script runs when the MySQL container is first created

-- Ensure the database exists
CREATE DATABASE IF NOT EXISTS billing_system;

-- Grant privileges
GRANT ALL PRIVILEGES ON billing_system.* TO 'root'@'%';
FLUSH PRIVILEGES;

-- Use the database
USE billing_system;

-- Set default character set and collation
ALTER DATABASE billing_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
