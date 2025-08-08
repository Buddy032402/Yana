<?php
include "../db.php";

$sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_image VARCHAR(255) NULL";

if ($conn->query($sql)) {
    echo "✅ Profile image column added successfully to users table";
} else {
    echo "❌ Error adding profile image column: " . $conn->error;
}
?>