<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'project';

// Test connection
echo "<h2>Database Connection Test</h2>";
try {
    $conn = new mysqli($servername, $username, $password);
    echo "✓ MySQL Connection successful<br>";
    
    // Check if database exists
    $result = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
    if ($result->num_rows > 0) {
        echo "✓ Database '$dbname' exists<br>";
        
        // Select the database
        $conn->select_db($dbname);
        
        // Check required tables
        $required_tables = [
            'complaints',
            'completedcomp',
            'inprogresscomp',
            'userloginfo',
            'userregistration'
        ];
        
        echo "<h3>Checking Tables:</h3>";
        foreach ($required_tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result->num_rows > 0) {
                echo "✓ Table '$table' exists<br>";
            } else {
                echo "✗ Table '$table' is missing<br>";
            }
        }
    } else {
        echo "✗ Database '$dbname' does not exist<br>";
    }
    
} catch (Exception $e) {
    echo "✗ Connection failed: " . $e->getMessage();
}
?> 