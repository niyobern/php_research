<?php
$host = 'db';
$dbname = 'AccountDB';
$username = 'user';
$password = 'password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
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
    throw new Exception("Database setup failed: " . $e->getMessage());
}
?> 