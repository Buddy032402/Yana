<?php
include "../db.php";

// Disable foreign key checks temporarily
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// Drop existing table
$conn->query("DROP TABLE IF EXISTS testimonials");

// Create new testimonials table with complete structure
$sql = "CREATE TABLE testimonials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255),
    package_id INT DEFAULT NULL,
    content TEXT NOT NULL,
    rating INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT rating_check CHECK (rating BETWEEN 1 AND 5),
    CONSTRAINT fk_testimonial_package FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE SET NULL
)";

if ($conn->query($sql)) {
    echo "✅ Testimonials table recreated successfully with all columns";
} else {
    echo "❌ Error creating table: " . $conn->error . "\n";
    echo $sql;
}

// Re-enable foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS = 1");
?>