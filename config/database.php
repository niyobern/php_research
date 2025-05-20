<?php
$host = 'db';
$dbname = 'appdb';
$username = 'user';
$password = 'password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create Users table
    $sql = "CREATE TABLE IF NOT EXISTS Users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        role ENUM('researcher', 'admin') NOT NULL DEFAULT 'researcher',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);

    // Create Research Projects table
    $sql = "CREATE TABLE IF NOT EXISTS ResearchProjects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        researcher_id INT NOT NULL,
        status ENUM('draft', 'active', 'completed') DEFAULT 'draft',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (researcher_id) REFERENCES Users(id)
    )";
    $pdo->exec($sql);

    // Create Surveys table
    $sql = "CREATE TABLE IF NOT EXISTS Surveys (
        id INT AUTO_INCREMENT PRIMARY KEY,
        project_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        status ENUM('draft', 'active', 'closed') DEFAULT 'draft',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (project_id) REFERENCES ResearchProjects(id)
    )";
    $pdo->exec($sql);

    // Create Questions table
    $sql = "CREATE TABLE IF NOT EXISTS Questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        survey_id INT NOT NULL,
        question_text TEXT NOT NULL,
        question_type ENUM('text', 'multiple_choice', 'single_choice', 'rating') NOT NULL,
        options JSON,
        required BOOLEAN DEFAULT false,
        order_number INT NOT NULL,
        FOREIGN KEY (survey_id) REFERENCES Surveys(id)
    )";
    $pdo->exec($sql);

    // Create Responses table
    $sql = "CREATE TABLE IF NOT EXISTS Responses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        survey_id INT NOT NULL,
        question_id INT NOT NULL,
        response_text TEXT,
        participant_id VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (survey_id) REFERENCES Surveys(id),
        FOREIGN KEY (question_id) REFERENCES Questions(id)
    )";
    $pdo->exec($sql);
    
} catch(PDOException $e) {
    throw new Exception("Database setup failed: " . $e->getMessage());
}
?> 