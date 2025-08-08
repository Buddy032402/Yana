<?php
session_start();
include "../db.php";

// Verify staff authentication
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Get pending messages
$sql = "SELECT m.*, u.name as user_name 
        FROM messages m 
        LEFT JOIN users u ON m.user_id = u.id 
        WHERE m.status = 'pending' 
        ORDER BY m.timestamp ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Chat Interface</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .chat-list {
            width: 300px;
            float: left;
            border-right: 1px solid #ccc;
        }
        .chat-window {
            margin-left: 320px;
        }
        .message {
            padding: 10px;
            margin: 5px;
            border-radius: 5px;
        }
        .user-message {
            background: #e3f2fd;
        }
        .staff-message {
            background: #f5f5f5;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="chat-list">
        <h3>Active Chats</h3>
        <!-- List of active chats will be populated here -->
    </div>
    
    <div class="chat-window">
        <div id="messages"></div>
        <div class="input-area">
            <input type="text" id="staff-message" placeholder="Type your response...">
            <button onclick="sendStaffResponse()">Send</button>
        </div>
    </div>

    <script>
        function sendStaffResponse() {
            // Implement staff response sending logic
        }

        // Implement real-time message updates
        setInterval(() => {
            fetchNewMessages();
        }, 3000);
    </script>
</body>
</html>