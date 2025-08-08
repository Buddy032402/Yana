<?php
include "../db.php";

$sql = "ALTER TABLE top_picks
        ADD COLUMN client_name VARCHAR(255),
        ADD COLUMN client_image VARCHAR(255),
        ADD COLUMN client_rating INT,
        ADD COLUMN description TEXT";

if ($conn->query($sql) === TRUE) {
    echo "Table updated successfully";
} else {
    echo "Error updating table: " . $conn->error;
}
?>