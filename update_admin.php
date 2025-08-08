<?php
include "../db.php";

$email = 'admin';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$name = 'Administrator';
$role = 'admin';

// First try to update existing admin
$stmt = $conn->prepare("UPDATE users SET email = ?, password = ?, name = ? WHERE role = 'admin'");
$stmt->bind_param("sss", $email, $password, $name);
$stmt->execute();

// If no admin exists, create one
if ($stmt->affected_rows == 0) {
    $stmt = $conn->prepare("INSERT INTO users (email, password, name, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $email, $password, $name, $role);
    $stmt->execute();
}

if ($stmt->affected_rows > 0) {
    echo "Admin account created/updated successfully!";
} else {
    echo "No changes were made.";
}
?>