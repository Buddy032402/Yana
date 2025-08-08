<?php
include "../db.php";

// Disable foreign key checks temporarily
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// Truncate all tables in the correct order
$tables = [
    'payments',
    'testimonials',
    'bookings',
    'packages',
    'destinations',
    'inquiries',
    'users'
];

foreach ($tables as $table) {
    $conn->query("TRUNCATE TABLE $table");
}

// Re-enable foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

// Create admin user
$email = 'admin@admin.com';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$name = 'Administrator';
$role = 'admin';

$stmt = $conn->prepare("INSERT INTO users (email, password, name, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $email, $password, $name, $role);
$stmt->execute();

echo "All tables have been cleaned and admin account has been created.<br>";
echo "Admin email: admin@admin.com<br>";
echo "Admin password: admin123";
?>