<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $type = isset($_POST['type']) ? $_POST['type'] : '';
    
    if ($id && $type === 'destination') {
        // Get image filename before deletion
        $stmt = $conn->prepare("SELECT image FROM destinations WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $destination = $result->fetch_assoc();
        
        // Delete the destination
        $delete_stmt = $conn->prepare("DELETE FROM destinations WHERE id = ?");
        $delete_stmt->bind_param("i", $id);
        
        if ($delete_stmt->execute()) {
            // Delete associated image file
            if ($destination && $destination['image']) {
                $image_path = "../uploads/destinations/" . $destination['image'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            $_SESSION['success'] = "Destination deleted successfully";
        } else {
            $_SESSION['error'] = "Error deleting destination";
        }
    } else {
        $_SESSION['error'] = "Invalid request";
    }
}

header("Location: manage_destinations.php");
exit;