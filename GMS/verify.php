<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>PHP Verification</h2>";
echo "PHP is working!<br>";

// Check if MySQL is running
$mysql_running = @fsockopen('localhost', 3306);
if ($mysql_running) {
    echo "MySQL is running on port 3306<br>";
    fclose($mysql_running);
} else {
    echo "MySQL is NOT running!<br>";
}

// Test database connection
$servername = 'localhost';
$username = 'root';
$password = '';

try {
    $conn = new mysqli($servername, $username, $password);
    echo "Database connection successful!<br>";
    
    // Check if our database exists
    $result = $conn->query("SHOW DATABASES LIKE 'project'");
    if ($result->num_rows > 0) {
        echo "Database 'project' exists!<br>";
    } else {
        echo "Database 'project' does not exist!<br>";
    }
    
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage() . "<br>";
}

// Check file permissions
echo "<h3>File Check:</h3>";
$files = [
    'index.html',
    'adminlogin1.html',
    'userlogin1.html',
    'userreg1.html',
    'setup_db.php',
    'check_db.php',
    'create_admin.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✓ $file exists<br>";
    } else {
        echo "✗ $file is missing<br>";
    }
}

// Check directory permissions
$upload_dir = 'uploads';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
    echo "Created uploads directory<br>";
}
if (is_writable($upload_dir)) {
    echo "✓ Uploads directory is writable<br>";
} else {
    echo "✗ Uploads directory is NOT writable<br>";
}
?> 