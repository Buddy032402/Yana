<?php
include "../db.php";

$sql = "ALTER TABLE inquiries ADD COLUMN IF NOT EXISTS type VARCHAR(50) DEFAULT 'general' AFTER tour_id";

if ($conn->query($sql) === TRUE) {
    echo "✅ Type column added successfully to inquiries table";
} else {
    echo "❌ Error adding type column: " . $conn->error;
}
?>