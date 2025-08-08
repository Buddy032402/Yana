<?php
include "../db.php";

$sql = "CREATE TABLE IF NOT EXISTS login_history_archive (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    username VARCHAR(255),
    login_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    logout_time DATETIME NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    status ENUM('success', 'failed') NOT NULL,
    archive_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)";

if ($conn->query($sql)) {
    echo "✅ Login history archive table created successfully";
} else {
    echo "❌ Error creating archive table: " . $conn->error;
}
?>