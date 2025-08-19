<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'project';

try {
    // Create connection
    $conn = new mysqli($servername, $username, $password);
    
    // Create database if not exists
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if ($conn->query($sql) === TRUE) {
        echo "Database created successfully or already exists<br>";
    }
    
    // Select the database
    $conn->select_db($dbname);
    
    // Create tables
    $tables = [
        "CREATE TABLE IF NOT EXISTS complaints (
            id int(11) NOT NULL AUTO_INCREMENT,
            user text NOT NULL,
            category text NOT NULL,
            subcategory text NOT NULL,
            nature text NOT NULL,
            comp text NOT NULL,
            file text NOT NULL,
            pending int(11) DEFAULT NULL,
            date text NOT NULL,
            PRIMARY KEY (id)
        )",
        
        "CREATE TABLE IF NOT EXISTS completedcomp (
            id int(11) NOT NULL AUTO_INCREMENT,
            user text NOT NULL,
            remark text NOT NULL,
            compnum text NOT NULL,
            PRIMARY KEY (id)
        )",
        
        "CREATE TABLE IF NOT EXISTS inprogresscomp (
            id int(11) NOT NULL AUTO_INCREMENT,
            user text NOT NULL,
            remarks text NOT NULL,
            compnum text NOT NULL,
            PRIMARY KEY (id)
        )",
        
        "CREATE TABLE IF NOT EXISTS userloginfo (
            id int(11) NOT NULL AUTO_INCREMENT,
            fname text NOT NULL,
            lname text NOT NULL,
            user text NOT NULL,
            date text NOT NULL,
            PRIMARY KEY (id)
        )",
        
        "CREATE TABLE IF NOT EXISTS userregistration (
            id int(11) NOT NULL AUTO_INCREMENT,
            username text NOT NULL,
            fname text NOT NULL,
            lname text NOT NULL,
            email text NOT NULL,
            phone text NOT NULL,
            gender text NOT NULL,
            pass text NOT NULL,
            date text NOT NULL,
            PRIMARY KEY (id)
        )"
    ];
    
    foreach ($tables as $sql) {
        if ($conn->query($sql) === TRUE) {
            echo "Table created successfully<br>";
        } else {
            echo "Error creating table: " . $conn->error . "<br>";
        }
    }
    
    echo "<br>Setup completed successfully!";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 