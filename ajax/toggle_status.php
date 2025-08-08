<?php
session_start();
if (!isset($_SESSION["admin"]) || $_SESSION['role'] !== 'admin') {
    exit("Unauthorized");
}

include "../../db.php";

if (isset($_POST['user_id']) && isset($_POST['status'])) {
    $user_id = $_POST['user_id'];
    $current_status = (int)$_POST['status'];
    $new_status = $current_status == 1 ? 0 : 1;
    
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'staff'");
    $stmt->bind_param("ii", $new_status, $user_id);
    
    echo $stmt->execute() ? 'success' : 'error';
}