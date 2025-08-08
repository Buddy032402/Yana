<?php
include "../db.php";

// Create top_client_picks table
$sql1 = "CREATE TABLE IF NOT EXISTS top_client_picks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    destination_id INT NOT NULL,
    featured_order INT NOT NULL DEFAULT 0,
    description TEXT,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE
)";

// Create client_testimonials table
$sql2 = "CREATE TABLE IF NOT EXISTS client_testimonials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_name VARCHAR(255) NOT NULL,
    client_image VARCHAR(255),
    testimonial TEXT NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// Create featured_destinations table
$sql3 = "CREATE TABLE IF NOT EXISTS featured_destinations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    destination_id INT NOT NULL,
    featured_order INT NOT NULL DEFAULT 0,
    highlight_text TEXT,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE
)";

if ($conn->query($sql1) && $conn->query($sql2) && $conn->query($sql3)) {
    echo "✅ Tables created successfully!";
} else {
    echo "❌ Error creating tables: " . $conn->error;
}
?>