<?php
require_once __DIR__ . '/../config/database.php';

class Student {
    // Create the students table if it doesn't exist
    public static function setupDatabase() {
        $conn = getConnection();
        if (!$conn) return false;
        
        try {
            $sql = "CREATE TABLE IF NOT EXISTS students (
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                student_name VARCHAR(100) NOT NULL,
                student_number VARCHAR(50) NOT NULL UNIQUE,
                department VARCHAR(100) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            
            $conn->exec($sql);
            return true;
        } catch(PDOException $e) {
            echo "Error setting up database: " . $e->getMessage() . "<br>";
            return false;
        }
    }

    // Create a new student
    public static function create($student_name, $student_number, $department) {
        $conn = getConnection();
        if (!$conn) return false;
        
        try {
            $sql = "INSERT INTO students (student_name, student_number, department) 
                    VALUES (:student_name, :student_number, :department)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':student_name', $student_name);
            $stmt->bindParam(':student_number', $student_number);
            $stmt->bindParam(':student_number', $student_number);
            $stmt->bindParam(':department', $department);
            $stmt->execute();
            
            return $conn->lastInsertId();
        } catch(PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo "Student number already exists!";
            } else {
                echo "Error creating student: " . $e->getMessage();
            }
            return false;
        }
    }

    // Get student by number
    public static function getByNumber($student_number) {
        $conn = getConnection();
        if (!$conn) return null;
        
        try {
            $stmt = $conn->prepare("SELECT * FROM students WHERE student_number = :student_number");
            $stmt->bindParam(':student_number', $student_number);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error retrieving student: " . $e->getMessage();
            return null;
        }
    }

    // Delete student
    public static function delete($student_number) {
        $conn = getConnection();
        if (!$conn) return false;
        
        try {
            $sql = "DELETE FROM students WHERE student_number = :student_number";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':student_number', $student_number);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        } catch(PDOException $e) {
            echo "Error deleting student: " . $e->getMessage();
            return false;
        }
    }
} 