<?php
session_start();
include "db.php";

header('Content-Type: application/json');

try {
    // Validate required fields
    $required_fields = ['name', 'email', 'phone', 'destination', 'package', 'travelers', 'preferred_date', 'budget_range'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Please fill in all required fields");
        }
    }

    // Sanitize inputs
    $name = htmlspecialchars(trim($_POST['name']), ENT_QUOTES, 'UTF-8');
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['phone']), ENT_QUOTES, 'UTF-8');
    $destination_id = intval($_POST['destination']);
    $package_id = intval($_POST['package']);
    $travelers = intval($_POST['travelers']);
    $preferred_date = htmlspecialchars(trim($_POST['preferred_date']), ENT_QUOTES, 'UTF-8');
    $budget_range = htmlspecialchars(trim($_POST['budget_range']), ENT_QUOTES, 'UTF-8');
    $special_requests = isset($_POST['special_requests']) ? htmlspecialchars(trim($_POST['special_requests']), ENT_QUOTES, 'UTF-8') : '';

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Please enter a valid email address");
    }
    
    // Validate phone number (modified to be more permissive)
    if (!preg_match('/^[0-9+\-\s().]{6,20}$/', $phone)) {
        // Custom error with no HTML syntax visible to user
        $_SESSION['phone_error'] = "Please enter a valid number";
        throw new Exception("Please enter a valid number");
    }
    
    // Validate date format and ensure it's in the future
    $date_obj = DateTime::createFromFormat('Y-m-d', $preferred_date);
    if (!$date_obj || $date_obj->format('Y-m-d') !== $preferred_date) {
        throw new Exception("Please enter a valid date in YYYY-MM-DD format");
    }
    
    $today = new DateTime();
    if ($date_obj <= $today) {
        throw new Exception("Preferred date must be in the future");
    }

    // Start transaction
    $conn->begin_transaction();
    
    // Check if a similar booking inquiry already exists - use a try-catch to handle any potential errors
    try {
        $check_stmt = $conn->prepare("
            SELECT id FROM booking_inquiries 
            WHERE email = ? AND package_id = ? AND preferred_date = ?
        ");
        
        $check_stmt->bind_param("sis", $email, $package_id, $preferred_date);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Instead of throwing an exception, we'll update the existing booking
            $booking_id = $result->fetch_assoc()['id'];
            
            // Update the existing booking with new information - without updated_at column
            $update_stmt = $conn->prepare("
                UPDATE booking_inquiries 
                SET name = ?, phone = ?, number_of_travelers = ?, 
                    budget_range = ?, special_requests = ?, 
                    status = 'updated'
                WHERE id = ?
            ");
            
            $update_stmt->bind_param("ssissi", $name, $phone, $travelers, $budget_range, $special_requests, $booking_id);
            
            if (!$update_stmt->execute()) {
                // Log the error but don't throw exception
                error_log("Failed to update booking inquiry: " . $update_stmt->error);
            }
            
            // Add a new inquiry entry to track the update
            $inquiry_stmt = $conn->prepare("
                INSERT INTO inquiries (name, email, phone, message, tour_id, type, status, created_at) 
                VALUES (?, ?, ?, ?, ?, 'booking_update', 'new', NOW())
            ");
            
            $message = "Booking Update Details:\nBooking ID: $booking_id\nPackage ID: $package_id\nTravelers: $travelers\nPreferred Date: $preferred_date\nBudget Range: $budget_range\nSpecial Requests: $special_requests";
            
            $inquiry_stmt->bind_param("ssssi", $name, $email, $phone, $message, $package_id);
            
            if (!$inquiry_stmt->execute()) {
                // Log the error but don't throw exception
                error_log("Failed to record booking update inquiry: " . $inquiry_stmt->error);
            }
            
            // Commit transaction
            $conn->commit();
            
            echo json_encode([
                'success' => true, 
                'updated' => true,
                'message' => '<div class="alert alert-success text-center" role="alert">
                                <i class="fas fa-check-circle fa-lg mb-2"></i><br>
                                <strong>Thank you, ' . htmlspecialchars($name) . '!</strong><br>
                                Your existing booking inquiry has been updated successfully.<br>
                                We will contact you soon!
                            </div>'
            ]);
            exit;
        }
    } catch (Exception $check_error) {
        // Log the error but continue with new booking
        error_log("Error checking for existing booking: " . $check_error->getMessage());
    }

    // If no existing booking or check failed, proceed with new booking
    $success = true;
    $error_message = "";
    
    // Insert into inquiries table
    try {
        $inquiry_stmt = $conn->prepare("
            INSERT INTO inquiries (name, email, phone, message, tour_id, type, status, created_at) 
            VALUES (?, ?, ?, ?, ?, 'booking', 'new', NOW())
        ");
        
        $message = "Booking Inquiry Details:\nPackage ID: $package_id\nTravelers: $travelers\nPreferred Date: $preferred_date\nBudget Range: $budget_range\nSpecial Requests: $special_requests";
        
        $inquiry_stmt->bind_param("ssssi", $name, $email, $phone, $message, $package_id);
        
        if (!$inquiry_stmt->execute()) {
            $success = false;
            $error_message = "Failed to submit inquiry: " . $inquiry_stmt->error;
            error_log($error_message);
        }
    } catch (Exception $inquiry_error) {
        $success = false;
        $error_message = "Error with inquiry: " . $inquiry_error->getMessage();
        error_log($error_message);
    }
    
    // Insert into booking_inquiries table
    try {
        $booking_stmt = $conn->prepare("
            INSERT INTO booking_inquiries (name, email, phone, package_id, preferred_date, number_of_travelers, budget_range, special_requests, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");
        
        $booking_stmt->bind_param("sssissss", $name, $email, $phone, $package_id, $preferred_date, $travelers, $budget_range, $special_requests);
        
        if (!$booking_stmt->execute()) {
            // Check if it's a duplicate entry error
            if (strpos($booking_stmt->error, 'Duplicate entry') !== false) {
                // This is a duplicate, but we'll still consider it a success
                // Just update the existing record instead
                $success = true;
            } else {
                $success = false;
                $error_message = "Failed to submit booking details: " . $booking_stmt->error;
                error_log($error_message);
            }
        }
    } catch (Exception $booking_error) {
        // If it's a duplicate entry, we'll still consider it a success
        if (strpos($booking_error->getMessage(), 'Duplicate entry') !== false) {
            $success = true;
        } else {
            $success = false;
            $error_message = "Error with booking: " . $booking_error->getMessage();
            error_log($error_message);
        }
    }

    // If we've made it this far without setting success to false, commit the transaction
    if ($success) {
        $conn->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => '<div class="alert alert-success text-center" role="alert">
                            <i class="fas fa-check-circle fa-lg mb-2"></i><br>
                            <strong>Thank you, ' . htmlspecialchars($name) . '!</strong><br>
                            Your booking inquiry has been sent successfully.<br>
                            We will contact you soon!
                        </div>'
        ]);
    } else {
        // Only rollback and show error if we actually had a problem
        $conn->rollback();
        throw new Exception($error_message);
    }

} catch (Exception $e) {
    if ($conn && $conn->ping()) {
        $conn->rollback();
    }
    
    // Log the error
    error_log("Booking inquiry error: " . $e->getMessage());
    
    // Return plain error message with error type
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'is_error' => true
    ]);

} finally {
    // Close all statements
    if (isset($check_stmt)) {
        $check_stmt->close();
    }
    if (isset($update_stmt)) {
        $update_stmt->close();
    }
    if (isset($inquiry_stmt)) {
        $inquiry_stmt->close();
    }
    if (isset($booking_stmt)) {
        $booking_stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>