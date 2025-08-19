<?php
require_once 'config.php';

echo "<h2>Database Verification</h2>";

try {
    // Test database connection
    $conn = getDBConnection();
    echo "✓ Database connection successful<br>";
    
    // Check required tables
    $required_tables = [
        'users',
        'categories',
        'subcategories',
        'grievances',
        'attachments',
        'grievance_updates',
        'login_history'
    ];
    
    echo "<h3>Checking Tables:</h3>";
    foreach ($required_tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "✓ Table '$table' exists<br>";
            
            // Check table structure
            $result = $conn->query("DESCRIBE $table");
            echo "<ul>";
            while ($row = $result->fetch_assoc()) {
                echo "<li>{$row['Field']} - {$row['Type']}</li>";
            }
            echo "</ul>";
        } else {
            echo "✗ Table '$table' is missing<br>";
        }
    }
    
    // Check for default admin user
    $result = $conn->query("SELECT * FROM users WHERE username = 'admin' AND role = 'admin'");
    if ($result->num_rows > 0) {
        echo "✓ Default admin user exists<br>";
    } else {
        echo "✗ Default admin user is missing<br>";
    }
    
    // Check for default categories
    $result = $conn->query("SELECT COUNT(*) as count FROM categories");
    $row = $result->fetch_assoc();
    if ($row['count'] > 0) {
        echo "✓ Default categories exist ({$row['count']} categories)<br>";
    } else {
        echo "✗ Default categories are missing<br>";
    }
    
    // Check for subcategories
    $result = $conn->query("SELECT COUNT(*) as count FROM subcategories");
    $row = $result->fetch_assoc();
    if ($row['count'] > 0) {
        echo "✓ Subcategories exist ({$row['count']} subcategories)<br>";
    } else {
        echo "✗ Subcategories are missing<br>";
    }
    
    // Check file permissions
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
    
    // Check PHP version and extensions
    echo "<h3>PHP Environment:</h3>";
    echo "PHP Version: " . phpversion() . "<br>";
    echo "MySQL Extension: " . (extension_loaded('mysqli') ? '✓ Loaded' : '✗ Not loaded') . "<br>";
    echo "File Upload: " . (ini_get('file_uploads') ? '✓ Enabled' : '✗ Disabled') . "<br>";
    echo "Max Upload Size: " . ini_get('upload_max_filesize') . "<br>";
    echo "Max Post Size: " . ini_get('post_max_size') . "<br>";
    
    echo "<br>Verification completed!";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 