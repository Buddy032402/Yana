<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $testimonial_id = $_POST['testimonial_id'];
    $rating = $_POST['rating'];
    $content = trim($_POST['content']);
    $status = $_POST['status'];
    $tour_title = $_POST['tour_title'] ?? null;
    
    // Validate inputs
    if (empty($content)) {
        $_SESSION['error'] = "❌ Content cannot be empty";
        header("Location: manage_testimonials.php");
        exit;
    }
    
    if (!in_array($status, ['pending', 'approved', 'rejected'])) {
        $_SESSION['error'] = "❌ Invalid status";
        header("Location: manage_testimonials.php");
        exit;
    }
    
    if (!is_numeric($rating) || $rating < 1 || $rating > 5) {
        $_SESSION['error'] = "❌ Invalid rating";
        header("Location: manage_testimonials.php");
        exit;
    }
    
    try {
        $stmt = $conn->prepare("UPDATE testimonials SET rating = ?, content = ?, status = ?, tour_title = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param("isssi", $rating, $content, $status, $tour_title, $testimonial_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "✅ Testimonial updated successfully";
            
            // Log the action
            $admin_id = $_SESSION['admin']['id'];
            $action = "Updated testimonial #$testimonial_id";
            $conn->query("INSERT INTO admin_logs (admin_id, action) VALUES ($admin_id, '$action')");
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "❌ Error updating testimonial: " . $e->getMessage();
    }
}

header("Location: manage_testimonials.php");
exit;