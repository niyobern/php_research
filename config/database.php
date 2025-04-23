<?php
$host = 'localhost';
$dbname = 'AccountDB';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    $pdo->exec($sql);
    
    // Select the database
    $pdo->exec("USE $dbname");
    
    // Create table if not exists
    $sql = "CREATE TABLE IF NOT EXISTS Credentials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        address TEXT NOT NULL,
        telephone VARCHAR(20) NOT NULL,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?> 