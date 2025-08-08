<?php
include "../db.php";

$sql = "ALTER TABLE packages 
        ADD COLUMN IF NOT EXISTS max_persons INT DEFAULT 10 AFTER duration";

if ($conn->query($sql) === TRUE) {
    echo "✅ max_persons column added successfully to packages table";
} else {
    echo "❌ Error adding max_persons column: " . $conn->error;
}
?>