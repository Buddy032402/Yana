<?php
include "../db.php";

// Create admin account if it doesn't exist
$email = 'admin@admin.com';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$name = 'Administrator';
$role = 'admin';

$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND role = 'admin'");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // Updated query to match the table structure
    $stmt = $conn->prepare("INSERT INTO users (email, password, name, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $email, $password, $name, $role);
    
    if ($stmt->execute()) {
        echo "Admin account created successfully!<br>";
        echo "Email: admin@admin.com<br>";
        echo "Password: admin123";
    } else {
        echo "Error creating admin account: " . $conn->error;
    }
} else {
    echo "Admin account already exists!";
}
?>