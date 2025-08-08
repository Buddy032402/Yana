<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$conn->begin_transaction();

try {
    // Remove from featured_destinations table
    $stmt = $conn->prepare("DELETE FROM featured_destinations WHERE destination_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Update destinations table
    $stmt = $conn->prepare("UPDATE destinations SET featured = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $conn->commit();
    $_SESSION['message'] = "✅ Destination removed from featured successfully";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "❌ Error removing destination from featured: " . $e->getMessage();
}

header("Location: manage_featured.php");
exit;