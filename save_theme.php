<?php
session_start();
if (!isset($_SESSION["admin"])) {
    http_response_code(403);
    exit('Unauthorized');
}

$data = json_decode(file_get_contents('php://input'), true);
if (isset($data['theme'])) {
    $_SESSION['theme'] = $data['theme'];
    echo json_encode(['success' => true]);
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Theme not specified']);
}