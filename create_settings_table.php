<?php
include "../db.php";

$sql = "CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(255) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_group VARCHAR(50) NOT NULL DEFAULT 'general',
    setting_label VARCHAR(255) NOT NULL,
    setting_type ENUM('text', 'textarea', 'number', 'email', 'file', 'select') DEFAULT 'text',
    setting_options TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql)) {
    // Insert default settings
    $default_settings = [
        ['site_name', 'Dream Travels', 'general', 'Site Name', 'text'],
        ['site_email', 'info@dreamtravels.com', 'general', 'Site Email', 'email'],
        ['site_phone', '+1234567890', 'general', 'Site Phone', 'text'],
        ['site_address', '123 Travel Street, City, Country', 'general', 'Site Address', 'textarea'],
        ['site_logo', 'logo.png', 'general', 'Site Logo', 'file'],
        ['currency_symbol', '$', 'payment', 'Currency Symbol', 'text'],
        ['booking_email', 'bookings@dreamtravels.com', 'booking', 'Booking Email', 'email']
    ];

    $stmt = $conn->prepare("INSERT IGNORE INTO settings (setting_key, setting_value, setting_group, setting_label, setting_type) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($default_settings as $setting) {
        $stmt->bind_param("sssss", $setting[0], $setting[1], $setting[2], $setting[3], $setting[4]);
        $stmt->execute();
    }
    
    echo "Settings table created and populated successfully!";
} else {
    echo "Error creating settings table: " . $conn->error;
}
?>