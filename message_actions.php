<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['id'])) {
        $action = $_POST['action'];
        $id = (int)$_POST['id'];
        
        if ($action === 'delete') {
            // Delete message
            $stmt = $conn->prepare("DELETE FROM admin_messages WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = '<div class="alert alert-success">Message deleted successfully</div>';
            } else {
                $response['message'] = '';
            }
        } elseif ($action === 'archive') {
            // Archive message - update status to archived
            $stmt = $conn->prepare("UPDATE admin_messages SET status = 'archived' WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = '';
            } else {
                $response['message'] = '';
            }
            $response['message'] = 'Error archiving message: ' . $conn->error;
        } elseif ($action === 'restore') {
            // Restore archived message
            $stmt = $conn->prepare("UPDATE admin_messages SET status = 'active' WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Message restored successfully';
            } else {
                $response['message'] = 'Error restoring message: ' . $conn->error;
            }
        } else {
            $response['message'] = 'Invalid action';
        }
    } else {
        $response['message'] = 'Missing required parameters';
    }
} else {
    $response['message'] = 'Invalid request method';
}

header('Content-Type: application/json');
echo json_encode($response);