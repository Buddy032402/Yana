<?php
$directory = "../uploads/destinations";

if (!file_exists($directory)) {
    if (mkdir($directory, 0777, true)) {
        echo "✅ Destinations uploads directory created successfully";
    } else {
        echo "❌ Error creating destinations uploads directory";
    }
} else {
    echo "✅ Destinations uploads directory already exists";
}
?>