<?php
require_once 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $conn = getDBConnection();
    
    // Check if users table exists
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
    if (mysqli_num_rows($result) == 0) {
        // Create users table
        $sql = "CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if (!mysqli_query($conn, $sql)) {
            throw new Exception("Error creating users table: " . mysqli_error($conn));
        }
        echo "Users table created successfully<br>";
    }

    // Check if login_history table exists
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'login_history'");
    if (mysqli_num_rows($result) == 0) {
        // Create login_history table
        $sql = "CREATE TABLE login_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT,
            login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )";
        
        if (!mysqli_query($conn, $sql)) {
            throw new Exception("Error creating login_history table: " . mysqli_error($conn));
        }
        echo "Login history table created successfully<br>";
    }

    // Check if grievances table exists
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'grievances'");
    if (mysqli_num_rows($result) == 0) {
        // Create grievances table
        $sql = "CREATE TABLE grievances (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            category VARCHAR(50) NOT NULL,
            subcategory VARCHAR(50) NOT NULL,
            description TEXT NOT NULL,
            status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )";
        
        if (!mysqli_query($conn, $sql)) {
            throw new Exception("Error creating grievances table: " . mysqli_error($conn));
        }
        echo "Grievances table created successfully<br>";
    }

    // Check if admin user exists
    $result = mysqli_query($conn, "SELECT id FROM users WHERE username = 'admin' AND role = 'admin'");
    if (mysqli_num_rows($result) == 0) {
        // Create default admin user
        $username = 'admin';
        $password = 'admin123';
        $email = 'admin@gms.com';
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (username, password_hash, email, role) VALUES (?, ?, ?, 'admin')");
        $stmt->bind_param("sss", $username, $password_hash, $email);
        
        if (!$stmt->execute()) {
            throw new Exception("Error creating admin user: " . $stmt->error);
        }
        echo "Default admin user created successfully<br>";
    }

    echo "All required tables and admin user are set up correctly!<br>";
    echo "You can now log in with:<br>";
    echo "Username: admin<br>";
    echo "Password: admin123<br>";

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?> 