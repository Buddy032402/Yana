<?php
session_start();
include "db.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    // Validate form data
    $required_fields = ['name', 'email', 'phone', 'destination', 'package', 'travelers', 'preferred_date'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("All required fields must be filled out");
        }
    }

    // Get customer information from the form
    $customer_name = $_POST['name'];
    $customer_email = $_POST['email'];
    $customer_phone = $_POST['phone'];
    
    // Check if user exists with this email
    $user_check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $user_check->bind_param("s", $customer_email);
    $user_check->execute();
    $existing_user = $user_check->get_result();
    
    $user_id = 0;
    
    if ($existing_user->num_rows > 0) {
        // User exists, get their ID
        $user_id = $existing_user->fetch_assoc()['id'];
        
        // Update their name and phone if provided
        $update_user = $conn->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
        $update_user->bind_param("ssi", $customer_name, $customer_phone, $user_id);
        $update_user->execute();
    } else {
        // Create a new user
        $create_user = $conn->prepare("INSERT INTO users (name, email, phone, created_at) VALUES (?, ?, ?, NOW())");
        $create_user->bind_param("sss", $customer_name, $customer_email, $customer_phone);
        
        if ($create_user->execute()) {
            $user_id = $conn->insert_id;
        }
    }

    // Insert booking into database
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, package_id, number_of_travelers, booking_date, special_requests, status, payment_status, total_amount, created_at) VALUES (?, ?, ?, ?, ?, 'pending', 'unpaid', ?, NOW())");
    
    $package_id = $_POST['package'];
    $travelers = $_POST['travelers'];
    $booking_date = $_POST['preferred_date'];
    $special_requests = $_POST['special_requests'] ?? '';
    
    // Calculate total amount based on package price and number of travelers
    $package_query = $conn->prepare("SELECT price FROM packages WHERE id = ?");
    $package_query->bind_param("i", $package_id);
    $package_query->execute();
    $package_result = $package_query->get_result();
    $package_price = $package_result->fetch_assoc()['price'];
    $total_amount = $package_price * $travelers;

    $stmt->bind_param("iiissd", $user_id, $package_id, $travelers, $booking_date, $special_requests, $total_amount);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Booking submitted successfully']);
    } else {
        throw new Exception("Error processing booking");
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>