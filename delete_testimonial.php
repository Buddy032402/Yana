<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // First check if testimonial exists
    $check = $conn->prepare("SELECT id FROM testimonials WHERE id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = "❌ Testimonial not found";
    } else {
        $stmt = $conn->prepare("DELETE FROM testimonials WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "✅ Testimonial deleted successfully";
        } else {
            $_SESSION['error'] = "❌ Error deleting testimonial";
        }
    }
} else {
    $_SESSION['error'] = "❌ Invalid testimonial ID";
}

header("Location: manage_testimonials.php");
exit;
?>