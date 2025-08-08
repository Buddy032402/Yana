<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

include "../db.php";

header('Content-Type: application/json');

try {
    $package_id = isset($_POST['package_id']) ? intval($_POST['package_id']) : 0;
    $type = $_POST['type'] ?? '';

    if ($type === 'main') {
        // Get current image name
        $stmt = $conn->prepare("SELECT image FROM packages WHERE id = ?");
        $stmt->bind_param("i", $package_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $package = $result->fetch_assoc();

        if ($package && $package['image']) {
            // Delete the physical file
            $file_path = "../uploads/packages/" . $package['image'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Update database
            $stmt = $conn->prepare("UPDATE packages SET image = NULL WHERE id = ?");
            $stmt->bind_param("i", $package_id);
            $stmt->execute();
        }
    } 
    elseif ($type === 'gallery') {
        $image_name = $_POST['image'] ?? '';
        $index = isset($_POST['index']) ? intval($_POST['index']) : -1;

        // Get current gallery images
        $stmt = $conn->prepare("SELECT gallery_images FROM packages WHERE id = ?");
        $stmt->bind_param("i", $package_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $package = $result->fetch_assoc();

        if ($package && $package['gallery_images']) {
            $gallery_images = json_decode($package['gallery_images'], true);
            
            // Delete the physical file
            $file_path = "../uploads/packages/gallery/" . $image_name;
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Remove from array
            if ($index >= 0 && isset($gallery_images[$index])) {
                array_splice($gallery_images, $index, 1);
            }

            // Update database
            $gallery_images_json = json_encode(array_values($gallery_images));
            $stmt = $conn->prepare("UPDATE packages SET gallery_images = ? WHERE id = ?");
            $stmt->bind_param("si", $gallery_images_json, $package_id);
            $stmt->execute();
        }
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>