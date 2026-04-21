-- Migration script to update the quiz_system database to the latest schema
-- Run this in phpMyAdmin or MySQL command line after selecting the quiz_system database

USE quiz_system;

-- Add missing columns to users table
ALTER TABLE users ADD COLUMN xp INT DEFAULT 0;
ALTER TABLE users ADD COLUMN level INT DEFAULT 1;
ALTER TABLE users ADD COLUMN streak INT DEFAULT 0;
ALTER TABLE users ADD COLUMN last_login TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Add missing columns to quizzes table
ALTER TABLE quizzes ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active';
ALTER TABLE quizzes ADD COLUMN sections JSON DEFAULT NULL;
ALTER TABLE quizzes ADD COLUMN negative_marking BOOLEAN DEFAULT FALSE;
ALTER TABLE quizzes ADD COLUMN time_limit INT DEFAULT 300;

-- Add missing columns to questions table
ALTER TABLE questions ADD COLUMN difficulty ENUM('easy', 'medium', 'hard') DEFAULT 'medium';
ALTER TABLE questions ADD COLUMN topic VARCHAR(100) DEFAULT '';
ALTER TABLE questions ADD COLUMN marks INT DEFAULT 1;
ALTER TABLE questions ADD COLUMN negative_marks DECIMAL(3,2) DEFAULT 0.00;

-- Add missing columns to results table
ALTER TABLE results ADD COLUMN time_taken INT DEFAULT 0;
ALTER TABLE results ADD COLUMN xp_earned INT DEFAULT 0;

-- Create answers table if it doesn't exist
CREATE TABLE IF NOT EXISTS answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    question_id INT NOT NULL,
    selected_option INT NOT NULL CHECK (selected_option BETWEEN 1 AND 4),
    time_spent INT DEFAULT 0,
    marked_for_review BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (question_id) REFERENCES questions(id)
);

-- Create achievements table if it doesn't exist
CREATE TABLE IF NOT EXISTS achievements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    badge VARCHAR(100) NOT NULL,
    earned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create notifications table if it doesn't exist
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    read_status BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);