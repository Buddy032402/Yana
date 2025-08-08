<?php
$conn = new mysqli("localhost", "root", "", "prefinal");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Enable error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Create table query
$sql = "CREATE TABLE IF NOT EXISTS booking_inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    destination_id INT NOT NULL,
    package_id INT NOT NULL,
    number_of_travelers INT NOT NULL,
    preferred_date DATE NOT NULL,
    budget_range VARCHAR(50) NOT NULL,
    special_requests TEXT,
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (destination_id) REFERENCES destinations(id),
    FOREIGN KEY (package_id) REFERENCES packages(id)
)";

// Execute the query
try {
    $conn->query($sql);
} catch (Exception $e) {
    echo "Error creating table: " . $e->getMessage();
}