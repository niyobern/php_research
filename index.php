<?php
// Database connection parameters
$host = 'db'; // This is the service name in docker-compose
$dbname = 'appdb';
$username = 'user';
$password = 'password';

// Create connection
function getConnection() {
    global $host, $dbname, $username, $password;
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        // Set error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
        return null;
    }
}

// Create the employees table if it doesn't exist
function setupDatabase() {
    $conn = getConnection();
    if (!$conn) return false;
    
    try {
        $sql = "CREATE TABLE IF NOT EXISTS employees (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            address VARCHAR(200) NOT NULL,
            salary DECIMAL(10,2) NOT NULL,
            employment_period INT NOT NULL,
            benefit_percentage DECIMAL(5,2) NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            monthly_amount DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $conn->exec($sql);
        return true;
    } catch(PDOException $e) {
        echo "Error setting up database: " . $e->getMessage() . "<br>";
        return false;
    }
    
    $conn = null;
}

// CREATE: Add a new employee
function createEmployee($name, $address, $salary, $employment_period, $benefit_percentage, $total_amount, $monthly_amount) {
    $conn = getConnection();
    if (!$conn) return false;
    
    try {
        $sql = "INSERT INTO employees (name, address, salary, employment_period, benefit_percentage, total_amount, monthly_amount) 
                VALUES (:name, :address, :salary, :employment_period, :benefit_percentage, :total_amount, :monthly_amount)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':salary', $salary);
        $stmt->bindParam(':employment_period', $employment_period);
        $stmt->bindParam(':benefit_percentage', $benefit_percentage);
        $stmt->bindParam(':total_amount', $total_amount);
        $stmt->bindParam(':monthly_amount', $monthly_amount);
        $stmt->execute();
        
        return $conn->lastInsertId();
    } catch(PDOException $e) {
        echo "Error creating employee: " . $e->getMessage() . "<br>";
        return false;
    }
    
    $conn = null;
}

// READ: Get all employees
function getAllEmployees() {
    $conn = getConnection();
    if (!$conn) return [];
    
    try {
        $stmt = $conn->prepare("SELECT * FROM employees ORDER BY id DESC");
        $stmt->execute();
        
        // Set the resulting array to associative
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    } catch(PDOException $e) {
        echo "Error reading employees: " . $e->getMessage() . "<br>";
        return [];
    }
    
    $conn = null;
}

