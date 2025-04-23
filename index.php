<?php
session_start();
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        $stmt = $pdo->prepare("SELECT * FROM Credentials WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: dashboard.php");
            exit();
        } else {
            $login_error = "Invalid username or password";
        }
    } elseif (isset($_POST['signup'])) {
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $address = $_POST['address'];
        $telephone = $_POST['telephone'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        try {
            $stmt = $pdo->prepare("INSERT INTO Credentials (first_name, last_name, address, telephone, username, password) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$first_name, $last_name, $address, $telephone, $username, $password]);
            $success_message = "Registration successful! Please login.";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $register_error = "Username already exists";
            } else {
                $register_error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Registration System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <!-- Login Form -->
        <div class="form-container">
            <div class="form-title">Login Form</div>
            <?php if (isset($login_error)): ?>
                <div class="error"><?php echo $login_error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="username">User-Name:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="button-group">
                    <button type="submit" name="login">Login</button>
                    <button type="reset">Cancel</button>
                </div>
            </form>
        </div>

        <!-- Registration Form -->
        <div class="form-container">
            <div class="form-title">Registration Form</div>
            <?php if (isset($register_error)): ?>
                <div class="error"><?php echo $register_error; ?></div>
            <?php endif; ?>
            <?php if (isset($success_message)): ?>
                <div class="success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="first_name">FIRST-NAME:</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">LAST-NAME:</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
                <div class="form-group">
                    <label for="address">ADDRESS:</label>
                    <input type="text" id="address" name="address" required>
                </div>
                <div class="form-group">
                    <label for="telephone">TELEPHONE:</label>
                    <input type="tel" id="telephone" name="telephone" required>
                </div>
                <div class="form-group">
                    <label for="reg_username">USER-NAME:</label>
                    <input type="text" id="reg_username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="reg_password">PASSWORD:</label>
                    <input type="password" id="reg_password" name="password" required>
                </div>
                <div class="button-group">
                    <button type="submit" name="signup">Signup</button>
                    <button type="reset">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>