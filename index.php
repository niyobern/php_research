<?php
session_start();
require_once 'config/database.php';

// Basic routing
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Authentication check
function isAuthenticated() {
    return isset($_SESSION['user_id']);
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
    default:
        include 'views/404.php';
}

// Footer
include 'views/footer.php';
?>