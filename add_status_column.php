<?php
include "../db.php";

$sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS status TINYINT(1) DEFAULT 1";

if ($conn->query($sql) === TRUE) {
    echo "Status column added successfully";
} else {
    echo "Error adding status column: " . $conn->error;
}
?>