// READ: Get a single employee by ID
function getEmployeeById($id) {
    $conn = getConnection();
    if (!$conn) return null;
    
    try {
        $stmt = $conn->prepare("SELECT * FROM employees WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    } catch(PDOException $e) {
        echo "Error reading employee: " . $e->getMessage() . "<br>";
        return null;
    }
    
    $conn = null;
}

// DELETE: Delete an employee
function deleteEmployee($id) {
    $conn = getConnection();
    if (!$conn) return false;
    
    try {
        $sql = "DELETE FROM employees WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return true;
    } catch(PDOException $e) {
        echo "Error deleting employee: " . $e->getMessage() . "<br>";
        return false;
    }
    
    $conn = null;
}

// Create the students table if it doesn't exist
function setupStudentsDatabase() {
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
    
    $conn = null;
}

// CREATE: Add a new student
function createStudent($student_name, $student_number, $department) {
    $conn = getConnection();
    if (!$conn) return false;
    
    try {
        $sql = "INSERT INTO students (student_name, student_number, department) 
                VALUES (:student_name, :student_number, :department)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':student_name', $student_name);
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
    
    $conn = null;
}

// READ: Get all students
function getAllStudents() {
    $conn = getConnection();
    if (!$conn) return [];
    
    try {
        $stmt = $conn->prepare("SELECT * FROM students ORDER BY id DESC");
        $stmt->execute();
        
        // Set the resulting array to associative
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    } catch(PDOException $e) {
        echo "Error reading students: " . $e->getMessage() . "<br>";
        return [];
    }
    
    $conn = null;
}

// READ: Get student by number
function getStudentByNumber($student_number) {
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
    
    $conn = null;
}

// DELETE: Delete a student
function deleteStudent($student_number) {
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
    
    $conn = null;
}

// Initialize database
setupDatabase();
setupStudentsDatabase();

// Process form submission
$message = '';
$employee = null;
$exchanged_amount = null;

// Calculate pension amounts
if (isset($_POST['calculate'])) {
    // Get values from form
    $salary = isset($_POST['salary']) ? floatval($_POST['salary']) : 0;
    $employment_period = isset($_POST['employment_period']) ? intval($_POST['employment_period']) : 0;
    $benefit_percentage = isset($_POST['benefit_percentage']) ? floatval($_POST['benefit_percentage']) : 0;
    
    // Calculate total and monthly amounts
    $total_amount = $salary * ($employment_period / 12) * ($benefit_percentage / 100);
    $monthly_amount = $total_amount / 12;
    
    // Store calculated values to display in form
    $_POST['total_amount'] = $total_amount;
    $_POST['monthly_amount'] = $monthly_amount;
}

// Handle submit (save to database)  
if (isset($_POST['submit'])) {
    $name = $_POST['name'] ?? '';
    $address = $_POST['address'] ?? '';
    $salary = floatval($_POST['salary'] ?? 0);
    $employment_period = intval($_POST['employment_period'] ?? 0);
    $benefit_percentage = floatval($_POST['benefit_percentage'] ?? 0);
    $total_amount = floatval($_POST['total_amount'] ?? 0);
    $monthly_amount = floatval($_POST['monthly_amount'] ?? 0);
    
    if (createEmployee($name, $address, $salary, $employment_period, $benefit_percentage, $total_amount, $monthly_amount)) {
        $message = "Employee pension record saved successfully!";
        // Clear form data after successful submission
        $_POST = array();
    } else {
        $message = "Error saving employee pension record!";
    }
}

// Handle retrieve (get from database)
if (isset($_POST['retrieve'])) {
    $name = $_POST['name'] ?? '';
    if (!empty($name)) {
        $conn = getConnection();
        if ($conn) {
            try {
                $stmt = $conn->prepare("SELECT * FROM employees WHERE name LIKE :name ORDER BY id DESC LIMIT 1");
                $searchName = "%$name%";
                $stmt->bindParam(':name', $searchName);
                $stmt->execute();
                $employee = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($employee) {
                    // Fill form with retrieved data
                    $_POST['name'] = $employee['name'];
                    $_POST['address'] = $employee['address'];
                    $_POST['salary'] = $employee['salary'];
                    $_POST['employment_period'] = $employee['employment_period'];
                    $_POST['benefit_percentage'] = $employee['benefit_percentage'];
                    $_POST['total_amount'] = $employee['total_amount'];
                    $_POST['monthly_amount'] = $employee['monthly_amount'];
                    $message = "Employee record retrieved!";
                } else {
                    $message = "No employee found with that name!";
                }
            } catch(PDOException $e) {
                $message = "Error retrieving employee: " . $e->getMessage();
            }
        }
    } else {
        $message = "Please enter a name to retrieve!";
    }
}

// Handle delete
if (isset($_POST['delete'])) {
    $name = $_POST['name'] ?? '';
    if (!empty($name)) {
        $conn = getConnection();
        if ($conn) {
            try {
                $stmt = $conn->prepare("SELECT id FROM employees WHERE name LIKE :name ORDER BY id DESC LIMIT 1");
                $searchName = "%$name%";
                $stmt->bindParam(':name', $searchName);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result && deleteEmployee($result['id'])) {
                    $message = "Employee record deleted successfully!";
                    // Clear form after deletion
                    $_POST = array();
                } else {
                    $message = "No employee found with that name!";
                }
            } catch(PDOException $e) {
                $message = "Error deleting employee: " . $e->getMessage();
            }
        }
    } else {
        $message = "Please enter a name to delete!";
    }
}

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
        if (createStudent($student_name, $student_number, $department)) {
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
        $student = getStudentByNumber($student_number);
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
        if (deleteStudent($student_number)) {
            $message = "Student deleted successfully!";
            $_POST = array();
        } else {
            $message = "No student found with that number!";
        }
    } else {
        $message = "Please enter a student number!";
    }
}

// Get recent employees for display
$recentEmployees = getAllEmployees();

