-- Create the database (if not already created)
CREATE DATABASE IF NOT EXISTS student_system;
USE student_system;

-- Table for storing program information
CREATE TABLE IF NOT EXISTS programs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    program_code VARCHAR(50) NOT NULL UNIQUE,
    program_name VARCHAR(255) NOT NULL
);

-- Table for storing student records
CREATE TABLE IF NOT EXISTS students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    parent_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    student_id VARCHAR(50) NOT NULL UNIQUE,
    uid VARCHAR(50) NOT NULL UNIQUE,
    picture_url VARCHAR(255) NOT NULL,
    program_id INT NOT NULL,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
);

-- Table for logging student entries
CREATE TABLE IF NOT EXISTS logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_uid VARCHAR(50)NULL,
    entry_time DATETIME,
	exit_time DATETIME,
    student_name VARCHAR(100) NOT NULL,
    student_id VARCHAR(50) NOT NULL,
    picture_url VARCHAR(255) NOT NULL,
    program_id INT NOT NULL,
    FOREIGN KEY (student_uid) REFERENCES students(uid) ON DELETE SET NULL,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
);

-- Table for logging student entries
CREATE TABLE IF NOT EXISTS history_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_uid VARCHAR(50)NULL,
    entry_time DATETIME,
	exit_time DATETIME,
    student_name VARCHAR(100) NOT NULL,
    student_id VARCHAR(50) NOT NULL,
    picture_url VARCHAR(255) NOT NULL,
    program_id INT NOT NULL,
    FOREIGN KEY (student_uid) REFERENCES students(uid) ON DELETE SET NULL,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
);

-- Table for logging system entries
CREATE TABLE IF NOT EXISTS system_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_uid VARCHAR(50)NULL,
    activity VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for storing admin users
CREATE TABLE IF NOT EXISTS admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    picture_url VARCHAR(255) NOT NULL,
    FirstName VARCHAR(100) NOT NULL,
    LastName VARCHAR(100) NOT NULL
);

-- Insert example programs
INSERT INTO programs (program_code, program_name) VALUES
    ('BAPS', 'BACHELOR OF ARTS IN POLITICAL SCIENCE'),
    ('BSA', 'BACHELOR OF SCIENCE IN AGRICULTURE'),
    ('BAEL', 'BACHELOR OF ARTS IN ENGLISH LANGUAGE'),
    ('BSAIS', 'BACHELOR OF SCIENCE IN ACCOUNTING INFORMATION SYSTEM'),
    ('BSIS', 'BACHELOR OF SCIENCE IN INFORMATION SYSTEMS'),
    ('BSCRIM', 'BACHELOR OF SCIENCE IN CRIMINOLOGY'),
    ('BECE', 'BACHELOR IN EARLY CHILDHOOD EDUCATION');

-- Insert example admin user with corrected hashed password
INSERT INTO admins (username, password, picture_url, FirstName, LastName) VALUES 
    ('admin', '$2y$10$AiCR/L3Hi0ujmhYNwFfi1u8XcVn7u.4ekanE.yxNMoFYuBp78Afra', 'admin.jpg', 'TPC', 'Admin');
