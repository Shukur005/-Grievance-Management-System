<?php
session_start();

// Database configuration
$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'project';

// Initialize response array
$response = array(
    'success' => false,
    'message' => '',
    'redirect' => ''
);

try {
    // Create database connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Get and sanitize input
    $user = $conn->real_escape_string($_POST['username']);
    $pass = $_POST['pass'];
    $remember = isset($_POST['chk']) ? true : false;

    // Current timestamp
    $date = date("Y-m-d H:i:s");

    // Prepare and execute the query
    $stmt = $conn->prepare("SELECT * FROM userregistration WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Verify password (assuming it's stored as plain text for now)
        // TODO: Implement proper password hashing
        if ($row['pass'] === $pass) {
            // Set session variables
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['fname'] = $row['fname'];
            $_SESSION['lname'] = $row['lname'];
            $_SESSION['logged_in'] = true;
            $_SESSION['last_activity'] = time();

            // If remember me is checked, set cookie
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + (86400 * 30), "/"); // 30 days
                
                // Store token in database
                $stmt = $conn->prepare("UPDATE userregistration SET remember_token = ? WHERE id = ?");
                $stmt->bind_param("si", $token, $row['id']);
                $stmt->execute();
            }

            // Log the successful login
            $stmt = $conn->prepare("INSERT INTO userloginfo (fname, lname, user, date) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $row['fname'], $row['lname'], $user, $date);
            $stmt->execute();

            // Set success response
            $response['success'] = true;
            $response['redirect'] = 'afterlogin.php';
        } else {
            $response['message'] = 'Invalid password';
        }
    } else {
        $response['message'] = 'User not found';
    }

} catch (Exception $e) {
    $response['message'] = 'An error occurred. Please try again later.';
    error_log("Login error: " . $e->getMessage());
} finally {
    // Close the database connection
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}

// Return response
if ($response['success']) {
    // Redirect on success
    header("Location: " . $response['redirect']);
    exit();
} else {
    // Store error message in session
    $_SESSION['login_error'] = $response['message'];
    header("Location: userlogin3.html");
    exit();
}
?>