// Get all students for display
$students = getAllStudents();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICDL Payment System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }
        .header img {
            width: 100%;
            height: auto;
        }
        .main-content {
            display: flex;
            margin: 20px;
            gap: 20px;
        }
        .left-panel {
            flex: 1;
        }
        .left-panel img {
            width: 100%;
            height: auto;
        }
        .center-panel {
            flex: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .right-panel {
            flex: 1;
        }
        .right-panel img {
            width: 100%;
            height: auto;
        }
        .seal {
            width: 150px;
            margin-bottom: 20px;
        }
        .form-container {
            width: 100%;
            border: 1px solid #ccc;
            padding: 20px;
            border-radius: 5px;
        }
        .form-title {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .form-group {
            margin-bottom: 10px;
        }
        .form-group label {
            display: inline-block;
            width: 150px;
            margin-right: 10px;
        }
        .form-group input {
            width: 200px;
            padding: 5px;
        }
        .button-group {
            text-align: center;
            margin-top: 15px;
        }
        .button-group button {
            margin: 0 5px;
            padding: 5px 15px;
        }
        .programs {
            display: flex;
            margin-top: 20px;
            width: 100%;
        }
        .program {
            flex: 1;
            padding: 10px;
            color: white;
            text-align: center;
        }
        .workforce { background-color: #00A1E1; }
        .professional { background-color: #000066; }
        .insights { background-color: #004d4d; }
        .digital-student { background-color: #009900; }
        .digital-citizen { background-color: #cc00cc; }
        .message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 3px;
            background-color: #f8d7da;
            color: #721c24;
            display: <?php echo empty($message) ? 'none' : 'block'; ?>;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="images/header.jpeg" alt="ICDL Header">
    </div>

    <div class="main-content">
        <div class="left-panel">
            <img src="images/left.jpeg" alt="ICDL Information">
        </div>

        <div class="center-panel">
            <img src="images/seal.jpeg" alt="ISTE Seal" class="seal">
            
            <div class="form-container">
                <div class="form-title">All About ICDL Payment:</div>
                
                <?php if (!empty($message)): ?>
                    <div class="message"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <form method="post">
                    <div class="form-group">
                        <label>STUDENT-NAME:</label>
                        <input type="text" name="student_name" value="<?php echo isset($_POST['student_name']) ? htmlspecialchars($_POST['student_name']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>STUDENT-NUMBER:</label>
                        <input type="text" name="student_number" value="<?php echo isset($_POST['student_number']) ? htmlspecialchars($_POST['student_number']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>DEPARTMENT:</label>
                        <input type="text" name="department" value="<?php echo isset($_POST['department']) ? htmlspecialchars($_POST['department']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>ENTER ICDL MONEY/$:</label>
                        <input type="number" step="0.01" name="amount" value="<?php echo isset($_POST['amount']) ? htmlspecialchars($_POST['amount']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>EXCHANGE RATE:</label>
                        <input type="number" step="0.01" name="rate" value="<?php echo isset($_POST['rate']) ? htmlspecialchars($_POST['rate']) : ''; ?>">
                    </div>

                    <div class="button-group">
                        <button type="submit" name="exchange">Exchange</button>
                        <button type="submit" name="display">Display</button>
                        <button type="submit" name="delete">Done</button>
                    </div>

                    <?php if ($exchanged_amount !== null): ?>
                        <div class="form-group">
                            <label>Exchanged Amount:</label>
                            <input type="text" value="<?php echo number_format($exchanged_amount, 2); ?>" readonly>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="right-panel">
            <img src="images/right.jpeg" alt="ICDL Training">
        </div>
    </div>

    <h2 style="text-align: center; margin: 20px 0;">ICDL Programmes</h2>
    <div class="programs">
        <div class="program workforce">
            <h3>ICDL<br>Workforce</h3>
            <p>Digital Skills for Employability and Productivity</p>
        </div>
        <div class="program professional">
            <h3>ICDL<br>Professional</h3>
            <p>Digital Skills for Occupational Effectiveness</p>
        </div>
        <div class="program insights">
            <h3>ICDL<br>Insights</h3>
            <p>Digital Understanding for Business</p>
        </div>
        <div class="program digital-student">
            <h3>ICDL<br>Digital Student</h3>
            <p>Digital Skills to Design and Develop</p>
        </div>
        <div class="program digital-citizen">
            <h3>ICDL<br>Digital Citizen</h3>
            <p>Digital Skills to Access and Engage</p>
        </div>
    </div>
</body>
</html>