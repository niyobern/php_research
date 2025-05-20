<?php
session_start();
require_once 'config/database.php';

// Flash message helpers
function set_flash($msg, $type = 'success') {
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}
function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Handle CSV export before any output
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
if ($page === 'export_csv') {
    include 'views/export_csv.php';
    exit;
}

// Basic routing
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Handle logout before any output
if ($page === 'logout') {
    session_unset();
    session_destroy();
    header('Location: index.php?page=login');
    exit();
}

// Authentication check
function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

// Handle login form submission
if ($page === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM Users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header('Location: index.php?page=home');
            exit();
        } else {
            $login_error = "Invalid username or password";
        }
    } catch (PDOException $e) {
        $login_error = "Login failed. Please try again.";
    }
}

// Handle registration form submission
if ($page === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    
    $errors = [];
    
    // Validation
    if (empty($username)) $errors[] = "Username is required";
    if (empty($email)) $errors[] = "Email is required";
    if (empty($password)) $errors[] = "Password is required";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match";
    if (empty($first_name)) $errors[] = "First name is required";
    if (empty($last_name)) $errors[] = "Last name is required";
    
    if (empty($errors)) {
        try {
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = "Username or email already exists";
            } else {
                // Create new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO Users (username, email, password, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password, $first_name, $last_name]);
                
                $_SESSION['success'] = "Registration successful! Please login.";
                header('Location: index.php?page=login');
                exit();
            }
        } catch (PDOException $e) {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}

// Redirect to login if not authenticated
if (!isAuthenticated() && $page != 'login' && $page != 'register') {
    header('Location: index.php?page=login');
    exit();
}

// Header
include 'views/header.php';

// Main content routing
switch ($page) {
    case 'home':
        include 'views/home.php';
        break;
    case 'login':
        include 'views/login.php';
        break;
    case 'register':
        include 'views/register.php';
        break;
    case 'projects':
        include 'views/projects.php';
        break;
    case 'surveys':
        include 'views/surveys.php';
        break;
    case 'create_survey':
        include 'views/create_survey.php';
        break;
    case 'view_responses':
        include 'views/view_responses.php';
        break;
    case 'public_survey':
        include 'views/public_survey.php';
        break;
    default:
        include 'views/404.php';
}

// Footer
include 'views/footer.php';
?>