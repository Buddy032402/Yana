<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

include "../db.php";

function getUsedImages() {
    global $conn;
    $used_images = [];
    
    // Get client images
    $result = $conn->query("SELECT client_image FROM top_picks WHERE client_image IS NOT NULL");
    while ($row = $result->fetch_assoc()) {
        $used_images[] = $row['client_image'];
    }
    
    // Get destination images
    $result = $conn->query("SELECT image, gallery_images FROM destinations WHERE image IS NOT NULL OR gallery_images IS NOT NULL");
    while ($row = $result->fetch_assoc()) {
        if ($row['image']) {
            $used_images[] = $row['image'];
        }
        if ($row['gallery_images']) {
            $gallery = json_decode($row['gallery_images'], true);
            if (is_array($gallery)) {
                $used_images = array_merge($used_images, $gallery);
            }
        }
    }
    
    // Get package images
    $result = $conn->query("SELECT image, gallery_images FROM packages WHERE image IS NOT NULL OR gallery_images IS NOT NULL");
    while ($row = $result->fetch_assoc()) {
        if ($row['image']) {
            $used_images[] = $row['image'];
        }
        if ($row['gallery_images']) {
            $gallery = json_decode($row['gallery_images'], true);
            if (is_array($gallery)) {
                $used_images = array_merge($used_images, $gallery);
            }
        }
    }
    
    // Get profile images
    $result = $conn->query("SELECT profile_image FROM users WHERE profile_image IS NOT NULL");
    while ($row = $result->fetch_assoc()) {
        $used_images[] = $row['profile_image'];
    }
    
    // Get testimonial images
    $result = $conn->query("SELECT image FROM testimonials WHERE image IS NOT NULL");
    while ($row = $result->fetch_assoc()) {
        $used_images[] = $row['image'];
    }
    
    return array_unique($used_images);
}

function cleanupDirectory($dir, $used_images) {
    if (!is_dir($dir)) {
        return ['deleted' => 0, 'errors' => 0];
    }
    
    $stats = ['deleted' => 0, 'errors' => 0];
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        if (!in_array($file, $used_images) && $file !== 'default-user.png') {
            $filepath = $dir . '/' . $file;
            if (is_file($filepath)) {
                if (unlink($filepath)) {
                    $stats['deleted']++;
                } else {
                    $stats['errors']++;
                }
            }
        }
    }
    
    return $stats;
}

// Define upload directories
$upload_dirs = [
    '../uploads/clients',
    '../uploads/destinations',
    '../uploads/destinations/gallery',
    '../uploads/packages',
    '../uploads/packages/gallery',
    '../uploads/profiles',
    '../uploads/testimonials'
];

// Get all used images from database
$used_images = getUsedImages();

// Clean up each directory
$total_deleted = 0;
$total_errors = 0;

foreach ($upload_dirs as $dir) {
    $stats = cleanupDirectory($dir, $used_images);
    $total_deleted += $stats['deleted'];
    $total_errors += $stats['errors'];
}

// Set message
if ($total_deleted > 0 || $total_errors > 0) {
    $_SESSION['message'] = "Cleanup completed: {$total_deleted} unused files deleted";
    if ($total_errors > 0) {
        $_SESSION['message'] .= ", {$total_errors} errors encountered";
    }
} else {
    $_SESSION['message'] = "No unused files found";
}

// Redirect back
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>