<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'project';

try {
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Add admin user
    $admin_username = "admin";
    $admin_password = "admin123"; // You should change this in production
    $date = date("F j, Y, g:i a");
    
    $sql = "INSERT INTO userregistration (username, fname, lname, email, phone, gender, pass, date) 
            VALUES ('$admin_username', 'Admin', 'User', 'admin@example.com', '1234567890', 'Other', '$admin_password', '$date')";
    
    if ($conn->query($sql) === TRUE) {
        echo "Admin user created successfully!<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
    } else {
        echo "Error creating admin user: " . $conn->error;
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 