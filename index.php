<?php
require_once 'config/database.php';
require_once 'models/Student.php';

// Initialize database
Student::setupDatabase();

// Process form submission
$message = '';
$exchanged_amount = null;

// Handle currency exchange calculation
if (isset($_POST['exchange'])) {
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    $rate = isset($_POST['rate']) ? floatval($_POST['rate']) : 0;
    
    if ($amount > 0 && $rate > 0) {
        $exchanged_amount = $amount * $rate;
    }
}

// Handle student registration
if (isset($_POST['register'])) {
    $student_name = $_POST['student_name'] ?? '';
    $student_number = $_POST['student_number'] ?? '';
    $department = $_POST['department'] ?? '';
    
    if (!empty($student_name) && !empty($student_number) && !empty($department)) {
        if (Student::create($student_name, $student_number, $department)) {
            $message = "Student registered successfully!";
            $_POST = array();
        }
    } else {
        $message = "Please fill in all fields!";
    }
}

// Handle student display
if (isset($_POST['display'])) {
    $student_number = $_POST['student_number'] ?? '';
    if (!empty($student_number)) {
        $student = Student::getByNumber($student_number);
        if (!$student) {
            $message = "No student found with that number!";
        }
    } else {
        $message = "Please enter a student number!";
    }
}

// Handle student deletion
if (isset($_POST['delete'])) {
    $student_number = $_POST['student_number'] ?? '';
    if (!empty($student_number)) {
        if (Student::delete($student_number)) {
            $message = "Student deleted successfully!";
            $_POST = array();
        } else {
            $message = "No student found with that number!";
        }
    } else {
        $message = "Please enter a student number!";
    }
}

// Include the view
$content = 'views/form.php';
include 'views/layout.php';