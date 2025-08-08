<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

include "../db.php";

// Validate input parameters
$destination_id = isset($_POST['destination_id']) ? (int)$_POST['destination_id'] : 0;
$image_to_delete = isset($_POST['image']) ? trim($_POST['image']) : '';

// Input validation
if (!$destination_id || empty($image_to_delete) || strpbrk($image_to_delete, "\\/?%*:|\"<>") !== false) {
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'message' => 'Invalid parameters']));
}

// Fetch current gallery images
// Change this line
$stmt = $conn->prepare("SELECT gallery_images FROM destinations WHERE id = ? AND status = 1");
// To this
$stmt = $conn->prepare("SELECT gallery_images FROM destinations WHERE id = ?");
$stmt->bind_param("i", $destination_id);
$stmt->execute();
$result = $stmt->get_result();
$destination = $result->fetch_assoc();

if (!$destination) {
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'message' => 'Destination not found']));
}

// Process gallery images
$gallery_images = json_decode($destination['gallery_images'], true) ?: [];
$image_found = false;

// Filter out the image to delete and verify it exists in the array
$gallery_images = array_filter($gallery_images, function($img) use ($image_to_delete, &$image_found) {
    if ($img === $image_to_delete) {
        $image_found = true;
        return false;
    }
    return true;
});

if (!$image_found) {
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'message' => 'Image not found in gallery']));
}

// Delete the physical file
$file_path = "../uploads/destinations/gallery/" . $image_to_delete;
if (file_exists($file_path)) {
    if (!unlink($file_path)) {
        header('Content-Type: application/json');
        die(json_encode(['success' => false, 'message' => 'Failed to delete image file']));
    }
}

// Update the database with reindexed array
$gallery_images_json = json_encode(array_values($gallery_images));
$stmt = $conn->prepare("UPDATE destinations SET gallery_images = ? WHERE id = ?");
$stmt->bind_param("si", $gallery_images_json, $destination_id);

header('Content-Type: application/json');
if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Image deleted successfully',
        'remaining_images' => count($gallery_images)
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Database update failed: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();