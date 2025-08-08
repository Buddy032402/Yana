<?php
include "../../db.php";

$sql = "ALTER TABLE top_picks
        ADD COLUMN IF NOT EXISTS client_name VARCHAR(255),
        ADD COLUMN IF NOT EXISTS client_image VARCHAR(255) DEFAULT 'default-user.png',
        ADD COLUMN IF NOT EXISTS client_rating INT DEFAULT 5,
        ADD COLUMN IF NOT EXISTS description TEXT";

if ($conn->query($sql) === TRUE) {
    echo "✅ Client columns added successfully to top_picks table";
} else {
    echo "❌ Error adding columns: " . $conn->error;
}
?>