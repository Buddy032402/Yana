<?php
include "../db.php";

$sql = "ALTER TABLE packages 
        ADD COLUMN IF NOT EXISTS includes TEXT AFTER featured,
        ADD COLUMN IF NOT EXISTS excludes TEXT AFTER includes,
        ADD COLUMN IF NOT EXISTS itinerary TEXT AFTER excludes";

if ($conn->query($sql) === TRUE) {
    echo "✅ New columns (includes, excludes, itinerary) added successfully to packages table";
} else {
    echo "❌ Error adding columns: " . $conn->error;
}
?>