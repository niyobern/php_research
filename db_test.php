<?php
// Set error reporting to display all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>MySQL Connection Test</h1>";

// First, let's see what we can find out about our environment
echo "<h2>Network Information</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Hostname: " . gethostname() . "<br>";

// Try to ping the db host
echo "<h2>Host Lookup</h2>";
echo "Looking up 'db' hostname...<br>";
$host_ip = gethostbyname('db');
echo "Resolved to: " . $host_ip . "<br>";
if ($host_ip === 'db') {
    echo "ERROR: Could not resolve hostname 'db'<br>";
}

// Show all database-related PHP extensions
echo "<h2>Database Extensions</h2>";
$loaded_extensions = get_loaded_extensions();
foreach ($loaded_extensions as $extension) {
    if (strpos($extension, 'mysql') !== false || strpos($extension, 'pdo') !== false) {
        echo "Extension loaded: " . $extension . "<br>";
    }
}

// Try to connect with MySQLi
echo "<h2>MySQLi Connection Test</h2>";
$mysqli_host = 'db';  // Service name in docker-compose
$mysqli_user = 'user';
$mysqli_pass = 'password';
$mysqli_db = 'appdb';

try {
    $mysqli = new mysqli($mysqli_host, $mysqli_user, $mysqli_pass, $mysqli_db);
    
    if ($mysqli->connect_error) {
        echo "MySQLi connection failed: " . $mysqli->connect_error . "<br>";
    } else {
        echo "MySQLi connection successful!<br>";
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "MySQLi error: " . $e->getMessage() . "<br>";
}

// Try to connect with PDO
echo "<h2>PDO Connection Test</h2>";
$pdo_host = 'db';  // Service name in docker-compose
$pdo_user = 'user';
$pdo_pass = 'password';
$pdo_db = 'appdb';

try {
    $pdo = new PDO("mysql:host=$pdo_host;dbname=$pdo_db", $pdo_user, $pdo_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "PDO connection successful!<br>";
    $pdo = null;
} catch(PDOException $e) {
    echo "PDO connection failed: " . $e->getMessage() . "<br>";
}

// Try alternative connection options
echo "<h2>Alternative Connection Tests</h2>";

// Try localhost
try {
    $conn = new PDO("mysql:host=localhost;dbname=$pdo_db", $pdo_user, $pdo_pass);
    echo "Connection to 'localhost' successful!<br>";
    $conn = null;
} catch(PDOException $e) {
    echo "Connection to 'localhost' failed: " . $e->getMessage() . "<br>";
}

// Try 127.0.0.1
try {
    $conn = new PDO("mysql:host=127.0.0.1;dbname=$pdo_db", $pdo_user, $pdo_pass);
    echo "Connection to '127.0.0.1' successful!<br>";
    $conn = null;
} catch(PDOException $e) {
    echo "Connection to '127.0.0.1' failed: " . $e->getMessage() . "<br>";
}
?>