<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config.php';

// Debug POST data
echo "<!-- Debug: POST data: ";
print_r($_POST);
echo " -->";

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];

    echo "<!-- Debug: Attempting login for username: " . htmlspecialchars($username) . " -->";

    // Get admin credentials from database
    $conn = getDBConnection();
    if (!$conn) {
        die("Database connection failed: " . mysqli_connect_error());
    }

    $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE username = ? AND role = 'admin'");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $username);
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    echo "<!-- Debug: Found " . $result->num_rows . " matching admin users -->";

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $admin['password_hash'])) {
            echo "<!-- Debug: Password verified successfully -->";
            
            // Set session variables
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $username;
            
            // Log the login
            $ip = $_SERVER['REMOTE_ADDR'];
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $stmt = $conn->prepare("INSERT INTO login_history (user_id, ip_address, user_agent) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $admin['id'], $ip, $user_agent);
            $stmt->execute();

            // Redirect to admin dashboard
            header("Location: admin_dashboard.php");
            exit();
        } else {
            echo "<!-- Debug: Password verification failed -->";
        }
    } else {
        echo "<!-- Debug: No admin user found with username: " . htmlspecialchars($username) . " -->";
    }

    // If login fails, redirect back with error
    $_SESSION['login_error'] = "Invalid username or password";
    header("Location: adminlogin1.html?error=" . urlencode("Invalid username or password"));
    exit();
}
?> 