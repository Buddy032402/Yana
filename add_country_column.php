<?php
include "../db.php";

$sql = "ALTER TABLE destinations ADD COLUMN IF NOT EXISTS country VARCHAR(255) NOT NULL AFTER name";

if ($conn->query($sql) === TRUE) {
    echo "✅ Country column added successfully to destinations table";
} else {
    echo "❌ Error adding country column: " . $conn->error;
}
?>