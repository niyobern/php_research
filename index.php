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

// Initialize database
setupDatabase();

// Process form submission
$message = '';
$employee = null;

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

// Get recent employees for display
$recentEmployees = getAllEmployees();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Pension Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .header {
            background-color: #0099cc;
            color: white;
            padding: 10px 0;
            text-align: center;
            border: 2px solid #006080;
        }
        .logo {
            max-height: 40px;
        }
        .nav {
            background-color: #f0f0f0;
            padding: 10px 0;
            text-align: center;
            border-left: 2px solid #006080;
            border-right: 2px solid #006080;
        }
        .nav a {
            margin: 0 15px;
            text-decoration: none;
            color: #333;
            font-weight: bold;
        }
        .container {
            display: flex;
            justify-content: space-between;
            padding: 20px;
            border-left: 2px solid #006080;
            border-right: 2px solid #006080;
            background-color: white;
        }
        .sidebar {
            width: 25%;
            background-color: #0099cc;
            color: white;
            padding: 15px;
            border-radius: 5px;
        }
        .content {
            width: 48%;
            border: 1px solid #ccc;
            padding: 15px;
            background-color: #fff;
        }
        .image-section {
            width: 25%;
            text-align: center;
        }
        .image-section img {
            max-width: 100%;
            border-radius: 5px;
        }
        .form-row {
            margin-bottom: 10px;
            display: flex;
        }
        .form-row label {
            width: 150px;
            text-align: right;
            margin-right: 10px;
        }
        .form-row input {
            flex: 1;
        }
        .btn-row {
            margin-top: 20px;
            text-align: center;
        }
        .btn {
            padding: 5px 10px;
            margin: 0 5px;
            cursor: pointer;
        }
        .footer {
            background-color: #f0f0f0;
            padding: 10px 0;
            text-align: center;
            border: 2px solid #006080;
        }
        h2 {
            text-align: center;
            margin-top: 0;
        }
        .programs {
            display: flex;
            margin-top: 20px;
            border: 2px solid #006080;
        }
        .program {
            flex: 1;
            padding: 10px;
            text-align: center;
            color: white;
        }
        .workforce {
            background-color: #0099cc;
        }
        .professional {
            background-color: #000066;
        }
        .insights {
            background-color: #004d4d;
        }
        .student {
            background-color: #009900;
        }
        .citizen {
            background-color: #cc00cc;
        }
        .message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            text-align: center;
            display: <?php echo empty($message) ? 'none' : 'block'; ?>;
        }
        .employee-list {
            margin-top: 20px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAH0AAAAjCAYAAACw4jRAAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAAB3RJTUUH4AYBDTUNYWEbDAAAAY5JREFUeNrt27FLW2EUxuHfTYJgIQhGRQfnDv0X7F9gW7oUsrh0EEQQHDoJjh2cXERsUTdXIVCQdnJSB50EQRDBJXTwBpXA9YMM75Dn2e6553LPO3y83HOLFOI5XuMJbuF1uvHM/sdnBhzpPbzAKhonUPwA7zDHp6gB5/iRSP/5j4S/xhbuRI31N9KrZRfNRHy/hPQefuFTlIBzfEykjxLQKaF7DzKp+KMnBR+jnUifJPM6YxRO06nCKJ0qVJfRwK1r/NcNrOEez7GUu/kQW3iacP9SgWt6QCdXp6f3dKpTnUqn0ql0Kp1Kp9KpdCqdSqfSqXQqnUqn0ql0Kp1K5w3XQSJmU3DTFZXewWcsMBv8+z0YJUKGfFbYxJ0KSH+EJ9jGE9wv2eI9fE9T8FDpFZBexQc9VDqVznl3O9A8uNjLM2Z2uobJsrg5zrCHB6XB4QD38bYiV+oeXuJBIbr9HB/wY1mFX/yL3kh9+zH2cVqR04/0c56HGbyqgvSZiOwNTnDEJf4C+o8+v2U1D0YAAAAASUVORK5CYII=" alt="ICDL Logo" class="logo">
    </div>
    <div class="nav">
        <a href="#">ABOUT US</a>
        <a href="#">PROGRAMMES</a>
        <a href="#">INDIVIDUALS</a>
        <a href="#">STUDENTS</a>
        <a href="#">EMPLOYERS</a>
        <a href="#">PARTNERSHIPS</a>
        <a href="#">TEST CENTRE</a>
    </div>
    <div class="container">
        <div class="sidebar">
            <h3>ICDL Foundation</h3>
            <p>ICDL Foundation is a global social enterprise committed to raising standards of digital competence in the workforce, education and society. ICDL certification is now available in more than 100 countries, across our network of more than 20,000 testing centres, delivering more than 70 million ICDL certification tests to more than 17 million people worldwide.</p>
            <p><a href="#" style="color: white;">Read more</a></p>
        </div>
        <div class="content">
            <h2>EMPLOYEE PENSION MANAGEMENT SYSTEM:</h2>
            <div class="message"><?php echo $message; ?></div>
            <form method="post">
                <div class="form-row">
                    <label for="name">EMPLOYEE NAME:</label>
                    <input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                </div>
                <div class="form-row">
                    <label for="address">EMPLOYEE ADDRESS:</label>
                    <input type="text" id="address" name="address" value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>" required>
                </div>
                <div class="form-row">
                    <label for="salary">MONTHLY SALARY:</label>
                    <input type="number" id="salary" name="salary" step="0.01" value="<?php echo isset($_POST['salary']) ? htmlspecialchars($_POST['salary']) : ''; ?>" required>
                </div>
                <div class="form-row">
                    <label for="employment_period">EMPLOYMENT PERIOD:</label>
                    <input type="number" id="employment_period" name="employment_period" value="<?php echo isset($_POST['employment_period']) ? htmlspecialchars($_POST['employment_period']) : ''; ?>" required>
                </div>
                <div class="form-row">
                    <label for="benefit_percentage">BENEFIT IN %:</label>
                    <input type="number" id="benefit_percentage" name="benefit_percentage" step="0.01" value="<?php echo isset($_POST['benefit_percentage']) ? htmlspecialchars($_POST['benefit_percentage']) : ''; ?>" required>
                </div>
                <div class="btn-row">
                    <button type="submit" name="calculate" class="btn">CLICK TO CALCULATE</button>
                </div>
                <div class="form-row">
                    <label for="total_amount">Total amount:</label>
                    <input type="text" id="total_amount" name="total_amount" value="<?php echo isset($_POST['total_amount']) ? htmlspecialchars($_POST['total_amount']) : ''; ?>" readonly>
                </div>
                <div class="form-row">
                    <label for="monthly_amount">Amount per month:</label>
                    <input type="text" id="monthly_amount" name="monthly_amount" value="<?php echo isset($_POST['monthly_amount']) ? htmlspecialchars($_POST['monthly_amount']) : ''; ?>" readonly>
                </div>
                <div class="btn-row">
                    <button type="submit" name="submit" class="btn">SUBMIT</button>
                    <button type="submit" name="retrieve" class="btn">RETRIEVE</button>
                    <button type="submit" name="delete" class="btn">DELETE</button>
                </div>
            </form>
            
            <!-- Display recent employees -->
            <?php if (!empty($recentEmployees)): ?>
                <div class="employee-list">
                    <h3>Recent Employee Records</h3>
                    <table>
                        <tr>
                            <th>Name</th>
                            <th>Salary</th>
                            <th>Employment Period</th>
                            <th>Benefit %</th>
                            <th>Total Amount</th>
                            <th>Monthly Amount</th>
                        </tr>
                        <?php foreach ($recentEmployees as $emp): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($emp['name']); ?></td>
                            <td>$<?php echo number_format($emp['salary'], 2); ?></td>
                            <td><?php echo $emp['employment_period']; ?> months</td>
                            <td><?php echo $emp['benefit_percentage']; ?>%</td>
                            <td>$<?php echo number_format($emp['total_amount'], 2); ?></td>
                            <td>$<?php echo number_format($emp['monthly_amount'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <div class="image-section">
            <img src="https://placeholder.pics/svg/400/DEDEDE/555555/ICDL%20WORKFORCE" alt="ICDL Training">
        </div>
    </div>
    <h2 style="margin-top: 20px; text-align: center;">ICDL Programmes</h2>
    <div class="programs">
        <div class="program workforce">
            <h3>ICDL<br>Workforce</h3>
            <p>Digital Skills for Employability and productivity</p>
        </div>
        <div class="program professional">
            <h3>ICDL<br>Professional</h3>
            <p>Digital skills for occupational effectiveness</p>
        </div>
        <div class="program insights">
            <h3>ICDL<br>Insights</h3>
            <p>Digital Understanding for business managers</p>
        </div>
        <div class="program student">
            <h3>ICDL<br>Digital Student</h3>
            <p>Digital Skills to design and develop, share and protect</p>
        </div>
        <div class="program citizen">
            <h3>ICDL<br>Digital Citizen</h3>
            <p>Digital Skills to access, engage and build computer confidence</p>
        </div>
    </div>
</body>
</html>