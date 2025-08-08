<?php
include "../../db.php";

$sql = "ALTER TABLE top_picks
        ADD COLUMN client_name VARCHAR(255) NULL,
        ADD COLUMN client_image VARCHAR(255) NULL DEFAULT 'default-user.png',
        ADD COLUMN client_rating INT NULL DEFAULT 5";

if ($conn->query($sql)) {
    echo "✅ Client columns added successfully to top_picks table";
} else {
    echo "❌ Error adding columns: " . $conn->error;
}
?>