<?php
session_start();
include "../db.php";

// Store the previous page URL in a session variable if not already set
if (!isset($_SESSION['previous_page']) && isset($_SERVER['HTTP_REFERER'])) {
    $_SESSION['previous_page'] = $_SERVER['HTTP_REFERER'];
}

// Check if logout was confirmed
if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
    // If not confirmed, redirect back to the stored previous page or dashboard
    $redirect_to = isset($_SESSION['previous_page']) ? $_SESSION['previous_page'] : 'dashboard.php';
    unset($_SESSION['previous_page']); // Clear the stored URL
    header("Location: $redirect_to");
    exit;
}

// Process logout only if confirmed
if ($_GET['confirm'] === 'yes' && isset($_SESSION['user_id'])) {
    // Update the latest login record with logout time
    $stmt = $conn->prepare("
        UPDATE login_history 
        SET logout_time = NOW() 
        WHERE user_id = ? 
        AND status = 'success' 
        AND logout_time IS NULL 
        ORDER BY login_time DESC 
        LIMIT 1
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();

    // Clear all session variables
    session_unset();
    // Destroy the session
    session_destroy();

    // Redirect to login page
    header("Location: login.php");
    exit;
}
?>