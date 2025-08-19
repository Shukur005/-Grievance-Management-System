<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Debug session information
echo "<!-- Debug: Session contents: ";
print_r($_SESSION);
echo " -->";

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo "<!-- Debug: Admin not logged in -->";
    header("Location: adminlogin1.html");
    exit();
}

// Database connection
require_once 'config.php';
try {
    $conn = getDBConnection();
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// Get statistics
$stats = array(
    'pending' => 0,
    'in_progress' => 0,
    'completed' => 0,
    'total' => 0
);

// Get pending grievances count
$query = "SELECT COUNT(*) as count FROM grievances WHERE status = 'pending'";
$result = mysqli_query($conn, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['pending'] = $row['count'];
    $stats['total'] += $row['count'];
} else {
    echo "<!-- Debug: Error in pending query: " . mysqli_error($conn) . " -->";
}

// Get in-progress grievances count
$query = "SELECT COUNT(*) as count FROM grievances WHERE status = 'in_progress'";
$result = mysqli_query($conn, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['in_progress'] = $row['count'];
    $stats['total'] += $row['count'];
} else {
    echo "<!-- Debug: Error in in-progress query: " . mysqli_error($conn) . " -->";
}

// Get completed grievances count
$query = "SELECT COUNT(*) as count FROM grievances WHERE status = 'completed'";
$result = mysqli_query($conn, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['completed'] = $row['count'];
    $stats['total'] += $row['count'];
} else {
    echo "<!-- Debug: Error in completed query: " . mysqli_error($conn) . " -->";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - GMS</title>
    <link rel="stylesheet" href="background.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dashboard-container {
            padding: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card i {
            font-size: 2em;
            margin-bottom: 10px;
            color: #4776E6;
        }
        .stat-card h3 {
            margin: 10px 0;
            color: #2c3e50;
        }
        .stat-card p {
            font-size: 1.5em;
            color: #4776E6;
            font-weight: bold;
        }
        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .action-btn {
            padding: 15px;
            border: none;
            border-radius: 8px;
            background: #4776E6;
            color: white;
            font-size: 1em;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
        }
        .action-btn:hover {
            background: #8E54E9;
            transform: translateY(-2px);
        }
        .action-btn i {
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <nav>
            <div class="logo">
                <h1><i class="fas fa-user-shield"></i> Admin Dashboard</h1>
                <p>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></p>
            </div>
            <div class="buttons">
                <a href="logout.php" class="btn logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>

        <main class="dashboard-container">
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <h3>Pending Grievances</h3>
                    <p><?php echo $stats['pending']; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-spinner"></i>
                    <h3>In Progress</h3>
                    <p><?php echo $stats['in_progress']; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-check-circle"></i>
                    <h3>Completed</h3>
                    <p><?php echo $stats['completed']; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-chart-bar"></i>
                    <h3>Total Grievances</h3>
                    <p><?php echo $stats['total']; ?></p>
                </div>
            </div>

            <div class="action-buttons">
                <a href="manage_grievances.php" class="action-btn">
                    <i class="fas fa-tasks"></i> Manage Grievances
                </a>
                <a href="manage_users.php" class="action-btn">
                    <i class="fas fa-users"></i> Manage Users
                </a>
                <a href="reports.php" class="action-btn">
                    <i class="fas fa-chart-line"></i> Reports
                </a>
                <a href="settings.php" class="action-btn">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </div>
        </main>
    </div>
</body>
</html> 