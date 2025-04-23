<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .dashboard {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .welcome {
            text-align: center;
            margin-bottom: 20px;
        }
        .logout-form {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="welcome">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
            <p>You have successfully logged in to your account.</p>
        </div>
        <form method="POST" class="logout-form">
            <button type="submit" name="logout">Logout</button>
        </form>
    </div>
</body>
</html> 