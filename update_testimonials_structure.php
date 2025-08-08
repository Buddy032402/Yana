<?php
session_start(); // Add session_start at the beginning
include "../db.php";

// Disable foreign key checks temporarily
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// Drop the foreign key constraint for user_id first
$dropForeignKeySQL = "ALTER TABLE testimonials 
                     DROP FOREIGN KEY IF EXISTS testimonials_ibfk_1";

try {
    $conn->query($dropForeignKeySQL);
} catch (Exception $e) {
    // Foreign key might not exist, continue with other alterations
}

// First add columns without NOT NULL constraint
$sql = "ALTER TABLE testimonials
        ADD COLUMN IF NOT EXISTS customer_name VARCHAR(255),
        ADD COLUMN IF NOT EXISTS customer_email VARCHAR(255),
        ADD COLUMN IF NOT EXISTS package_id INT,
        DROP COLUMN IF EXISTS user_id";

if ($conn->query($sql)) {
    // Update existing rows with a default value
    $updateSQL = "UPDATE testimonials SET customer_name = 'Anonymous' WHERE customer_name IS NULL";
    $conn->query($updateSQL);
    
    // Now add NOT NULL constraint
    $alterSQL = "ALTER TABLE testimonials MODIFY customer_name VARCHAR(255) NOT NULL";
    $conn->query($alterSQL);
    
    // Add foreign key for package_id
    $addForeignKeySQL = "ALTER TABLE testimonials
                        ADD CONSTRAINT IF NOT EXISTS fk_testimonial_package
                        FOREIGN KEY (package_id) REFERENCES packages(id) 
                        ON DELETE SET NULL";
    
    if ($conn->query($addForeignKeySQL)) {
        $_SESSION['message'] = "✅ Testimonials table structure updated successfully";
    } else {
        $_SESSION['error'] = "❌ Error adding foreign key: " . $conn->error;
    }
} else {
    $_SESSION['error'] = "❌ Error updating table structure: " . $conn->error;
}

// Re-enable foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

// Redirect to manage_testimonials.php
header("Location: manage_testimonials.php");
exit;
?>