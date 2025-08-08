<?php
session_start();
include "db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate inputs
    if (!isset($_POST['package_id']) || !isset($_POST['rating']) || !isset($_POST['review'])) {
        $_SESSION['error'] = "Missing required fields";
        header("Location: package_details.php?id=" . $_POST['package_id']);
        exit;
    }
    
    $package_id = (int)$_POST['package_id'];
    $user_id = (int)$_SESSION['user_id'];
    $rating = (int)$_POST['rating'];
    $review = trim($_POST['review']);
    
    // Validate rating
    if ($rating < 1 || $rating > 5) {
        $_SESSION['error'] = "Invalid rating";
        header("Location: package_details.php?id=$package_id");
        exit;
    }
    
    // Check if user already reviewed this package
    $check_query = $conn->prepare("SELECT id FROM package_reviews WHERE user_id = ? AND package_id = ?");
    $check_query->bind_param("ii", $user_id, $package_id);
    $check_query->execute();
    $result = $check_query->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing review
        $review_id = $result->fetch_assoc()['id'];
        $stmt = $conn->prepare("UPDATE package_reviews SET rating = ?, review = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("isi", $rating, $review, $review_id);
    } else {
        // Insert new review
        $stmt = $conn->prepare("INSERT INTO package_reviews (package_id, user_id, rating, review, status, created_at) VALUES (?, ?, ?, ?, 1, NOW())");
        $stmt->bind_param("iiis", $package_id, $user_id, $rating, $review);
    }
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Your review has been submitted successfully!";
    } else {
        $_SESSION['error'] = "Error submitting review: " . $conn->error;
    }
    
    header("Location: package_details.php?id=$package_id");
    exit;
} else {
    // If not POST request, redirect to home
    header("Location: index.php");
    exit;
}
?>