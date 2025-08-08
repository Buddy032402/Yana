<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : 'Contact Form Message';
    $message = trim($_POST['message']);
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Please fill all required fields']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
        exit;
    }

    // Check for duplicate message in last 20 seconds
    $duplicate_check = $conn->prepare("
        SELECT id FROM admin_messages 
        WHERE sender_email = ? 
        AND message = ? 
        AND created_at > DATE_SUB(NOW(), INTERVAL 20 SECOND)
        LIMIT 1
    ");
    $duplicate_check->bind_param("ss", $email, $message);
    $duplicate_check->execute();
    
    if ($duplicate_check->get_result()->num_rows > 0) {
        echo json_encode([
            'success' => true,  // Changed from false to true to trigger green background
            'message' => '<div class="alert alert-success">Thank you! '.htmlspecialchars($name).', we have received your message and will respond shortly.</div>',
            'warning' => '<div class="alert alert-warning mt-3">Please wait 20 seconds before sending another message</div>',
            'show_warning_after' => 5000
        ]);
        exit;
    }
    
    // Insert message into admin_messages table
    $stmt = $conn->prepare("INSERT INTO admin_messages (sender_name, sender_email, subject, message, phone, is_read, status, created_at) VALUES (?, ?, ?, ?, ?, 0, 'active', NOW())");
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
        exit;
    }
    
    $stmt->bind_param("sssss", $name, $email, $subject, $message, $phone);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => '<div class="alert alert-success">Dear '.htmlspecialchars($name).', thank you for contacting us! Your message has been sent successfully and we will respond soon.</div>'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => '<div class="alert alert-danger">We apologize, '.htmlspecialchars($name).', but there was an error sending your message. Please try again later.</div>'
        ]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>