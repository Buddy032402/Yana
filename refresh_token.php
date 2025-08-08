<?php
session_start();
$_SESSION['submission_token'] = bin2hex(random_bytes(32));
echo json_encode(['success' => true]);
?>