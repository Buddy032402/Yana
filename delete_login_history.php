<?php
session_start();
if (!isset($_SESSION["admin"]) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include "../db.php";

// Start transaction
$conn->begin_transaction();

try {
    if (isset($_GET['ids'])) {
        // Delete selected logs
        $ids = array_map('intval', explode(',', $_GET['ids']));
        $ids = implode(',', $ids);
        
        // Move selected logs to archive with specific columns
        $conn->query("INSERT INTO login_history_archive 
                     (id, user_id, username, login_time, logout_time, status, user_agent)
                     SELECT l.id, 
                            CASE WHEN u.id IS NULL THEN NULL ELSE l.user_id END,
                            l.username, 
                            l.login_time, 
                            l.logout_time, 
                            l.status, 
                            l.user_agent 
                     FROM login_history l
                     LEFT JOIN users u ON l.user_id = u.id
                     WHERE l.id IN ($ids)");
        
        // Delete selected logs
        $conn->query("DELETE FROM login_history WHERE id IN ($ids)");
        
        $_SESSION['message'] = 'Selected logs have been archived and deleted successfully.';
    } else {
        // Move all logs to archive with specific columns
        $conn->query("INSERT INTO login_history_archive 
                     (id, user_id, username, login_time, logout_time, status, user_agent)
                     SELECT l.id, 
                            CASE WHEN u.id IS NULL THEN NULL ELSE l.user_id END,
                            l.username, 
                            l.login_time, 
                            l.logout_time, 
                            l.status, 
                            l.user_agent 
                     FROM login_history l
                     LEFT JOIN users u ON l.user_id = u.id");
        
        // Delete all logs
        $conn->query("TRUNCATE TABLE login_history");
        
        $_SESSION['message'] = 'All logs have been archived and deleted successfully.';
    }
    
    // Commit transaction
    $conn->commit();
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    $_SESSION['error'] = 'Error: ' . $e->getMessage();
}

header("Location: login_history.php");
exit;