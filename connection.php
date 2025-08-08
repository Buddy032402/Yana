<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "yana_db";

try {
    // Create connection
    $conn = new mysqli($servername, $username, $password);
    
    // Check if database exists, create if not
    if (!$conn->select_db($dbname)) {
        $create_db = "CREATE DATABASE IF NOT EXISTS $dbname";
        if ($conn->query($create_db)) {
            $conn->select_db($dbname);
        } else {
            throw new Exception("Failed to create database: " . $conn->error);
        }
    }

    // Set charset to utf8mb4 for full Unicode support
    $conn->set_charset("utf8mb4");

} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("We're experiencing technical difficulties. Please try again later.");
}
?>