CREATE DATABASE IF NOT EXISTS student_dashboard;
USE student_dashboard;

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    branch VARCHAR(100) NOT NULL,
    cgpa DECIMAL(3,2) NOT NULL,
    status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
    photo VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO students (name, email, branch, cgpa, status, photo)
VALUES
    ('Aarav Sharma', 'aarav@example.com', 'CSE', 8.90, 'Active', NULL),
    ('Meera Patel', 'meera@example.com', 'ECE', 8.40, 'Active', NULL),
    ('Rohan Verma', 'rohan@example.com', 'ME', 7.80, 'Inactive', NULL),
    ('Nisha Rao', 'nisha@example.com', 'CSE', 9.10, 'Active', NULL),
    ('Karan Singh', 'karan@example.com', 'IT', 7.60, 'Active', NULL);
