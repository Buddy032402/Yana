<?php
include "../db.php";

$sql = "ALTER TABLE login_history ADD COLUMN IF NOT EXISTS logout_time DATETIME NULL";

if ($conn->query($sql)) {
    echo "✅ Logout time column added successfully to login_history table";
} else {
    echo "❌ Error adding logout time column: " . $conn->error;
}
?>