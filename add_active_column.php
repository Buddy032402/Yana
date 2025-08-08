<?php
include "../db.php";

$sql = "ALTER TABLE users ADD COLUMN active TINYINT(1) DEFAULT 1 AFTER role";
if ($conn->query($sql)) {
    echo "Active column added successfully";
} else {
    echo "Error adding active column: " . $conn->error;
}
?>